<?php

namespace Database\Factories;

use App\Models\EnquiryType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnquiryType>
 */
class EnquiryTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Mortgage advice', 'Valuation request', 'Partnership proposal', 'Site inspection', 'Diaspora buyer']),
            'is_active' => true,
            'sort_order' => 50,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
