<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Table>
 */
class TableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 100);

        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'code' => 'T' . str_pad($number, 3, '0', STR_PAD_LEFT),
            'label' => 'Table ' . $number,
            'capacity' => fake()->numberBetween(2, 8),
            'qr_code_url' => null,
            'is_active' => true,
        ];
    }
}
