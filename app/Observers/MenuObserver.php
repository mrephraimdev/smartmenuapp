<?php

namespace App\Observers;

use App\Models\Menu;
use App\Services\AuditService;

class MenuObserver
{
    /**
     * Store old values temporarily before update
     */
    protected static array $oldValues = [];

    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Handle the Menu "created" event.
     */
    public function created(Menu $menu): void
    {
        if ($menu->tenant_id) {
            $this->auditService->logCreated($menu);
        }
    }

    /**
     * Handle the Menu "updating" event.
     */
    public function updating(Menu $menu): void
    {
        if ($menu->tenant_id) {
            self::$oldValues[$menu->id] = $menu->getOriginal();
        }
    }

    /**
     * Handle the Menu "updated" event.
     */
    public function updated(Menu $menu): void
    {
        if ($menu->tenant_id && isset(self::$oldValues[$menu->id])) {
            $oldValues = self::$oldValues[$menu->id];
            $this->auditService->logUpdated($menu, $oldValues);
            unset(self::$oldValues[$menu->id]);
        }
    }

    /**
     * Handle the Menu "deleted" event.
     */
    public function deleted(Menu $menu): void
    {
        if ($menu->tenant_id) {
            $this->auditService->logDeleted($menu);
        }
    }

    /**
     * Handle the Menu "restored" event.
     */
    public function restored(Menu $menu): void
    {
        if ($menu->tenant_id) {
            $this->auditService->logRestored($menu);
        }
    }
}
