<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = ['dish_id', 'name', 'extra_price'];

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }
}