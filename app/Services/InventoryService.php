<?php

namespace App\Services;

use App\Models\Dish;
use App\Models\Tenant;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Decrease stock after order is placed
     */
    public function decrementStock(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $dish = $item->dish;

                if ($dish && $dish->stock_quantity !== null) {
                    $newStock = max(0, $dish->stock_quantity - $item->quantity);
                    $dish->update(['stock_quantity' => $newStock]);

                    // Check if stock is low
                    if ($newStock <= 5) {
                        $this->checkLowStock($dish);
                    }
                }
            }
        });
    }

    /**
     * Restore stock when order is cancelled
     */
    public function restoreStock(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $dish = $item->dish;

                if ($dish && $dish->stock_quantity !== null) {
                    $dish->increment('stock_quantity', $item->quantity);
                }
            }
        });
    }

    /**
     * Update stock for a specific dish
     */
    public function updateStock(Dish $dish, int $quantity): Dish
    {
        $dish->update(['stock_quantity' => max(0, $quantity)]);

        if ($quantity <= 5) {
            $this->checkLowStock($dish);
        }

        return $dish->fresh();
    }

    /**
     * Add stock to a dish
     */
    public function addStock(Dish $dish, int $quantity): Dish
    {
        $dish->increment('stock_quantity', $quantity);
        return $dish->fresh();
    }

    /**
     * Get all dishes with low stock for a tenant
     */
    public function getLowStockDishes(Tenant $tenant, int $threshold = 10): Collection
    {
        return $tenant->dishes()
            ->where('active', true)
            ->where('stock_quantity', '<=', $threshold)
            ->with('category')
            ->orderBy('stock_quantity')
            ->get();
    }

    /**
     * Get all out-of-stock dishes for a tenant
     */
    public function getOutOfStockDishes(Tenant $tenant): Collection
    {
        return $tenant->dishes()
            ->where('active', true)
            ->where('stock_quantity', '<=', 0)
            ->with('category')
            ->get();
    }

    /**
     * Check and handle low stock for a dish
     */
    protected function checkLowStock(Dish $dish): void
    {
        $tenant = $dish->tenant ?? $dish->category?->menu?->tenant;

        if (!$tenant) {
            return;
        }

        // Log low stock warning
        Log::warning("Low stock alert: {$dish->name} has only {$dish->stock_quantity} units left", [
            'dish_id' => $dish->id,
            'tenant_id' => $tenant->id,
            'stock' => $dish->stock_quantity
        ]);

        // Auto-disable if out of stock
        if ($dish->stock_quantity <= 0 && $dish->active) {
            $dish->update(['active' => false]);
            Log::info("Dish {$dish->name} auto-disabled due to out of stock");
        }
    }

    /**
     * Run daily stock check and send alerts
     */
    public function runDailyStockCheck(): void
    {
        $tenants = Tenant::where('is_active', true)->get();

        foreach ($tenants as $tenant) {
            $lowStockDishes = $this->getLowStockDishes($tenant);

            if ($lowStockDishes->isNotEmpty()) {
                $this->notificationService->sendLowStockAlert($tenant);
            }
        }
    }

    /**
     * Get stock report for a tenant
     */
    public function getStockReport(Tenant $tenant): array
    {
        $dishes = $tenant->dishes()->with('category')->get();

        return [
            'total_dishes' => $dishes->count(),
            'active_dishes' => $dishes->where('active', true)->count(),
            'out_of_stock' => $dishes->where('stock_quantity', '<=', 0)->count(),
            'low_stock' => $dishes->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10)->count(),
            'healthy_stock' => $dishes->where('stock_quantity', '>', 10)->count(),
            'dishes_by_category' => $dishes->groupBy('category.name')->map->count(),
            'low_stock_items' => $dishes->where('stock_quantity', '<=', 10)
                ->sortBy('stock_quantity')
                ->take(10)
                ->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'category' => $d->category->name ?? 'N/A',
                    'stock' => $d->stock_quantity,
                    'active' => $d->active
                ])
                ->values()
        ];
    }

    /**
     * Bulk update stock for multiple dishes
     */
    public function bulkUpdateStock(array $stockUpdates): int
    {
        $updated = 0;

        DB::transaction(function () use ($stockUpdates, &$updated) {
            foreach ($stockUpdates as $dishId => $quantity) {
                $dish = Dish::find($dishId);
                if ($dish) {
                    $dish->update(['stock_quantity' => max(0, (int) $quantity)]);
                    $updated++;
                }
            }
        });

        return $updated;
    }
}
