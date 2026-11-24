<?php

namespace Database\Factories;

use App\Enums\InvestmentCategory;
use App\Enums\LandUse;
use App\Enums\OpportunityStatus;
use App\Enums\VerificationStatus;
use App\Models\InvestmentOpportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvestmentOpportunity>
 */
class InvestmentOpportunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['Riverside', 'Greenfield', 'Harbour', 'Crescent', 'Summit']).' '.fake()->randomElement(['Rental Portfolio', 'Joint Venture', 'Residences', 'Acquisition']),
            'category' => InvestmentCategory::PropertyInvestment,
            'status' => OpportunityStatus::Open,
            'location' => fake()->city(),
            'state' => fake()->state(),
            'summary' => fake()->sentence(14),
            'description' => fake()->paragraphs(3, true),
            'required_investment' => fake()->numberBetween(20, 500) * 1_000_000,
            'minimum_participation' => fake()->numberBetween(1, 10) * 1_000_000,
            'timeline' => fake()->numberBetween(12, 60).' months',
            'structure' => fake()->sentence(),
            'risks' => fake()->sentence(),
            'documentation' => 'Survey and deed available',
            'verification_status' => VerificationStatus::DocumentsAvailable,
            'is_featured' => false,
        ];
    }

    public function land(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => InvestmentCategory::LandBanking,
            'land_use' => LandUse::Residential,
            'land_sizes' => '500 m², 1,000 m²',
            'land_size_sqm' => 500,
            'acquisition_price' => fake()->numberBetween(3, 60) * 1_000_000,
            'access' => '5 minutes from the expressway',
            'estate_status' => 'Estate road and perimeter fencing in progress',
            'documentation' => 'Certificate of Occupancy',
            'rationale' => fake()->sentence(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => OpportunityStatus::Draft]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => OpportunityStatus::Closed]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['is_featured' => true]);
    }
}
