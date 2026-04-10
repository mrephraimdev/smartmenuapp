<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dish;
use App\Models\Tenant;
use App\Models\Review;
use App\Models\Reservation;
use App\Enums\OrderStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /**
     * Get hourly peaks for orders
     */
    public function getHourlyPeaks(int $tenantId, ?Carbon $date = null): array
    {
        $date = $date ?? now();

        $hourlyData = Order::where('tenant_id', $tenantId)
            ->whereDate('created_at', $date)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Fill all 24 hours with 0 if no data
        $result = [];
        for ($i = 0; $i < 24; $i++) {
            $result[$i] = $hourlyData[$i] ?? 0;
        }

        return [
            'data' => $result,
            'peak_hour' => array_search(max($result), $result),
            'peak_count' => max($result),
            'total' => array_sum($result),
        ];
    }

    /**
     * Get top dishes by popularity
     */
    public function getTopDishes(int $tenantId, int $limit = 10, string $period = 'month'): Collection
    {
        $startDate = $this->getStartDate($period);

        return OrderItem::select('dish_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('COUNT(*) as order_count'))
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.created_at', '>=', $startDate)
            ->whereIn('orders.status', [OrderStatus::READY->value, OrderStatus::SERVED->value])
            ->groupBy('dish_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->with('dish')
            ->get()
            ->map(function ($item) {
                return [
                    'dish' => $item->dish,
                    'total_quantity' => (int) $item->total_quantity,
                    'order_count' => (int) $item->order_count,
                ];
            });
    }

    /**
     * Get conversion rate (orders/visits)
     * Note: This requires visit tracking to be implemented
     */
    public function getConversionRate(int $tenantId, string $period = 'month'): float
    {
        $startDate = $this->getStartDate($period);

        $ordersCount = Order::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startDate)
            ->count();

        // Placeholder - would need visit tracking
        $visitsCount = $ordersCount * 3; // Assuming 33% conversion rate placeholder

        if ($visitsCount === 0) {
            return 0;
        }

        return round(($ordersCount / $visitsCount) * 100, 2);
    }

    /**
     * Get revenue for a period
     */
    public function getRevenue(int $tenantId, string $period = 'month'): array
    {
        $startDate = $this->getStartDate($period);

        $revenue = Order::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', [OrderStatus::READY->value, OrderStatus::SERVED->value])
            ->sum('total');

        $previousPeriodRevenue = $this->getPreviousPeriodRevenue($tenantId, $period);

        $change = $previousPeriodRevenue > 0
            ? round((($revenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100, 2)
            : 0;

        return [
            'current' => (float) $revenue,
            'previous' => (float) $previousPeriodRevenue,
            'change_percent' => $change,
            'trend' => $change >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Get daily revenue for chart
     */
    public function getDailyRevenue(int $tenantId, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $dailyData = Order::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', [OrderStatus::READY->value, OrderStatus::SERVED->value])
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $result[] = [
                'date' => $date,
                'revenue' => (float) ($dailyData[$date]->revenue ?? 0),
                'orders' => (int) ($dailyData[$date]->orders ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Get orders count by status
     */
    public function getOrdersByStatus(int $tenantId, string $period = 'month'): array
    {
        $startDate = $this->getStartDate($period);

        $statusCounts = Order::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'RECU' => $statusCounts[OrderStatus::RECEIVED->value] ?? 0,
            'PREP' => $statusCounts[OrderStatus::PREPARING->value] ?? 0,
            'PRET' => $statusCounts[OrderStatus::READY->value] ?? 0,
            'SERVI' => $statusCounts[OrderStatus::SERVED->value] ?? 0,
            'ANNULE' => $statusCounts[OrderStatus::CANCELLED->value] ?? 0,
        ];
    }

    /**
     * Get average order value
     */
    public function getAverageOrderValue(int $tenantId, string $period = 'month'): float
    {
        $startDate = $this->getStartDate($period);

        return (float) Order::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', [OrderStatus::READY->value, OrderStatus::SERVED->value])
            ->avg('total') ?? 0;
    }

    /**
     * Get complete dashboard statistics
     */
    public function getDashboardStats(int $tenantId): array
    {
        $today = now()->startOfDay();

        return [
            'today' => [
                'orders' => Order::where('tenant_id', $tenantId)->whereDate('created_at', $today)->count(),
                'revenue' => (float) Order::where('tenant_id', $tenantId)
                    ->whereDate('created_at', $today)
                    ->whereIn('status', [OrderStatus::READY->value, OrderStatus::SERVED->value])
                    ->sum('total'),
                'average_order' => (float) Order::where('tenant_id', $tenantId)
                    ->whereDate('created_at', $today)
                    ->whereIn('status', [OrderStatus::READY->value, OrderStatus::SERVED->value])
                    ->avg('total') ?? 0,
            ],
            'week' => [
                'orders' => Order::where('tenant_id', $tenantId)
                    ->where('created_at', '>=', now()->startOfWeek())
                    ->count(),
                'revenue' => (float) Order::where('tenant_id', $tenantId)
                    ->where('created_at', '>=', now()->startOfWeek())
                    ->whereIn('status', [OrderStatus::READY->value, OrderStatus::SERVED->value])
                    ->sum('total'),
            ],
            'month' => [
                'orders' => Order::where('tenant_id', $tenantId)
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count(),
                'revenue' => (float) Order::where('tenant_id', $tenantId)
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->whereIn('status', [OrderStatus::READY->value, OrderStatus::SERVED->value])
                    ->sum('total'),
            ],
            'pending_orders' => Order::where('tenant_id', $tenantId)
                ->whereIn('status', OrderStatus::activeValues())
                ->count(),
            'hourly_peaks' => $this->getHourlyPeaks($tenantId),
            'top_dishes' => $this->getTopDishes($tenantId, 5, 'week'),
        ];
    }

    /**
     * Get table performance statistics
     */
    public function getTableStats(int $tenantId, string $period = 'month'): Collection
    {
        $startDate = $this->getStartDate($period);

        return Order::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', [OrderStatus::READY->value, OrderStatus::SERVED->value])
            ->select('table_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total) as total_revenue'))
            ->groupBy('table_id')
            ->with('table')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($item) {
                return [
                    'table' => $item->table,
                    'orders_count' => (int) $item->orders_count,
                    'total_revenue' => (float) $item->total_revenue,
                    'average_order' => $item->orders_count > 0
                        ? round($item->total_revenue / $item->orders_count, 2)
                        : 0,
                ];
            });
    }

    /**
     * Get start date based on period
     */
    private function getStartDate(string $period): Carbon
    {
        return match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    /**
     * Get previous period revenue for comparison
     */
    private function getPreviousPeriodRevenue(int $tenantId, string $period): float
    {
        $dates = match($period) {
            'today' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'quarter' => [now()->subQuarter()->startOfQuarter(), now()->subQuarter()->endOfQuarter()],
            'year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            default => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
        };

        return (float) Order::where('tenant_id', $tenantId)
            ->whereBetween('created_at', $dates)
            ->whereIn('status', [OrderStatus::READY->value, OrderStatus::SERVED->value])
            ->sum('total');
    }

    /**
     * Get reviews statistics
     */
    public function getReviewsStats(int $tenantId): array
    {
        $reviews = Review::where('tenant_id', $tenantId)->get();

        if ($reviews->isEmpty()) {
            return [
                'total' => 0,
                'published' => 0,
                'pending' => 0,
                'average_rating' => 0,
                'food_average' => 0,
                'service_average' => 0,
                'ambiance_average' => 0,
                'distribution' => array_fill(0, 5, 0)
            ];
        }

        $published = $reviews->where('status', 'PUBLISHED');

        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[] = $published->filter(fn($r) => round($r->overall_rating) == $i)->count();
        }

        return [
            'total' => $reviews->count(),
            'published' => $published->count(),
            'pending' => $reviews->where('status', 'PENDING')->count(),
            'average_rating' => round($published->avg('overall_rating') ?? 0, 1),
            'food_average' => round($published->avg('food_rating') ?? 0, 1),
            'service_average' => round($published->avg('service_rating') ?? 0, 1),
            'ambiance_average' => round($published->avg('ambiance_rating') ?? 0, 1),
            'distribution' => $distribution
        ];
    }

    /**
     * Get reservations statistics
     */
    public function getReservationsStats(int $tenantId): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $reservations = Reservation::where('tenant_id', $tenantId)->get();
        $monthReservations = $reservations->where('created_at', '>=', $thisMonth);

        $completedThisMonth = $monthReservations->whereIn('status', ['COMPLETED', 'NO_SHOW'])->count();
        $noShowsThisMonth = $monthReservations->where('status', 'NO_SHOW')->count();

        return [
            'today' => $reservations
                ->where('reservation_date', '>=', $today)
                ->where('reservation_date', '<', now()->endOfDay())
                ->whereIn('status', ['PENDING', 'CONFIRMED', 'SEATED'])
                ->count(),
            'upcoming' => $reservations
                ->where('reservation_date', '>=', $today)
                ->whereIn('status', ['PENDING', 'CONFIRMED'])
                ->count(),
            'this_month' => $monthReservations->count(),
            'completed_this_month' => $monthReservations->where('status', 'COMPLETED')->count(),
            'cancelled_this_month' => $monthReservations->where('status', 'CANCELLED')->count(),
            'no_show_rate' => $completedThisMonth > 0
                ? round(($noShowsThisMonth / $completedThisMonth) * 100, 1)
                : 0,
            'average_party_size' => round($reservations->avg('party_size') ?? 0, 1)
        ];
    }

    /**
     * Get comprehensive statistics for the statistics page
     */
    public function getComprehensiveStats(int $tenantId): array
    {
        return [
            'dashboard' => $this->getDashboardStats($tenantId),
            'revenue' => $this->getRevenue($tenantId, 'month'),
            'daily_revenue' => $this->getDailyRevenue($tenantId, 30),
            'orders_by_status' => $this->getOrdersByStatus($tenantId, 'month'),
            'top_dishes' => $this->getTopDishes($tenantId, 10, 'month'),
            'table_stats' => $this->getTableStats($tenantId, 'month'),
            'reviews' => $this->getReviewsStats($tenantId),
            'reservations' => $this->getReservationsStats($tenantId)
        ];
    }
}
