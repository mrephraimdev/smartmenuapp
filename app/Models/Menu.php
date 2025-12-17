<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'title', 'active'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}