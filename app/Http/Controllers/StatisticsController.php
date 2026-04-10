<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    /**
     * Afficher les statistiques pour un tenant
     * Optimisé avec cache et requêtes groupées
     */
    public function index(Request $request, $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        // Résoudre la période (défaut : 30 derniers jours)
        $period   = $request->get('period', '30days');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        [$from, $to] = $this->resolveDateRange($period, $dateFrom, $dateTo);

        // Pas de cache pour les requêtes filtrées par date (ou cache très court)
        $data = [
            'stats'          => $this->getGeneralStats($tenant, $from, $to),
            'hourlyPeaks'    => $this->getHourlyPeaks($tenant, $from, $to),
            'topDishes'      => $this->getTopDishes($tenant, $from, $to),
            'conversionRate' => $this->getConversionRate($tenant),
            'revenueByPeriod'=> $this->getRevenueByPeriod($tenant),
            'trendData'      => $this->getLast7DaysData($tenant),
        ];

        return view('admin.statistics', array_merge([
            'tenant'      => $tenant,
            'period'      => $period,
            'dateFrom'    => $from->toDateString(),
            'dateTo'      => $to->toDateString(),
            'periodLabel' => $this->buildPeriodLabel($period, $from, $to),
        ], $data));
    }

    private function resolveDateRange(string $period, ?string $dateFrom, ?string $dateTo): array
    {
        $today = Carbon::today();
        return match ($period) {
            'today'     => [$today, $today],
            'yesterday' => [$today->copy()->subDay(), $today->copy()->subDay()],
            '7days'     => [$today->copy()->subDays(6), $today],
            'month'     => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'custom'    => [
                Carbon::parse($dateFrom ?? $today),
                Carbon::parse($dateTo   ?? $today),
            ],
            default     => [$today->copy()->subDays(29), $today], // 30days
        };
    }

    private function buildPeriodLabel(string $period, Carbon $from, Carbon $to): string
    {
        return match ($period) {
            'today'     => "Aujourd'hui",
            'yesterday' => 'Hier',
            '7days'     => '7 derniers jours',
            'month'     => 'Ce mois',
            'custom'    => $from->format('d/m/Y') . ' – ' . $to->format('d/m/Y'),
            default     => '30 derniers jours',
        };
    }

    /**
     * API pour les données de graphiques
     * Validation stricte du paramètre period
     */
    public function chartData(Request $request, $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        // SECURITE: Valider period contre liste blanche
        $allowedPeriods = ['7days', '30days', 'hourly'];
        $period = $request->get('period', '7days');

        if (!in_array($period, $allowedPeriods)) {
            return response()->json(['error' => 'Période invalide'], 400);
        }

        // Cache court (1 minute) pour les données de graphique
        $cacheKey = "chart_data_{$tenant->id}_{$period}";
        $data = Cache::remember($cacheKey, 60, function () use ($tenant, $period) {
            return match ($period) {
                '7days' => $this->getLast7DaysData($tenant),
                '30days' => $this->getLast30DaysData($tenant),
                'hourly' => $this->getHourlyData($tenant),
                default => [],
            };
        });

        return response()->json($data);
    }

    /**
     * Statistiques générales filtrées par période
     */
    private function getGeneralStats($tenant, Carbon $from, Carbon $to)
    {
        $stats = Order::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw("
                COUNT(*) as total_orders,
                COALESCE(SUM(total), 0) as total_revenue,
                COALESCE(AVG(total), 0) as avg_order_value,
                COALESCE(SUM(CASE WHEN DATE(created_at) = date('now') THEN 1 ELSE 0 END), 0) as today_orders,
                COALESCE(SUM(CASE WHEN DATE(created_at) = date('now') THEN total ELSE 0 END), 0) as today_revenue,
                SUM(CASE WHEN status = 'RECU' THEN 1 ELSE 0 END) as pending_orders
            ")
            ->first();

        return [
            'total_orders'    => (int) $stats->total_orders,
            'total_revenue'   => (float) $stats->total_revenue,
            'avg_order_value' => round((float) $stats->avg_order_value, 2),
            'today_orders'    => (int) $stats->today_orders,
            'today_revenue'   => (float) $stats->today_revenue,
            'pending_orders'  => (int) $stats->pending_orders,
        ];
    }

    /**
     * Pics horaires filtrés par période
     */
    private function getHourlyPeaks($tenant, Carbon $from, Carbon $to)
    {
        $hourlyData = Order::where('tenant_id', $tenant->id)
            ->selectRaw("cast(strftime('%H', created_at) as integer) as hour, COUNT(*) as count")
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $peaks = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $peaks[] = [
                'hour'  => $hour,
                'count' => $hourlyData->get($hour)->count ?? 0,
                'label' => sprintf('%02d:00', $hour),
            ];
        }
        return $peaks;
    }

    /**
     * Top plats filtrés par période
     */
    private function getTopDishes($tenant, Carbon $from, Carbon $to, $limit = 10)
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->where('orders.tenant_id', $tenant->id)
            ->whereBetween('orders.created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('dishes.name, SUM(order_items.quantity) as total_quantity, COUNT(order_items.id) as order_count')
            ->groupBy('dishes.id', 'dishes.name')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'name'     => $item->name,
                'quantity' => $item->total_quantity,
                'orders'   => $item->order_count,
            ]);
    }

    /**
     * Taux de conversion (simulé)
     */
    private function getConversionRate($tenant)
    {
        $totalOrders = Order::where('tenant_id', $tenant->id)->count();
        $estimatedVisits = $totalOrders * 3;

        $rate = $estimatedVisits > 0 ? ($totalOrders / $estimatedVisits) * 100 : 0;

        return [
            'rate' => round($rate, 1),
            'orders' => $totalOrders,
            'estimated_visits' => $estimatedVisits
        ];
    }

    /**
     * Revenus par période - OPTIMISE: 1 requête au lieu de 3
     */
    private function getRevenueByPeriod($tenant)
    {
        // Une seule requête pour toutes les périodes
        $results = Order::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', Carbon::now()->subDays(90))
            ->selectRaw('
                SUM(CASE WHEN created_at >= datetime(\'now\', \'-7 days\') THEN total ELSE 0 END) as revenue_7days,
                SUM(CASE WHEN created_at >= datetime(\'now\', \'-30 days\') THEN total ELSE 0 END) as revenue_30days,
                SUM(total) as revenue_90days
            ')
            ->first();

        return [
            '7days' => [
                'revenue' => (float) ($results->revenue_7days ?? 0),
                'days' => 7,
                'avg_daily' => round(((float) ($results->revenue_7days ?? 0)) / 7, 2)
            ],
            '30days' => [
                'revenue' => (float) ($results->revenue_30days ?? 0),
                'days' => 30,
                'avg_daily' => round(((float) ($results->revenue_30days ?? 0)) / 30, 2)
            ],
            '90days' => [
                'revenue' => (float) ($results->revenue_90days ?? 0),
                'days' => 90,
                'avg_daily' => round(((float) ($results->revenue_90days ?? 0)) / 90, 2)
            ],
        ];
    }

    /**
     * Données des 7 derniers jours - OPTIMISE: 1 requête au lieu de 14
     */
    private function getLast7DaysData($tenant)
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();

        // Une seule requête groupée par date
        $dailyData = Order::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $dayData = $dailyData->get($date);

            $data[] = [
                'date' => Carbon::parse($date)->format('d/m'),
                'orders' => $dayData ? (int) $dayData->orders : 0,
                'revenue' => $dayData ? (float) $dayData->revenue : 0
            ];
        }

        return $data;
    }

    /**
     * Données des 30 derniers jours - OPTIMISE: 1 requête au lieu de 60
     */
    private function getLast30DaysData($tenant)
    {
        $startDate = Carbon::now()->subDays(29)->startOfDay();

        // Une seule requête groupée par date
        $dailyData = Order::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $dayData = $dailyData->get($date);

            $data[] = [
                'date' => Carbon::parse($date)->format('d/m'),
                'orders' => $dayData ? (int) $dayData->orders : 0,
                'revenue' => $dayData ? (float) $dayData->revenue : 0
            ];
        }

        return $data;
    }

    /**
     * Données horaires du jour - OPTIMISE: 1 requête au lieu de 24
     */
    private function getHourlyData($tenant)
    {
        // Une seule requête groupée par heure
        $hourlyData = Order::where('tenant_id', $tenant->id)
            ->whereDate('created_at', Carbon::today())
            ->selectRaw('cast(strftime(\'%H\', created_at) as integer) as hour, COUNT(*) as orders')
            ->groupBy('hour')
            ->get()
            ->keyBy('hour');

        $data = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $data[] = [
                'hour' => sprintf('%02d:00', $hour),
                'orders' => $hourlyData->get($hour)?->orders ?? 0
            ];
        }

        return $data;
    }
}
