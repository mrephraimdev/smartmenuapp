<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Trait TenantScope
 *
 * Applique automatiquement un filtre tenant_id sur toutes les requêtes Eloquent
 * pour garantir l'isolation des données entre tenants.
 *
 * Les utilisateurs SUPER_ADMIN ont accès à tous les tenants.
 *
 * @package App\Traits
 */
trait TenantScope
{
    /**
     * Boot du trait - Applique le Global Scope
     */
    protected static function bootTenantScope(): void
    {
        // Applique le scope sur toutes les requêtes
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (static::shouldApplyTenantScope()) {
                $builder->where(static::getTenantColumn(), Auth::user()->tenant_id);
            }
        });

        // Lors de la création d'un nouveau modèle, assigne automatiquement le tenant_id
        static::creating(function (Model $model) {
            if (static::shouldApplyTenantScope() && !$model->{static::getTenantColumn()}) {
                $model->{static::getTenantColumn()} = Auth::user()->tenant_id;
            }
        });
    }

    /**
     * Détermine si le scope tenant doit être appliqué
     *
     * @return bool
     */
    protected static function shouldApplyTenantScope(): bool
    {
        // Ne pas appliquer si :
        // 1. L'utilisateur n'est pas authentifié
        if (!Auth::check()) {
            return false;
        }

        // 2. L'utilisateur est SUPER_ADMIN (accès à tous les tenants)
        if (Auth::user()->hasRole('SUPER_ADMIN')) {
            return false;
        }

        // 3. L'utilisateur n'a pas de tenant_id (cas edge)
        if (!Auth::user()->tenant_id) {
            return false;
        }

        return true;
    }

    /**
     * Retourne le nom de la colonne tenant
     *
     * @return string
     */
    protected static function getTenantColumn(): string
    {
        return 'tenant_id';
    }

    /**
     * Query scope pour exclure le filtre tenant
     * Utilisation : Model::withoutTenantScope()->get()
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Query scope pour forcer un tenant spécifique
     * Utilisation : Model::forTenant($tenantId)->get()
     *
     * @param Builder $query
     * @param int $tenantId
     * @return Builder
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')
            ->where(static::getTenantColumn(), $tenantId);
    }

    /**
     * Query scope pour tous les tenants (alias de withoutTenantScope)
     * Utilisation : Model::allTenants()->get()
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeAllTenants(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}
