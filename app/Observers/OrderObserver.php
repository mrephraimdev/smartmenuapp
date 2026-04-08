<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\AuditService;

class OrderObserver
{
    /**
     * Store old values temporarily before update
     */
    protected static array $oldValues = [];

    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if ($order->tenant_id) {
            $this->auditService->logCreated($order, "Nouvelle commande #{$order->numero} créée");
        }
    }

    /**
     * Handle the Order "updating" event.
     * Store original values before update.
     */
    public function updating(Order $order): void
    {
        if ($order->tenant_id) {
            self::$oldValues[$order->id] = $order->getOriginal();
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->tenant_id && isset(self::$oldValues[$order->id])) {
            $oldValues = self::$oldValues[$order->id];
            $oldStatus = $oldValues['status'] ?? null;
            $newStatus = $order->status;

            // Log status change specifically
            if ($oldStatus && $oldStatus !== $newStatus) {
                $this->auditService->logStatusChanged($order, $oldStatus, $newStatus);
            } else {
                $this->auditService->logUpdated($order, $oldValues);
            }

            unset(self::$oldValues[$order->id]);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        if ($order->tenant_id) {
            $this->auditService->logDeleted($order, "Commande #{$order->numero} supprimée");
        }
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        if ($order->tenant_id) {
            $this->auditService->logRestored($order, "Commande #{$order->numero} restaurée");
        }
    }
}
