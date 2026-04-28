<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Services\AuditService;

class TenantObserver
{
    /**
     * Cache statique pour stocker les anciennes valeurs (évite de polluer le modèle)
     */
    protected static array $oldValuesCache = [];

    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Handle the Tenant "created" event.
     */
    public function created(Tenant $tenant): void
    {
        $this->auditService->logCreated($tenant, "Restaurant \"{$tenant->name}\" créé");
    }

    /**
     * Handle the Tenant "updating" event.
     */
    public function updating(Tenant $tenant): void
    {
        // Stocker les anciennes valeurs dans un cache statique
        static::$oldValuesCache[$tenant->id] = $tenant->getOriginal();
    }

    /**
     * Handle the Tenant "updated" event.
     */
    public function updated(Tenant $tenant): void
    {
        if (isset(static::$oldValuesCache[$tenant->id])) {
            $this->auditService->logUpdated($tenant, static::$oldValuesCache[$tenant->id]);
            unset(static::$oldValuesCache[$tenant->id]);
        }

        // Invalider le cache slug (ancien et nouveau slug en cas de changement)
        Tenant::forgetSlugCache($tenant->slug);
        if ($tenant->wasChanged('slug')) {
            Tenant::forgetSlugCache($tenant->getOriginal('slug'));
        }
    }

    /**
     * Handle the Tenant "deleted" event.
     */
    public function deleted(Tenant $tenant): void
    {
        $this->auditService->logDeleted($tenant, "Restaurant \"{$tenant->name}\" supprimé");

        // Invalider le cache slug
        Tenant::forgetSlugCache($tenant->slug);

        if (isset(static::$oldValuesCache[$tenant->id])) {
            unset(static::$oldValuesCache[$tenant->id]);
        }
    }
}
