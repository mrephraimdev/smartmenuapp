<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relations
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obtenir le rôle sous forme d'enum
     */
    public function getRoleEnum(): ?UserRole
    {
        return $this->role ? UserRole::tryFrom($this->role) : null;
    }

    /**
     * Vérifie si l'utilisateur a le rôle spécifié
     */
    public function hasRole(string|UserRole $roleName): bool
    {
        $roleValue = $roleName instanceof UserRole ? $roleName->value : $roleName;
        return $this->role === $roleValue;
    }

    /**
     * Vérifie si l'utilisateur a l'un des rôles spécifiés
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vérifie si l'utilisateur est un Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SUPER_ADMIN);
    }

    /**
     * Vérifie si l'utilisateur est un Admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::ADMIN);
    }

    /**
     * Vérifie si l'utilisateur peut accéder au POS
     */
    public function canAccessPOS(): bool
    {
        $roleEnum = $this->getRoleEnum();
        return $roleEnum ? $roleEnum->canAccessPOS() : false;
    }

    /**
     * Vérifie si l'utilisateur peut accéder au KDS
     */
    public function canAccessKDS(): bool
    {
        $roleEnum = $this->getRoleEnum();
        return $roleEnum ? $roleEnum->canAccessKDS() : false;
    }

    /**
     * Vérifie si l'utilisateur peut gérer les paiements
     */
    public function canManagePayments(): bool
    {
        $roleEnum = $this->getRoleEnum();
        return $roleEnum ? $roleEnum->canManagePayments() : false;
    }

    /**
     * Vérifie si l'utilisateur a une permission spécifique
     */
    public function hasPermission(string $permission): bool
    {
        $roleEnum = $this->getRoleEnum();
        if (!$roleEnum) {
            return false;
        }

        $permissions = $roleEnum->permissions();
        return in_array('*', $permissions) || in_array($permission, $permissions);
    }
}