<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaiterCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'table_id',
        'call_type',
        'status',
        'handled_by',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Relation avec le tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relation avec la table
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Relation avec l'utilisateur qui a pris en charge l'appel
     */
    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Scope pour les appels en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope pour les appels d'un tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope pour les appels récents (dernières 2 heures)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subHours(2));
    }

    /**
     * Marquer comme pris en charge
     */
    public function acknowledge(User $user): void
    {
        $this->update([
            'status' => 'ACKNOWLEDGED',
            'handled_by' => $user->id,
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Marquer comme résolu
     */
    public function resolve(): void
    {
        $this->update([
            'status' => 'RESOLVED',
            'resolved_at' => now(),
        ]);
    }

    /**
     * Obtenir le label du type d'appel
     */
    public function getCallTypeLabelAttribute(): string
    {
        return match($this->call_type) {
            'SERVICE' => 'Demande de service',
            'QUESTION' => 'Question',
            'URGENCE' => 'Urgence',
            default => $this->call_type,
        };
    }

    /**
     * Obtenir la couleur du badge selon le type
     */
    public function getCallTypeColorAttribute(): string
    {
        return match($this->call_type) {
            'SERVICE' => 'blue',
            'QUESTION' => 'yellow',
            'URGENCE' => 'red',
            default => 'gray',
        };
    }

    /**
     * Vérifie si l'appel est urgent
     */
    public function isUrgent(): bool
    {
        return $this->call_type === 'URGENCE';
    }
}
