<?php

namespace App\Observers;

use App\Models\Table;
use App\Services\AuditService;

class TableObserver
{
    /**
     * Store old values temporarily before update
     */
    protected static array $oldValues = [];

    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Handle the Table "created" event.
     */
    public function created(Table $table): void
    {
        if ($table->tenant_id) {
            $this->auditService->logCreated($table, "Table \"{$table->name}\" créée");
        }
    }

    /**
     * Handle the Table "updating" event.
     */
    public function updating(Table $table): void
    {
        if ($table->tenant_id) {
            self::$oldValues[$table->id] = $table->getOriginal();
        }
    }

    /**
     * Handle the Table "updated" event.
     */
    public function updated(Table $table): void
    {
        if ($table->tenant_id && isset(self::$oldValues[$table->id])) {
            $oldValues = self::$oldValues[$table->id];
            $this->auditService->logUpdated($table, $oldValues);
            unset(self::$oldValues[$table->id]);
        }
    }

    /**
     * Handle the Table "deleted" event.
     */
    public function deleted(Table $table): void
    {
        if ($table->tenant_id) {
            $this->auditService->logDeleted($table, "Table \"{$table->name}\" supprimée");
        }
    }

    /**
     * Handle the Table "restored" event.
     */
    public function restored(Table $table): void
    {
        if ($table->tenant_id) {
            $this->auditService->logRestored($table, "Table \"{$table->name}\" restaurée");
        }
    }
}
