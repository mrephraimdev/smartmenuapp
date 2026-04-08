<?php

namespace App\Models;

use App\Traits\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dish extends Model
{
    use HasFactory, TenantScope, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'description',
        'price_base',
        'photo_url',
        'allergens',
        'tags',
        'stock_quantity',
        'preparation_time_minutes',
        'active'
    ];

    protected $casts = [
        'allergens' => 'array',
        'tags' => 'array',
        'active' => 'boolean',
        'price_base' => 'decimal:2'
    ];

    /**
     * Ajouter is_available à la sérialisation JSON
     */
    protected $appends = ['is_available'];

    /**
     * Accesseur pour is_available (alias de active + stock check)
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->isAvailable();
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Assigner automatiquement le tenant_id si manquant
        static::creating(function ($dish) {
            if (!$dish->tenant_id && $dish->category_id) {
                $category = \App\Models\Category::withoutGlobalScope('tenant')
                    ->with('menu')
                    ->find($dish->category_id);

                if ($category && $category->menu?->tenant_id) {
                    $dish->tenant_id = $category->menu->tenant_id;
                }
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    // Vérifier si le plat est disponible
    public function isAvailable()
    {
        if (!$this->active) {
            return false;
        }

        // Si stock_quantity est null, le plat est disponible en illimité
        if ($this->stock_quantity === null) {
            return true;
        }

        return $this->stock_quantity > 0;
    }

    // Obtenir les allergènes formatés
    public function getAllergensFormatted()
    {
        if (empty($this->allergens)) {
            return 'Aucun';
        }

        return implode(', ', $this->allergens);
    }

    // Obtenir les tags formatés
    public function getTagsFormatted()
    {
        if (empty($this->tags)) {
            return [];
        }

        return $this->tags;
    }
}