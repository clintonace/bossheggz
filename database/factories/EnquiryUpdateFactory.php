<?php

namespace Database\Factories;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\EnquiryUpdate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnquiryUpdate>
 */
class EnquiryUpdateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enquiry_id' => Enquiry::factory(),
            'status' => EnquiryStatus::Contacted,
            'message' => fake()->sentence(),
        ];
    }
}
