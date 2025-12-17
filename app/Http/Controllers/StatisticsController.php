<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    /**
     * Afficher les statistiques pour un tenant
     */
    public function index($tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        // Statistiques générales
        $stats = $this->getGeneralStats($tenant);

        // Pics horaires
        $hourlyPeaks = $this->getHourlyPeaks($tenant);

        // Top plats
        $topDishes = $this->getTopDishes($tenant);

        // Taux de conversion (simulé pour l'instant)
        $conversionRate = $this->getConversionRate($tenant);

        // Revenus par période
        $revenueByPeriod = $this->getRevenueByPeriod($tenant);

        return view('admin.statistics', compact(
            'tenant',
            'stats',
            'hourlyPeaks',
            'topDishes',
            'conversionRate',
            'revenueByPeriod'
        ));
    }

    /**
     * API pour les données de graphiques
     */
    public function chartData(Request $request, $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $period = $request->get('period', '7days');

        $data = [];

        switch ($period) {
            case '7days':
                $data = $this->getLast7DaysData($tenant);
                break;
            case '30days':
                $data = $this->getLast30DaysData($tenant);
                break;
            case 'hourly':
                $data = $this->getHourlyData($tenant);
                break;
        }

        return response()->json($data);
    }

    /**
     * Statistiques générales
     */
    private function getGeneralStats($tenant)
    {
        $totalOrders = Order::where('tenant_id', $tenant->id)->count();
        $totalRevenue = Order::where('tenant_id', $tenant->id)->sum('total');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $todayOrders = Order::where('tenant_id', $tenant->id)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $todayRevenue = Order::where('tenant_id', $tenant->id)
            ->whereDate('created_at', Carbon::today())
            ->sum('total');

        $pendingOrders = Order::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->count();

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'avg_order_value' => round($avgOrderValue, 2),
            'today_orders' => $todayOrders,
            'today_revenue' => $todayRevenue,
            'pending_orders' => $pendingOrders
        ];
    }

    /**
     * Pics horaires des commandes
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
     * Top plats populaires
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
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'quantity' => $item->total_quantity,
                    'orders' => $item->order_count
                ];
            });
    }

    /**
     * Taux de conversion (simulé)
     */
    private function getConversionRate($tenant)
    {
        // Pour l'instant, on simule un taux basé sur les commandes
        $totalOrders = Order::where('tenant_id', $tenant->id)->count();
        $estimatedVisits = $totalOrders * 3; // Estimation arbitraire

        $rate = $estimatedVisits > 0 ? ($totalOrders / $estimatedVisits) * 100 : 0;

        return [
            'rate' => round($rate, 1),
            'orders' => $totalOrders,
            'estimated_visits' => $estimatedVisits
        ];
    }

    /**
     * Revenus par période
     */
    private function getRevenueByPeriod($tenant)
    {
        $periods = ['7days', '30days', '90days'];

        $data = [];
        foreach ($periods as $period) {
            $days = str_replace(['7days', '30days', '90days'], [7, 30, 90], $period);

            $revenue = Order::where('tenant_id', $tenant->id)
                ->whereDate('created_at', '>=', Carbon::now()->subDays($days))
                ->sum('total');

            $data[$period] = [
                'revenue' => $revenue,
                'days' => $days,
                'avg_daily' => $days > 0 ? round($revenue / $days, 2) : 0
            ];
        }

        return $data;
    }

    /**
     * Données des 7 derniers jours
     */
    private function getLast7DaysData($tenant)
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();

            $orders = Order::where('tenant_id', $tenant->id)
                ->whereDate('created_at', $date)
                ->count();

            $revenue = Order::where('tenant_id', $tenant->id)
                ->whereDate('created_at', $date)
                ->sum('total');

            $data[] = [
                'date' => Carbon::parse($date)->format('d/m'),
                'orders' => $orders,
                'revenue' => $revenue
            ];
        }

        return $data;
    }

    /**
     * Données des 30 derniers jours
     */
    private function getLast30DaysData($tenant)
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();

            $orders = Order::where('tenant_id', $tenant->id)
                ->whereDate('created_at', $date)
                ->count();

            $revenue = Order::where('tenant_id', $tenant->id)
                ->whereDate('created_at', $date)
                ->sum('total');

            $data[] = [
                'date' => Carbon::parse($date)->format('d/m'),
                'orders' => $orders,
                'revenue' => $revenue
            ];
        }

        return $data;
    }

    /**
     * Données horaires du jour
     */
    private function getHourlyData($tenant)
    {
        $data = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $orders = Order::where('tenant_id', $tenant->id)
                ->whereDate('created_at', Carbon::today())
                ->whereRaw('HOUR(created_at) = ?', [$hour])
                ->count();

            $data[] = [
                'hour' => sprintf('%02d:00', $hour),
                'orders' => $orders
            ];
        }

        return $data;
    }
}
