<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'logo_url', 'branding', 'type', 'currency', 'locale', 'is_active', 'theme_id'
    ];

    protected $casts = [
        'branding' => 'array'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    // Helper methods for branding
    public function getTheme()
    {
        return $this->theme ?? Theme::default()->first();
    }

    public function getPrimaryColor()
    {
        return $this->getTheme()->getPrimaryColor();
    }

    public function getSecondaryColor()
    {
        return $this->getTheme()->getSecondaryColor();
    }

    public function getAccentColor()
    {
        return $this->getTheme()->getAccentColor();
    }

    public function getBackgroundColor()
    {
        return $this->getTheme()->getBackgroundColor();
    }

    public function getTextColor()
    {
        return $this->getTheme()->getTextColor();
    }

    public function getHeadingFont()
    {
        return $this->getTheme()->getHeadingFont();
    }

    public function getBodyFont()
    {
        return $this->getTheme()->getBodyFont();
    }
}
