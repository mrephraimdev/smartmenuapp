<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\AuditService;

class CategoryObserver
{
    /**
     * Store old values temporarily before update
     */
    protected static array $oldValues = [];

    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        // Load menu relation if not loaded
        if (!$category->relationLoaded('menu')) {
            $category->load('menu');
        }

        if ($category->menu?->tenant_id) {
            $this->auditService->logCreated($category);
        }
    }

    /**
     * Handle the Category "updating" event.
     */
    public function updating(Category $category): void
    {
        if (!$category->relationLoaded('menu')) {
            $category->load('menu');
        }

        if ($category->menu?->tenant_id) {
            self::$oldValues[$category->id] = $category->getOriginal();
        }
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        if (!$category->relationLoaded('menu')) {
            $category->load('menu');
        }

        if ($category->menu?->tenant_id && isset(self::$oldValues[$category->id])) {
            $oldValues = self::$oldValues[$category->id];
            $this->auditService->logUpdated($category, $oldValues);
            unset(self::$oldValues[$category->id]);
        }
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        if (!$category->relationLoaded('menu')) {
            $category->load('menu');
        }

        if ($category->menu?->tenant_id) {
            $this->auditService->logDeleted($category);
        }
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        if (!$category->relationLoaded('menu')) {
            $category->load('menu');
        }

        if ($category->menu?->tenant_id) {
            $this->auditService->logRestored($category);
        }
    }
}
