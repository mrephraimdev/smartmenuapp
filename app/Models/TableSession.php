<?php

namespace App\Models;

use App\Enums\TableSessionStatus;
use App\Traits\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableSession extends Model
{
    use HasFactory, TenantScope;

    protected $fillable = [
        'tenant_id', 'table_id', 'opened_by', 'status',
        'opened_at', 'closed_at', 'last_activity_at',
    ];

    protected $casts = [
        'opened_at'        => 'datetime',
        'closed_at'        => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_session_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', TableSessionStatus::ACTIVE->value);
    }

    public function isActive(): bool
    {
        return $this->status === TableSessionStatus::ACTIVE->value;
    }

    public function isExpiredByInactivity(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        $lastActivity = $this->last_activity_at ?? $this->opened_at;

        return $lastActivity->lt(now()->subMinutes(15));
    }

    public function expire(): void
    {
        $this->update([
            'status'    => TableSessionStatus::EXPIRED->value,
            'closed_at' => now(),
        ]);
    }

    public function close(): void
    {
        $this->update([
            'status'    => TableSessionStatus::CLOSED->value,
            'closed_at' => now(),
        ]);
    }

    public function refreshActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    public function getRemainingSeconds(): int
    {
        $lastActivity = $this->last_activity_at ?? $this->opened_at;
        $expiresAt    = $lastActivity->addMinutes(15);

        return max(0, (int) now()->diffInSeconds($expiresAt, false));
    }
}
