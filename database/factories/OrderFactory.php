<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
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
            'table_id' => \App\Models\Table::factory(),
            'order_number' => 'ORD-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'status' => fake()->randomElement(['RECU', 'PREP', 'PRET', 'SERVI']),
            'total' => fake()->numberBetween(5000, 50000),
            'notes' => null,
            'pos_session_id' => null,
        ];
    }
}
