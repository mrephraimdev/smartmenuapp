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
    public function index($tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        // Cache des stats pendant 5 minutes pour réduire la charge
        $cacheKey = "statistics_{$tenant->id}";
        $data = Cache::remember($cacheKey, 300, function () use ($tenant) {
            return [
                'stats' => $this->getGeneralStats($tenant),
                'hourlyPeaks' => $this->getHourlyPeaks($tenant),
                'topDishes' => $this->getTopDishes($tenant),
                'conversionRate' => $this->getConversionRate($tenant),
                'revenueByPeriod' => $this->getRevenueByPeriod($tenant),
                'trendData' => $this->getLast7DaysData($tenant),
            ];
        });

        return view('admin.statistics', array_merge(['tenant' => $tenant], $data));
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
     * Statistiques générales - OPTIMISE: 1 requête au lieu de 5
     */
    private function getGeneralStats($tenant)
    {
        // Combiner toutes les stats en une seule requête
        $stats = Order::where('tenant_id', $tenant->id)
            ->selectRaw('
                COUNT(*) as total_orders,
                COALESCE(SUM(total), 0) as total_revenue,
                COALESCE(AVG(total), 0) as avg_order_value,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_orders,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total ELSE 0 END) as today_revenue,
                SUM(CASE WHEN status = "RECU" THEN 1 ELSE 0 END) as pending_orders
            ')
            ->first();

        return [
            'total_orders' => (int) $stats->total_orders,
            'total_revenue' => (float) $stats->total_revenue,
            'avg_order_value' => round((float) $stats->avg_order_value, 2),
            'today_orders' => (int) $stats->today_orders,
            'today_revenue' => (float) $stats->today_revenue,
            'pending_orders' => (int) $stats->pending_orders
        ];
    }

    /**
     * Pics horaires des commandes - Déjà optimisé
     */
    private function getHourlyPeaks($tenant)
    {
        $hourlyData = Order::where('tenant_id', $tenant->id)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $peaks = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $peaks[] = [
                'hour' => $hour,
                'count' => $hourlyData->get($hour)->count ?? 0,
                'label' => sprintf('%02d:00', $hour)
            ];
        }

        return $peaks;
    }

    /**
     * Top plats populaires - Déjà optimisé
     */
    private function getTopDishes($tenant, $limit = 10)
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->where('orders.tenant_id', $tenant->id)
            ->selectRaw('dishes.name, SUM(order_items.quantity) as total_quantity, COUNT(order_items.id) as order_count')
            ->groupBy('dishes.id', 'dishes.name')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'name' => $item->name,
                'quantity' => $item->total_quantity,
                'orders' => $item->order_count
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
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN total ELSE 0 END) as revenue_7days,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN total ELSE 0 END) as revenue_30days,
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
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as orders')
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
