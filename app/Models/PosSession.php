<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'session_number',
        'status',
        'opened_at',
        'closed_at',
        'opening_float',
        'expected_cash',
        'actual_cash',
        'cash_difference',
        'total_sales',
        'total_orders',
        'total_items',
        'cash_sales',
        'card_sales',
        'mobile_sales',
        'cancelled_orders',
        'refunds_total',
        'opening_notes',
        'closing_notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_float' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'mobile_sales' => 'decimal:2',
        'refunds_total' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the session
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user (cashier) that opened the session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the orders for this session
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if session is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'OPEN';
    }

    /**
     * Check if session is closed
     */
    public function isClosed(): bool
    {
        return $this->status === 'CLOSED';
    }

    /**
     * Get session duration in minutes
     */
    public function getDurationInMinutes(): ?int
    {
        if (!$this->closed_at) {
            return now()->diffInMinutes($this->opened_at);
        }

        return $this->closed_at->diffInMinutes($this->opened_at);
    }

    /**
     * Get formatted session duration (e.g., "2h 30min")
     */
    public function getDurationFormatted(): string
    {
        $minutes = $this->getDurationInMinutes();

        if ($minutes === null) {
            return '0 min';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return $remainingMinutes > 0
                ? "{$hours}h {$remainingMinutes}min"
                : "{$hours}h";
        }

        return "{$remainingMinutes} min";
    }

    /**
     * Check if there's a cash discrepancy
     */
    public function hasCashDiscrepancy(): bool
    {
        return $this->cash_difference !== null && abs($this->cash_difference) > 0.01;
    }

    /**
     * Get the status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'OPEN' => 'Ouvert',
            'CLOSED' => 'Fermé',
            default => $this->status
        };
    }

    /**
     * Scope to get open sessions
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'OPEN');
    }

    /**
     * Scope to get closed sessions
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'CLOSED');
    }

    /**
     * Scope to filter by tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
