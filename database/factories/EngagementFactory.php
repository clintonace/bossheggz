<?php

namespace Database\Factories;

use App\Enums\EngagementStatus;
use App\Models\Engagement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Engagement>
 */
class EngagementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'message' => fake()->sentence(12),
            'status' => EngagementStatus::New,
        ];
    }

    /**
     * Engagement made by a signed-in user.
     */
    public function forUser(?User $user = null): static
    {
        return $this->state(function (array $attributes) use ($user) {
            $user ??= User::factory()->create();

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });
    }

    public function status(EngagementStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
