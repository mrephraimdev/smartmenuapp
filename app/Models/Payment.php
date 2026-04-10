<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'tenant_id',
        'amount',
        'method',
        'status',
        'transaction_id',
        'external_reference',
        'amount_received',
        'change_given',
        'provider_response',
        'processed_by',
        'processed_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'change_given' => 'decimal:2',
        'provider_response' => 'array',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    // ============================================
    // RELATIONS
    // ============================================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getMethodEnumAttribute(): PaymentMethod
    {
        return PaymentMethod::from($this->method);
    }

    public function getStatusEnumAttribute(): PaymentStatus
    {
        return PaymentStatus::tryFrom($this->status) ?? PaymentStatus::PENDING;
    }

    public function getMethodLabelAttribute(): string
    {
        return $this->methodEnum->label();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->statusEnum->label();
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', ' ') . ' FCFA';
    }

    public function getMethodColorAttribute(): string
    {
        return $this->methodEnum->badgeClass();
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'SUCCESS');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeByMethod($query, string $method)
    {
        return $query->where('method', $method);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now());
    }

    // ============================================
    // METHODS
    // ============================================

    public function isSuccessful(): bool
    {
        return $this->status === 'SUCCESS';
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isCash(): bool
    {
        return $this->method === PaymentMethod::CASH->value;
    }

    public function markAsSuccess(?int $processedBy = null): void
    {
        $this->update([
            'status' => 'SUCCESS',
            'processed_by' => $processedBy ?? auth()->id(),
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(array $response = []): void
    {
        $this->update([
            'status' => 'FAILED',
            'provider_response' => $response,
            'processed_at' => now(),
        ]);
    }

    /**
     * Génère un numéro de transaction unique
     */
    public static function generateTransactionId(): string
    {
        return 'PAY-' . strtoupper(uniqid()) . '-' . now()->format('ymd');
    }
}
