<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DishResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'price_base' => (float) $this->price_base,
            'price_formatted' => number_format($this->price_base, 0, ',', ' ') . ' FCFA',
            'photo_url' => $this->photo_url,
            'photo_thumbnail' => $this->photo_url ? $this->getPhotoThumbnail() : null,
            'allergens' => $this->allergens ?? [],
            'allergens_formatted' => $this->getAllergensFormatted(),
            'tags' => $this->tags ?? [],
            'stock_quantity' => $this->stock_quantity,
            'is_unlimited_stock' => $this->stock_quantity === null,
            'is_available' => $this->isAvailable(),
            'preparation_time_minutes' => $this->preparation_time_minutes,
            'preparation_time_formatted' => $this->getPreparationTimeFormatted(),
            'active' => (bool) $this->active,
            'category' => $this->when($this->relationLoaded('category'), function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'category_id' => $this->category_id,
            'variants' => $this->when($this->relationLoaded('variants'), function () {
                return $this->variants->map(fn($variant) => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'price_modifier' => (float) $variant->price_modifier,
                    'final_price' => (float) ($this->price_base + $variant->price_modifier),
                    'final_price_formatted' => number_format($this->price_base + $variant->price_modifier, 0, ',', ' ') . ' FCFA',
                ]);
            }),
            'options' => $this->when($this->relationLoaded('options'), function () {
                return $this->options->map(fn($option) => [
                    'id' => $option->id,
                    'name' => $option->name,
                    'price' => (float) $option->price,
                    'price_formatted' => $option->price > 0
                        ? '+' . number_format($option->price, 0, ',', ' ') . ' FCFA'
                        : 'Gratuit',
                ]);
            }),
            'variants_count' => $this->whenCounted('variants'),
            'options_count' => $this->whenCounted('options'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get preparation time formatted.
     */
    protected function getPreparationTimeFormatted(): ?string
    {
        if (!$this->preparation_time_minutes) {
            return null;
        }

        if ($this->preparation_time_minutes < 60) {
            return $this->preparation_time_minutes . ' min';
        }

        $hours = floor($this->preparation_time_minutes / 60);
        $minutes = $this->preparation_time_minutes % 60;

        if ($minutes === 0) {
            return $hours . 'h';
        }

        return $hours . 'h' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get photo thumbnail URL.
     */
    protected function getPhotoThumbnail(): ?string
    {
        // If using external service or storage, adjust accordingly
        return $this->photo_url;
    }
}
