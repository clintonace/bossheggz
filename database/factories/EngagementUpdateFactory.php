<?php

namespace Database\Factories;

use App\Enums\EngagementStatus;
use App\Models\Engagement;
use App\Models\EngagementUpdate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EngagementUpdate>
 */
class EngagementUpdateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'engagement_id' => Engagement::factory(),
            'author_id' => null,
            'status' => EngagementStatus::Contacted,
            'note' => fake()->sentence(),
        ];
    }
}
