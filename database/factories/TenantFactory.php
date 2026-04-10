<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'logo_url' => null,
            'cover_url' => null,
            'branding' => null,
            'type' => 'restaurant',
            'currency' => 'FCFA',
            'locale' => 'fr',
            'is_active' => true,
            'theme_id' => null,
        ];
    }
}
