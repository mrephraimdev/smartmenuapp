<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['menu_id', 'name', 'sort_order'];

    /**
     * Boot du modèle - Applique le scope tenant via relation Menu
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && !Auth::user()->hasRole('SUPER_ADMIN') && Auth::user()->tenant_id) {
                $builder->whereHas('menu', function (Builder $query) {
                    $query->where('tenant_id', Auth::user()->tenant_id);
                });
            }
        });
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function dishes()
    {
        return $this->hasMany(Dish::class);
    }

    /**
     * Query scope pour exclure le filtre tenant
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}