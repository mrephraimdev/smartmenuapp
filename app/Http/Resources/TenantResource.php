<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
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
            'slug' => $this->slug,
            'type' => $this->type,
            'logo_url' => $this->logo_url,
            'cover_url' => $this->cover_url,
            'currency' => $this->currency,
            'locale' => $this->locale,
            'is_active' => (bool) $this->is_active,
            'branding' => $this->branding ?? [],
            'theme' => $this->when($this->relationLoaded('theme') || $this->theme_id, function () {
                $theme = $this->relationLoaded('theme') ? $this->theme : $this->getTheme();
                return $theme ? [
                    'id' => $theme->id,
                    'name' => $theme->name,
                    'colors' => [
                        'primary' => $theme->getPrimaryColor(),
                        'secondary' => $theme->getSecondaryColor(),
                        'accent' => $theme->getAccentColor(),
                        'background' => $theme->getBackgroundColor(),
                        'text' => $theme->getTextColor(),
                    ],
                    'fonts' => [
                        'heading' => $theme->getHeadingFont(),
                        'body' => $theme->getBodyFont(),
                    ],
                ] : null;
            }),
            'theme_id' => $this->theme_id,
            'computed_styles' => [
                'primary_color' => $this->getPrimaryColor(),
                'secondary_color' => $this->getSecondaryColor(),
                'accent_color' => $this->getAccentColor(),
                'background_color' => $this->getBackgroundColor(),
                'text_color' => $this->getTextColor(),
                'heading_font' => $this->getHeadingFont(),
                'body_font' => $this->getBodyFont(),
            ],
            'menus_count' => $this->whenCounted('menus'),
            'tables_count' => $this->whenCounted('tables'),
            'orders_count' => $this->whenCounted('orders'),
            'users_count' => $this->whenCounted('users'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'links' => [
                'self' => route('api.tenants.show', $this->slug, false),
                'menu' => route('menu.client', [$this->id, 'T01'], false),
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
