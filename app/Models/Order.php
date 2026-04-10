<?php

namespace App\Models;

use App\Traits\TenantScope;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, TenantScope, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'table_id', 'pos_session_id', 'order_number', 'status', 'total', 'notes',
        'payment_status', 'paid_amount', 'paid_at'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get status as enum
     */
    public function getStatusEnum(): OrderStatus
    {
        return OrderStatus::from($this->status);
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return $this->getStatusEnum()->label();
    }

    /**
     * Get status color
     */
    public function getStatusColor(): string
    {
        return $this->getStatusEnum()->color();
    }

    /**
     * Check if order is active (not served or cancelled)
     */
    public function isActive(): bool
    {
        return in_array($this->status, OrderStatus::activeValues());
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function posSession()
    {
        return $this->belongsTo(PosSession::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ============================================
    // PAYMENT METHODS
    // ============================================

    /**
     * Get payment status as enum
     */
    public function getPaymentStatusEnum(): PaymentStatus
    {
        return PaymentStatus::tryFrom($this->payment_status) ?? PaymentStatus::PENDING;
    }

    /**
     * Get payment status label
     */
    public function getPaymentStatusLabel(): string
    {
        return $this->getPaymentStatusEnum()->label();
    }

    /**
     * Get payment status badge class
     */
    public function getPaymentStatusBadgeClass(): string
    {
        return $this->getPaymentStatusEnum()->badgeClass();
    }

    /**
     * Check if order is fully paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::PAID->value;
    }

    /**
     * Check if order has pending payment
     */
    public function hasPendingPayment(): bool
    {
        return in_array($this->payment_status, [
            PaymentStatus::PENDING->value,
            PaymentStatus::PARTIAL->value
        ]);
    }

    /**
     * Get remaining amount to pay
     */
    public function getRemainingAmount(): float
    {
        return max(0, $this->total - $this->paid_amount);
    }

    /**
     * Get formatted remaining amount
     */
    public function getFormattedRemainingAmount(): string
    {
        return number_format($this->getRemainingAmount(), 0, ',', ' ') . ' FCFA';
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotal(): string
    {
        return number_format($this->total, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Get formatted paid amount
     */
    public function getFormattedPaidAmount(): string
    {
        return number_format($this->paid_amount, 0, ',', ' ') . ' FCFA';
    }

    // Générer un numéro de commande unique
    public static function generateOrderNumber($tenantId)
    {
        $date = now()->format('Ymd');
        $count = self::where('tenant_id', $tenantId)
                     ->whereDate('created_at', now())
                     ->count() + 1;

        return sprintf('%s-T%d-%04d', $date, $tenantId, $count);
    }

    // Créer une commande avec numéro automatique
    public static function createWithNumber(array $data)
    {
        if (!isset($data['order_number'])) {
            $data['order_number'] = self::generateOrderNumber($data['tenant_id']);
        }

        return self::create($data);
    }
}