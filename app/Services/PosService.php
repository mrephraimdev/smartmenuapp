<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PosSession;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PosService
{
    /**
     * Open a new POS session
     */
    public function openSession(Tenant $tenant, User $user, float $openingFloat, ?string $notes = null): PosSession
    {
        // Check if user already has an open session
        $existingSession = PosSession::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->where('status', 'OPEN')
            ->first();

        if ($existingSession) {
            throw new \Exception('Vous avez déjà une session ouverte. Fermez-la avant d\'en ouvrir une nouvelle.');
        }

        // Generate session number
        $sessionNumber = $this->generateSessionNumber($tenant->id);

        // Create session
        $session = PosSession::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'session_number' => $sessionNumber,
            'status' => 'OPEN',
            'opened_at' => now(),
            'opening_float' => $openingFloat,
            'opening_notes' => $notes,
        ]);

        return $session;
    }

    /**
     * Close a POS session
     */
    public function closeSession(PosSession $session, float $actualCash, ?string $notes = null): PosSession
    {
        if ($session->isClosed()) {
            throw new \Exception('Cette session est déjà fermée.');
        }

        // Calculate session totals
        $totals = $this->calculateSessionTotals($session);

        // Calculate expected cash (opening float + cash sales - refunds)
        $expectedCash = $session->opening_float + $totals['cash_sales'] - $totals['refunds_total'];
        $cashDifference = $actualCash - $expectedCash;

        // Update session
        $session->update([
            'status' => 'CLOSED',
            'closed_at' => now(),
            'actual_cash' => $actualCash,
            'expected_cash' => $expectedCash,
            'cash_difference' => $cashDifference,
            'total_sales' => $totals['total_sales'],
            'total_orders' => $totals['total_orders'],
            'total_items' => $totals['total_items'],
            'cash_sales' => $totals['cash_sales'],
            'card_sales' => $totals['card_sales'],
            'mobile_sales' => $totals['mobile_sales'],
            'cancelled_orders' => $totals['cancelled_orders'],
            'refunds_total' => $totals['refunds_total'],
            'closing_notes' => $notes,
        ]);

        return $session->fresh();
    }

    /**
     * Get current open session for a user
     */
    public function getCurrentSession(Tenant $tenant, User $user): ?PosSession
    {
        return PosSession::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->where('status', 'OPEN')
            ->first();
    }

    /**
     * Calculate session totals
     */
    protected function calculateSessionTotals(PosSession $session): array
    {
        $orders = $session->orders()
            ->with('items')
            ->get();

        $totalSales = 0;
        $totalOrders = 0;
        $totalItems = 0;
        $cashSales = 0;
        $cardSales = 0;
        $mobileSales = 0;
        $cancelledOrders = 0;
        $refundsTotal = 0;

        foreach ($orders as $order) {
            if ($order->status === 'ANNULE') {
                $cancelledOrders++;
                $refundsTotal += $order->total;
                continue;
            }

            $totalSales += $order->total;
            $totalOrders++;
            $totalItems += $order->items->sum('quantity');

            // Breakdown by payment method (assuming payment_method field exists)
            if (isset($order->payment_method)) {
                switch ($order->payment_method) {
                    case 'CASH':
                    case 'ESPECES':
                        $cashSales += $order->total;
                        break;
                    case 'CARD':
                    case 'CARTE':
                        $cardSales += $order->total;
                        break;
                    case 'MOBILE':
                    case 'MOBILE_MONEY':
                        $mobileSales += $order->total;
                        break;
                    default:
                        $cashSales += $order->total; // Default to cash
                }
            } else {
                // If no payment method specified, assume cash
                $cashSales += $order->total;
            }
        }

        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_items' => $totalItems,
            'cash_sales' => $cashSales,
            'card_sales' => $cardSales,
            'mobile_sales' => $mobileSales,
            'cancelled_orders' => $cancelledOrders,
            'refunds_total' => $refundsTotal,
        ];
    }

    /**
     * Generate unique session number
     */
    protected function generateSessionNumber(int $tenantId): string
    {
        $date = now()->format('Ymd');
        $count = PosSession::where('tenant_id', $tenantId)
            ->whereDate('opened_at', now())
            ->count() + 1;

        return sprintf('POS-%s-T%d-%03d', $date, $tenantId, $count);
    }

    /**
     * Generate Z Report (end of day report) - Full closing report
     */
    public function generateZReport(PosSession $session): array
    {
        if ($session->isOpen()) {
            throw new \Exception('La session doit être fermée pour générer un rapport Z.');
        }

        $orders = $session->orders()
            ->with(['items.dish', 'table'])
            ->get();

        // Group orders by status
        $ordersByStatus = $orders->groupBy('status')
            ->map(fn($group) => $group->count())
            ->toArray();

        // Top dishes sold
        $topDishes = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->where('orders.pos_session_id', $session->id)
            ->whereNotIn('orders.status', ['ANNULE'])
            ->select(
                'dishes.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.unit_price * order_items.quantity) as total_revenue')
            )
            ->groupBy('dishes.id', 'dishes.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // Hourly distribution
        $hourlyDistribution = $orders
            ->whereNotIn('status', ['ANNULE'])
            ->groupBy(fn($order) => $order->created_at->format('H'))
            ->map(fn($group) => [
                'count' => $group->count(),
                'revenue' => $group->sum('total')
            ])
            ->sortKeys()
            ->toArray();

        return [
            'session' => $session,
            'orders' => $orders,
            'orders_by_status' => $ordersByStatus,
            'top_dishes' => $topDishes,
            'hourly_distribution' => $hourlyDistribution,
            'summary' => [
                'duration_minutes' => $session->getDurationInMinutes(),
                'average_order_value' => $session->total_orders > 0 ? $session->total_sales / $session->total_orders : 0,
                'cash_discrepancy' => $session->cash_difference,
            ]
        ];
    }

    /**
     * Generate X Report (intermediate report) - Current status without closing
     */
    public function generateXReport(PosSession $session): array
    {
        if ($session->isClosed()) {
            throw new \Exception('La session est fermée. Utilisez le rapport Z.');
        }

        // Calculate current totals
        $currentTotals = $this->calculateSessionTotals($session);

        $orders = $session->orders()
            ->with(['items.dish', 'table'])
            ->get();

        // Group orders by status
        $ordersByStatus = $orders->groupBy('status')
            ->map(fn($group) => $group->count())
            ->toArray();

        // Top dishes sold so far
        $topDishes = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->where('orders.pos_session_id', $session->id)
            ->whereNotIn('orders.status', ['ANNULE'])
            ->select(
                'dishes.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.unit_price * order_items.quantity) as total_revenue')
            )
            ->groupBy('dishes.id', 'dishes.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // Expected cash based on current sales
        $expectedCash = $session->opening_float + $currentTotals['cash_sales'] - $currentTotals['refunds_total'];

        return [
            'session' => $session,
            'current_totals' => $currentTotals,
            'expected_cash' => $expectedCash,
            'orders_by_status' => $ordersByStatus,
            'top_dishes' => $topDishes,
            'summary' => [
                'duration_minutes' => $session->getDurationInMinutes(),
                'average_order_value' => $currentTotals['total_orders'] > 0 ? $currentTotals['total_sales'] / $currentTotals['total_orders'] : 0,
            ]
        ];
    }

    /**
     * Get session statistics for a tenant (all sessions)
     */
    public function getSessionStatistics(Tenant $tenant, Carbon $startDate, Carbon $endDate): array
    {
        $sessions = PosSession::where('tenant_id', $tenant->id)
            ->whereBetween('opened_at', [$startDate, $endDate])
            ->with('user')
            ->get();

        $totalSales = $sessions->sum('total_sales');
        $totalOrders = $sessions->sum('total_orders');
        $totalSessions = $sessions->count();
        $closedSessions = $sessions->where('status', 'CLOSED')->count();

        // Cash discrepancies
        $cashDiscrepancies = $sessions->where('status', 'CLOSED')
            ->where('cash_difference', '!=', 0)
            ->values();

        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_sessions' => $totalSessions,
            'closed_sessions' => $closedSessions,
            'open_sessions' => $totalSessions - $closedSessions,
            'average_sales_per_session' => $totalSessions > 0 ? $totalSales / $totalSessions : 0,
            'average_orders_per_session' => $totalSessions > 0 ? $totalOrders / $totalSessions : 0,
            'sessions_with_discrepancies' => $cashDiscrepancies->count(),
            'total_discrepancy' => $cashDiscrepancies->sum('cash_difference'),
            'sessions' => $sessions,
        ];
    }
}
