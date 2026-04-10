<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Option extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['dish_id', 'name', 'kind', 'extra_price'];

    /**
     * Boot du modèle - Applique le scope tenant via relation Dish
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && !Auth::user()->hasRole('SUPER_ADMIN') && Auth::user()->tenant_id) {
                $builder->whereHas('dish', function (Builder $query) {
                    $query->where('tenant_id', Auth::user()->tenant_id);
                });
            }
        });
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }

    /**
     * Query scope pour exclure le filtre tenant
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}