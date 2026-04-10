<?php

namespace App\Services;

use App\Mail\OrderConfirmation;
use App\Mail\OrderStatusUpdate;
use App\Mail\LowStockAlert;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation(Order $order, ?string $customerEmail = null): void
    {
        try {
            // If customer email provided, send to customer
            if ($customerEmail) {
                Mail::to($customerEmail)->send(new OrderConfirmation($order));
            }

            // Send to tenant admins
            $this->notifyTenantAdmins($order->tenant, new OrderConfirmation($order));

            Log::info("Order confirmation sent for order #{$order->order_number}");
        } catch (\Exception $e) {
            Log::error("Failed to send order confirmation: " . $e->getMessage());
        }
    }

    /**
     * Send order status update email
     */
    public function sendOrderStatusUpdate(Order $order, string $previousStatus, ?string $customerEmail = null): void
    {
        try {
            // Only send for significant status changes
            if (!in_array($order->status, ['PRET', 'SERVI', 'ANNULE'])) {
                return;
            }

            if ($customerEmail) {
                Mail::to($customerEmail)->send(new OrderStatusUpdate($order, $previousStatus));
            }

            Log::info("Order status update sent for order #{$order->order_number}: {$previousStatus} -> {$order->status}");
        } catch (\Exception $e) {
            Log::error("Failed to send order status update: " . $e->getMessage());
        }
    }

    /**
     * Send low stock alert to tenant admins
     */
    public function sendLowStockAlert(Tenant $tenant): void
    {
        try {
            $lowStockDishes = $tenant->dishes()
                ->where('active', true)
                ->where('stock_quantity', '<=', 10)
                ->with('category')
                ->get();

            if ($lowStockDishes->isEmpty()) {
                return;
            }

            $this->notifyTenantAdmins($tenant, new LowStockAlert($tenant, $lowStockDishes));

            Log::info("Low stock alert sent to tenant: {$tenant->name}");
        } catch (\Exception $e) {
            Log::error("Failed to send low stock alert: " . $e->getMessage());
        }
    }

    /**
     * Notify all admin users of a tenant
     */
    protected function notifyTenantAdmins(Tenant $tenant, $mailable): void
    {
        $admins = $tenant->users()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['ADMIN', 'SUPER_ADMIN']);
            })
            ->get();

        foreach ($admins as $admin) {
            if ($admin->email) {
                Mail::to($admin->email)->queue($mailable);
            }
        }
    }

    /**
     * Send notification to kitchen (KDS)
     * This could be expanded to use WebSockets or push notifications
     */
    public function notifyKitchen(Order $order): void
    {
        // For now, log the notification
        // In the future, this could trigger WebSocket events
        Log::info("New order #{$order->order_number} sent to kitchen");

        // You could add: broadcast(new NewOrderEvent($order));
    }

    /**
     * Send daily report email
     */
    public function sendDailyReport(Tenant $tenant): void
    {
        // Implementation for daily report email
        // This would typically be called by a scheduled task
    }
}
