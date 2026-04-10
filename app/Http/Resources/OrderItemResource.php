<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'unit_price_formatted' => number_format($this->unit_price, 0, ',', ' ') . ' FCFA',
            'subtotal' => (float) ($this->quantity * $this->unit_price),
            'subtotal_formatted' => number_format($this->quantity * $this->unit_price, 0, ',', ' ') . ' FCFA',
            'notes' => $this->notes,
            'options' => $this->options ?? [],
            'dish' => new DishResource($this->whenLoaded('dish')),
            'dish_id' => $this->dish_id,
            'dish_name' => $this->when(!$this->relationLoaded('dish'), fn() => $this->dish?->name),
            'variant' => $this->when($this->variant_id, function () {
                return [
                    'id' => $this->variant?->id,
                    'name' => $this->variant?->name,
                    'price_modifier' => (float) ($this->variant?->price_modifier ?? 0),
                ];
            }),
            'variant_id' => $this->variant_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
