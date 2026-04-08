<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dish;
use App\Models\Variant;
use App\Models\Tenant;
use App\Enums\OrderStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service de gestion des commandes
 *
 * Ce service gère tout le cycle de vie d'une commande:
 * - Création avec calcul automatique des totaux
 * - Progression des statuts (RECU → PREP → PRET → SERVI)
 * - Annulation avec restauration du stock
 * - Récupération pour le KDS (Kitchen Display System)
 *
 * @package App\Services
 * @author SmartMenu Team
 */
class OrderService
{
    protected ?NotificationService $notificationService = null;
    protected ?InventoryService $inventoryService = null;

    /**
     * Constructeur du service
     *
     * @param NotificationService|null $notificationService Service de notifications (optionnel)
     * @param InventoryService|null $inventoryService Service de gestion des stocks (optionnel)
     */
    public function __construct(
        ?NotificationService $notificationService = null,
        ?InventoryService $inventoryService = null
    ) {
        $this->notificationService = $notificationService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Créer une nouvelle commande avec ses items
     *
     * Calcule automatiquement le total en tenant compte:
     * - Prix de base des plats
     * - Prix des variantes sélectionnées
     * - Quantités commandées
     *
     * @param array $data Données de la commande
     *   - tenant_id: int ID du restaurant
     *   - table_id: int ID de la table
     *   - items: array Liste des items [{dish_id, quantity, variant_id?, options?, notes?}]
     *   - notes: string|null Notes générales de la commande
     *   - customer_email: string|null Email pour les notifications
     *
     * @return Order La commande créée avec ses relations chargées
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si un plat n'existe pas
     * @throws \Throwable En cas d'erreur de transaction
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $total = 0;
            $orderItems = [];

            foreach ($data['items'] as $item) {
                $dish = Dish::findOrFail($item['dish_id']);
                $itemPrice = $dish->price_base;

                if (!empty($item['variant_id'])) {
                    $variant = Variant::find($item['variant_id']);
                    if ($variant) {
                        $itemPrice += $variant->extra_price;
                    }
                }

                $itemTotal = $itemPrice * $item['quantity'];
                $total += $itemTotal;

                $orderItems[] = [
                    'dish_id' => $item['dish_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'options' => json_encode($item['options'] ?? []),
                    'quantity' => $item['quantity'],
                    'unit_price' => $itemPrice,
                    'notes' => $item['notes'] ?? ''
                ];
            }

            $order = Order::createWithNumber([
                'tenant_id' => $data['tenant_id'],
                'table_id' => $data['table_id'],
                'status' => OrderStatus::RECEIVED->value,
                'total' => $total,
                'notes' => $data['notes'] ?? ''
            ]);

            foreach ($orderItems as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            $order = $order->load(['items.dish', 'items.variant', 'table']);

            // Decrement stock
            if ($this->inventoryService) {
                $this->inventoryService->decrementStock($order);
            }

            // Send notification
            if ($this->notificationService) {
                $customerEmail = $data['customer_email'] ?? null;
                $this->notificationService->sendOrderConfirmation($order, $customerEmail);
                $this->notificationService->notifyKitchen($order);
            }

            return $order;
        });
    }

    /**
     * Mettre à jour le statut d'une commande
     *
     * Envoie une notification si le service est configuré.
     *
     * @param Order $order La commande à mettre à jour
     * @param OrderStatus $newStatus Le nouveau statut
     * @param string|null $customerEmail Email du client (pour notifications)
     *
     * @return Order La commande mise à jour
     */
    public function updateStatus(Order $order, OrderStatus $newStatus, ?string $customerEmail = null): Order
    {
        $previousStatus = $order->status;
        $order->update(['status' => $newStatus->value]);

        // Send status update notification
        if ($this->notificationService && $previousStatus !== $newStatus->value) {
            $this->notificationService->sendOrderStatusUpdate($order->fresh(), $previousStatus, $customerEmail);
        }

        return $order->fresh();
    }

    /**
     * Faire progresser une commande vers le statut suivant
     *
     * Progression automatique: RECU → PREP → PRET → SERVI
     *
     * @param Order $order La commande à faire progresser
     *
     * @return Order|null La commande mise à jour, ou null si déjà au statut final
     */
    public function progressStatus(Order $order): ?Order
    {
        $currentStatus = OrderStatus::from($order->status);
        $nextStatus = $currentStatus->nextStatus();

        if ($nextStatus) {
            return $this->updateStatus($order, $nextStatus);
        }

        return null;
    }

    /**
     * Get orders by tenant
     */
    public function getOrdersByTenant(int $tenantId, ?array $statuses = null): Collection
    {
        $query = Order::with(['table', 'items.dish', 'items.variant'])
            ->where('tenant_id', $tenantId);

        if ($statuses) {
            $query->whereIn('status', $statuses);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get active orders (not served or cancelled)
     */
    public function getActiveOrders(int $tenantId): Collection
    {
        return $this->getOrdersByTenant($tenantId, OrderStatus::activeValues());
    }

    /**
     * Get orders for KDS view, grouped by status
     */
    public function getOrdersForKDS(int $tenantId): array
    {
        $orders = $this->getActiveOrders($tenantId);

        return [
            'RECU' => $orders->where('status', OrderStatus::RECEIVED->value)->values(),
            'PREP' => $orders->where('status', OrderStatus::PREPARING->value)->values(),
            'PRET' => $orders->where('status', OrderStatus::READY->value)->values(),
        ];
    }

    /**
     * Generate order number
     */
    public function generateOrderNumber(int $tenantId): string
    {
        return Order::generateOrderNumber($tenantId);
    }

    /**
     * Get order with full details
     */
    public function getOrderWithDetails(int $orderId): ?Order
    {
        return Order::with(['table', 'items.dish', 'items.variant', 'tenant'])
            ->find($orderId);
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Order $order, string $reason = ''): Order
    {
        $notes = $order->notes;
        if ($reason) {
            $notes .= "\n[ANNULATION] " . $reason;
        }

        $previousStatus = $order->status;
        $order->update([
            'status' => OrderStatus::CANCELLED->value,
            'notes' => $notes
        ]);

        // Restore stock when order is cancelled
        if ($this->inventoryService) {
            $this->inventoryService->restoreStock($order);
        }

        // Send cancellation notification
        if ($this->notificationService) {
            $this->notificationService->sendOrderStatusUpdate($order->fresh(), $previousStatus);
        }

        return $order->fresh();
    }

    /**
     * Get today's orders count for a tenant
     */
    public function getTodayOrdersCount(int $tenantId): int
    {
        return Order::where('tenant_id', $tenantId)
            ->whereDate('created_at', now())
            ->count();
    }

    /**
     * Get today's revenue for a tenant
     */
    public function getTodayRevenue(int $tenantId): float
    {
        return Order::where('tenant_id', $tenantId)
            ->whereDate('created_at', now())
            ->whereIn('status', [
                OrderStatus::READY->value,
                OrderStatus::SERVED->value
            ])
            ->sum('total');
    }
}
