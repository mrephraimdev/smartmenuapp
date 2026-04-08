<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Tenant;
use App\Enums\PaymentMethod;
use Illuminate\Support\Facades\View;

class PrintService
{
    /**
     * Generate printable HTML for kitchen ticket
     */
    public function generateKitchenTicket(Order $order): string
    {
        $order->load(['items.dish', 'items.variant', 'table', 'tenant']);

        return View::make('prints.kitchen-ticket', [
            'order' => $order,
            'tenant' => $order->tenant,
            'table' => $order->table,
            'items' => $order->items,
            'printedAt' => now()
        ])->render();
    }

    /**
     * Generate printable HTML for customer receipt
     */
    public function generateReceipt(Order $order): string
    {
        $order->load(['items.dish', 'items.variant', 'table', 'tenant']);

        return View::make('prints.receipt', [
            'order' => $order,
            'tenant' => $order->tenant,
            'table' => $order->table,
            'items' => $order->items,
            'printedAt' => now()
        ])->render();
    }

    /**
     * Generate daily report for printing
     */
    public function generateDailyReport(Tenant $tenant, string $date): string
    {
        $orders = Order::where('tenant_id', $tenant->id)
            ->whereDate('created_at', $date)
            ->with(['items.dish', 'table'])
            ->get();

        // Paiements du jour
        $payments = Payment::where('tenant_id', $tenant->id)
            ->whereDate('created_at', $date)
            ->where('status', 'SUCCESS')
            ->get();

        // Stats par méthode de paiement
        $paymentsByMethod = [];
        foreach (PaymentMethod::cashierMethods() as $method) {
            $methodPayments = $payments->where('method', $method->value);
            $paymentsByMethod[$method->value] = [
                'label' => $method->label(),
                'count' => $methodPayments->count(),
                'total' => $methodPayments->sum('amount'),
            ];
        }

        $stats = [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->whereIn('status', ['PRET', 'SERVI'])->sum('total'),
            'served_orders' => $orders->where('status', 'SERVI')->count(),
            'cancelled_orders' => $orders->where('status', 'ANNULE')->count(),
            'popular_dishes' => $this->getPopularDishes($orders),
            // Stats paiements
            'total_payments' => $payments->sum('amount'),
            'payment_count' => $payments->count(),
            'payments_by_method' => $paymentsByMethod,
            'unpaid_orders' => $orders->where('payment_status', '!=', 'PAID')->where('status', '!=', 'ANNULE')->count(),
            'unpaid_amount' => $orders->where('payment_status', '!=', 'PAID')->where('status', '!=', 'ANNULE')->sum(fn($o) => $o->total - $o->paid_amount),
        ];

        return View::make('prints.daily-report', [
            'tenant' => $tenant,
            'date' => $date,
            'orders' => $orders,
            'stats' => $stats,
            'printedAt' => now()
        ])->render();
    }

    /**
     * Get popular dishes from orders collection
     */
    protected function getPopularDishes($orders): array
    {
        $dishes = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $dishName = $item->dish->name ?? 'Unknown';
                if (!isset($dishes[$dishName])) {
                    $dishes[$dishName] = 0;
                }
                $dishes[$dishName] += $item->quantity;
            }
        }

        arsort($dishes);
        return array_slice($dishes, 0, 10, true);
    }
}
