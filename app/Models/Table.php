<?php

namespace App\Models;

use App\Traits\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasFactory, SoftDeletes, TenantScope;

    protected $fillable = ['tenant_id', 'code', 'label', 'capacity', 'qr_code_url', 'is_active'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sessions()
    {
        return $this->hasMany(TableSession::class);
    }

    public function activeSession()
    {
        return $this->hasOne(TableSession::class)->where('status', 'ACTIVE')->latest();
    }

    public function hasActiveSession(): bool
    {
        return $this->sessions()->where('status', 'ACTIVE')->exists();
    }
}
