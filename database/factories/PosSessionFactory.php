<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PosSession>
 */
class PosSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $opened = now()->subHours(fake()->numberBetween(1, 8));

        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'user_id' => \App\Models\User::factory(),
            'session_number' => 'POS-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'status' => 'OPEN',
            'opened_at' => $opened,
            'closed_at' => null,
            'opening_float' => fake()->numberBetween(20000, 100000),
            'actual_cash' => 0,
            'expected_cash' => 0,
            'cash_difference' => 0,
            'total_sales' => 0,
            'total_orders' => 0,
            'total_items' => 0,
            'cash_sales' => 0,
            'card_sales' => 0,
            'mobile_sales' => 0,
            'cancelled_orders' => 0,
            'refunds_total' => 0,
            'opening_notes' => null,
            'closing_notes' => null,
        ];
    }
}
