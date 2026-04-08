<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dish>
 */
class DishFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'category_id' => \App\Models\Category::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price_base' => fake()->numberBetween(2000, 20000),
            'photo_url' => null,
            'allergens' => null,
            'tags' => null,
            'stock_quantity' => null,
            'preparation_time_minutes' => fake()->numberBetween(10, 60),
            'active' => true,
        ];
    }
}
