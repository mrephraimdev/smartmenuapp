<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dish = \App\Models\Dish::factory()->create();

        return [
            'order_id' => \App\Models\Order::factory(),
            'dish_id' => $dish->id,
            'variant_id' => null,
            'options' => null,
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => $dish->price_base,
            'notes' => null,
        ];
    }
}
