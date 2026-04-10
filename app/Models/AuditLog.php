<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'changed_fields',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Action constants
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_RESTORED = 'restored';
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_STATUS_CHANGED = 'status_changed';

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant this log belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the entity that was audited (polymorphic).
     */
    public function entity(): ?Model
    {
        if (!$this->entity_type || !$this->entity_id) {
            return null;
        }

        $modelClass = $this->entity_type;

        if (!class_exists($modelClass)) {
            return null;
        }

        return $modelClass::withTrashed()->find($this->entity_id);
    }

    /**
     * Scope to filter by tenant.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to filter by entity type.
     */
    public function scopeForEntityType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Scope to filter by action.
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get a human-readable action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATED => 'Créé',
            self::ACTION_UPDATED => 'Modifié',
            self::ACTION_DELETED => 'Supprimé',
            self::ACTION_RESTORED => 'Restauré',
            self::ACTION_LOGIN => 'Connexion',
            self::ACTION_LOGOUT => 'Déconnexion',
            self::ACTION_STATUS_CHANGED => 'Statut modifié',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get a short entity type name.
     */
    public function getEntityShortNameAttribute(): string
    {
        if (!$this->entity_type) {
            return 'N/A';
        }

        return class_basename($this->entity_type);
    }

    /**
     * Get entity type label in French.
     */
    public function getEntityTypeLabelAttribute(): string
    {
        return match($this->entity_short_name) {
            'Dish' => 'Plat',
            'Category' => 'Catégorie',
            'Menu' => 'Menu',
            'Order' => 'Commande',
            'Table' => 'Table',
            'Tenant' => 'Restaurant',
            'User' => 'Utilisateur',
            'Variant' => 'Variante',
            'Option' => 'Option',
            default => $this->entity_short_name,
        };
    }
}
