<?php

namespace App\Observers;

use App\Models\Dish;
use App\Services\AuditService;

class DishObserver
{
    /**
     * Store old values temporarily before update
     */
    protected static array $oldValues = [];

    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Handle the Dish "created" event.
     */
    public function created(Dish $dish): void
    {
        if ($dish->tenant_id) {
            $this->auditService->logCreated($dish);
        }
    }

    /**
     * Handle the Dish "updating" event.
     * Store original values before update.
     */
    public function updating(Dish $dish): void
    {
        if ($dish->tenant_id) {
            self::$oldValues[$dish->id] = $dish->getOriginal();
        }
    }

    /**
     * Handle the Dish "updated" event.
     */
    public function updated(Dish $dish): void
    {
        if ($dish->tenant_id && isset(self::$oldValues[$dish->id])) {
            $oldValues = self::$oldValues[$dish->id];
            $this->auditService->logUpdated($dish, $oldValues);
            unset(self::$oldValues[$dish->id]);
        }
    }

    /**
     * Handle the Dish "deleted" event.
     */
    public function deleted(Dish $dish): void
    {
        if ($dish->tenant_id) {
            $this->auditService->logDeleted($dish);
        }
    }

    /**
     * Handle the Dish "restored" event.
     */
    public function restored(Dish $dish): void
    {
        if ($dish->tenant_id) {
            $this->auditService->logRestored($dish);
        }
    }
}
