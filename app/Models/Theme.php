<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'colors', 'fonts', 'category', 'is_default', 'is_active'
    ];

    protected $casts = [
        'colors' => 'array',
        'fonts' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relations
    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Helper methods
    public function getPrimaryColor()
    {
        return $this->colors['primary'] ?? '#C9A227';
    }

    public function getSecondaryColor()
    {
        return $this->colors['secondary'] ?? '#1A1A1A';
    }

    public function getAccentColor()
    {
        return $this->colors['accent'] ?? '#D4AF37';
    }

    public function getBackgroundColor()
    {
        return $this->colors['background'] ?? '#FFFFFF';
    }

    public function getTextColor()
    {
        return $this->colors['text'] ?? '#333333';
    }

    public function getHeadingFont()
    {
        return $this->fonts['heading'] ?? 'Inter';
    }

    public function getBodyFont()
    {
        return $this->fonts['body'] ?? 'Inter';
    }
}
