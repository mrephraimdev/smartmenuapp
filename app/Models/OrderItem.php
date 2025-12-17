<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'dish_id', 'variant_id', 'options', 'quantity', 'unit_price', 'notes'
    ];

    protected $casts = [
        'options' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
