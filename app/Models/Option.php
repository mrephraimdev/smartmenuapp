<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $fillable = ['dish_id', 'name', 'kind', 'extra_price'];

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }
}