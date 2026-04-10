<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an action on a model.
     */
    public function log(
        string $action,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): AuditLog {
        $tenantId = $this->getTenantId($model);

        if (!$tenantId) {
            throw new \InvalidArgumentException('Cannot audit model without tenant_id');
        }

        $changedFields = $this->getChangedFields($oldValues, $newValues);

        return AuditLog::create([
            'user_id' => Auth::id(),
            'tenant_id' => $tenantId,
            'action' => $action,
            'entity_type' => get_class($model),
            'entity_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $changedFields,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'description' => $description ?? $this->generateDescription($action, $model),
        ]);
    }

    /**
     * Log a model creation.
     */
    public function logCreated(Model $model, ?string $description = null): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_CREATED,
            $model,
            null,
            $this->getAuditableAttributes($model),
            $description
        );
    }

    /**
     * Log a model update.
     */
    public function logUpdated(Model $model, array $oldValues, ?string $description = null): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_UPDATED,
            $model,
            $oldValues,
            $this->getAuditableAttributes($model),
            $description
        );
    }

    /**
     * Log a model deletion.
     */
    public function logDeleted(Model $model, ?string $description = null): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_DELETED,
            $model,
            $this->getAuditableAttributes($model),
            null,
            $description
        );
    }

    /**
     * Log a model restoration (soft delete).
     */
    public function logRestored(Model $model, ?string $description = null): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_RESTORED,
            $model,
            null,
            $this->getAuditableAttributes($model),
            $description
        );
    }

    /**
     * Log a status change.
     */
    public function logStatusChanged(Model $model, string $oldStatus, string $newStatus, ?string $description = null): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_STATUS_CHANGED,
            $model,
            ['status' => $oldStatus],
            ['status' => $newStatus],
            $description ?? "Statut changé de {$oldStatus} à {$newStatus}"
        );
    }

    /**
     * Log a user login.
     */
    public function logLogin(Model $user): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user->getKey(),
            'tenant_id' => $user->tenant_id,
            'action' => AuditLog::ACTION_LOGIN,
            'entity_type' => get_class($user),
            'entity_id' => $user->getKey(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'description' => "Connexion de {$user->name}",
        ]);
    }

    /**
     * Log a user logout.
     */
    public function logLogout(Model $user): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user->getKey(),
            'tenant_id' => $user->tenant_id,
            'action' => AuditLog::ACTION_LOGOUT,
            'entity_type' => get_class($user),
            'entity_id' => $user->getKey(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'description' => "Déconnexion de {$user->name}",
        ]);
    }

    /**
     * Get audit trail for a specific model.
     */
    public function getAuditTrail(Model $model, int $limit = 50): Collection
    {
        return AuditLog::where('entity_type', get_class($model))
            ->where('entity_id', $model->getKey())
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent actions for a tenant.
     */
    public function getRecentActions(int $tenantId, int $limit = 100): Collection
    {
        return AuditLog::forTenant($tenantId)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get actions by user.
     */
    public function getActionsByUser(int $userId, int $limit = 100): Collection
    {
        return AuditLog::byUser($userId)
            ->with('tenant')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get actions for a date range.
     */
    public function getActionsForDateRange(int $tenantId, $startDate, $endDate): Collection
    {
        return AuditLog::forTenant($tenantId)
            ->dateBetween($startDate, $endDate)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get the tenant ID from a model.
     */
    protected function getTenantId(Model $model): ?int
    {
        // Direct tenant_id
        if (isset($model->tenant_id)) {
            return $model->tenant_id;
        }

        // For Tenant model itself
        if ($model instanceof \App\Models\Tenant) {
            return $model->getKey();
        }

        // For models with menu relation (Category)
        if (method_exists($model, 'menu') && $model->menu) {
            return $model->menu->tenant_id;
        }

        // For models with dish relation (Variant, Option)
        if (method_exists($model, 'dish') && $model->dish) {
            return $model->dish->tenant_id;
        }

        // For models with order relation (OrderItem)
        if (method_exists($model, 'order') && $model->order) {
            return $model->order->tenant_id;
        }

        return null;
    }

    /**
     * Get auditable attributes from a model.
     */
    protected function getAuditableAttributes(Model $model): array
    {
        $attributes = $model->getAttributes();

        // Remove sensitive fields
        $hiddenFields = ['password', 'remember_token', 'api_token'];

        return array_diff_key($attributes, array_flip($hiddenFields));
    }

    /**
     * Get list of changed fields.
     */
    protected function getChangedFields(?array $oldValues, ?array $newValues): ?array
    {
        if (!$oldValues || !$newValues) {
            return null;
        }

        $changed = [];

        foreach ($newValues as $key => $value) {
            if (!array_key_exists($key, $oldValues) || $oldValues[$key] !== $value) {
                $changed[] = $key;
            }
        }

        return $changed ?: null;
    }

    /**
     * Generate a description for the action.
     */
    protected function generateDescription(string $action, Model $model): string
    {
        $entityName = class_basename($model);
        $userName = Auth::user()?->name ?? 'Système';

        $entityLabels = [
            'Dish' => 'le plat',
            'Category' => 'la catégorie',
            'Menu' => 'le menu',
            'Order' => 'la commande',
            'Table' => 'la table',
            'Tenant' => 'le restaurant',
            'User' => 'l\'utilisateur',
            'Variant' => 'la variante',
            'Option' => 'l\'option',
        ];

        $actionLabels = [
            AuditLog::ACTION_CREATED => 'a créé',
            AuditLog::ACTION_UPDATED => 'a modifié',
            AuditLog::ACTION_DELETED => 'a supprimé',
            AuditLog::ACTION_RESTORED => 'a restauré',
        ];

        $entityLabel = $entityLabels[$entityName] ?? $entityName;
        $actionLabel = $actionLabels[$action] ?? $action;

        $identifier = $model->name ?? $model->numero ?? $model->code ?? "#{$model->getKey()}";

        return "{$userName} {$actionLabel} {$entityLabel} \"{$identifier}\"";
    }
}
