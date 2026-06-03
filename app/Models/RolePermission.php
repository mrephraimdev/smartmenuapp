<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermission extends Model
{
    protected $fillable = ['tenant_id', 'role', 'permission', 'enabled'];

    protected $casts = ['enabled' => 'boolean'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function getEffective(string $role, string $permission, ?int $tenantId): ?bool
    {
        if ($tenantId) {
            $tenantOverride = static::where('tenant_id', $tenantId)
                ->where('role', $role)
                ->where('permission', $permission)
                ->first();
            if ($tenantOverride) {
                return $tenantOverride->enabled;
            }
        }

        $globalOverride = static::whereNull('tenant_id')
            ->where('role', $role)
            ->where('permission', $permission)
            ->first();

        return $globalOverride?->enabled;
    }
}
