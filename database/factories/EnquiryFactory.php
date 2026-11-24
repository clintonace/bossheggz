<?php

namespace Database\Factories;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\EnquiryType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enquiry>
 */
class EnquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enquiry_type_id' => fn () => EnquiryType::where('slug', EnquiryType::General)->value('id') ?? EnquiryType::factory(),
            'status' => EnquiryStatus::New,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'message' => fake()->sentence(16),
        ];
    }

    public function ofType(string $slug): static
    {
        return $this->state(fn (array $attributes) => ['enquiry_type_id' => EnquiryType::where('slug', $slug)->value('id')]);
    }

    public function status(EnquiryStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }
}
