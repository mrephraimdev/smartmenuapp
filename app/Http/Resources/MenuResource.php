<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
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
            'title' => $this->title,
            'active' => (bool) $this->active,
            'categories' => $this->when($this->relationLoaded('categories'), function () {
                return $this->categories
                    ->sortBy('sort_order')
                    ->values()
                    ->map(fn($category) => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'sort_order' => $category->sort_order,
                        'dishes' => $this->when($category->relationLoaded('dishes'), function () use ($category) {
                            return DishResource::collection($category->dishes->where('active', true));
                        }),
                        'dishes_count' => $category->dishes_count ?? $category->dishes?->count(),
                    ]);
            }),
            'categories_count' => $this->whenCounted('categories'),
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
            'tenant_id' => $this->tenant_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'links' => [
                'self' => $this->when($this->id, fn() => route('api.menus.show', $this->id, false)),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'api' => 'SmartMenu API',
            ],
        ];
    }
}
