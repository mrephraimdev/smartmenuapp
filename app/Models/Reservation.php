<?php

namespace App\Models;

use App\Traits\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory, TenantScope;

    protected $fillable = [
        'tenant_id',
        'table_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'reservation_date',
        'reservation_time',
        'party_size',
        'duration_minutes',
        'status',
        'special_requests',
        'notes',
        'confirmation_code',
        'confirmed_at',
        'seated_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'reservation_time' => 'datetime:H:i',
        'confirmed_at' => 'datetime',
        'seated_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            if (empty($reservation->confirmation_code)) {
                $reservation->confirmation_code = self::generateConfirmationCode();
            }
        });
    }

    /**
     * Generate unique confirmation code
     */
    public static function generateConfirmationCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('confirmation_code', $code)->exists());

        return $code;
    }

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Status helpers
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'CONFIRMED';
    }

    public function isSeated(): bool
    {
        return $this->status === 'SEATED';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'CANCELLED';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    public function isNoShow(): bool
    {
        return $this->status === 'NO_SHOW';
    }

    /**
     * Actions
     */
    public function confirm(): self
    {
        $this->update([
            'status' => 'CONFIRMED',
            'confirmed_at' => now(),
        ]);

        return $this;
    }

    public function seat(): self
    {
        $this->update([
            'status' => 'SEATED',
            'seated_at' => now(),
        ]);

        return $this;
    }

    public function complete(): self
    {
        $this->update(['status' => 'COMPLETED']);

        return $this;
    }

    public function cancel(string $reason = ''): self
    {
        $this->update([
            'status' => 'CANCELLED',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $this;
    }

    public function markNoShow(): self
    {
        $this->update(['status' => 'NO_SHOW']);

        return $this;
    }

    /**
     * Get end time of reservation
     */
    public function getEndTimeAttribute(): string
    {
        return $this->reservation_time
            ? $this->reservation_time->addMinutes($this->duration_minutes)->format('H:i')
            : '';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'PENDING' => 'En attente',
            'CONFIRMED' => 'Confirmée',
            'SEATED' => 'Installé',
            'COMPLETED' => 'Terminée',
            'CANCELLED' => 'Annulée',
            'NO_SHOW' => 'Absent',
            default => $this->status
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'PENDING' => 'amber',
            'CONFIRMED' => 'blue',
            'SEATED' => 'green',
            'COMPLETED' => 'gray',
            'CANCELLED' => 'red',
            'NO_SHOW' => 'red',
            default => 'gray'
        };
    }

    /**
     * Scopes
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('reservation_date', $date);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('reservation_date', now()->toDateString());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('reservation_date', '>=', now()->toDateString())
            ->whereIn('status', ['PENDING', 'CONFIRMED']);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['PENDING', 'CONFIRMED', 'SEATED']);
    }
}
