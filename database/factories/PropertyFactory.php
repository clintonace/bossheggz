<?php

namespace Database\Factories;

use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RentPeriod;
use App\Enums\VerificationStatus;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['Elegant', 'Modern', 'Serene', 'Spacious', 'Classic']).' '.fake()->numberBetween(2, 6).' Bedroom '.fake()->randomElement(['Home', 'Residence', 'Villa', 'Apartment']),
            'listing_type' => ListingType::Sale,
            'property_type' => fake()->randomElement(PropertyType::cases()),
            'status' => PropertyStatus::Published,
            'price' => fake()->numberBetween(50, 900) * 1_000_000,
            'rent_period' => null,
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'bedrooms' => fake()->numberBetween(1, 6),
            'bathrooms' => fake()->numberBetween(1, 6),
            'area_sqm' => fake()->numberBetween(80, 900),
            'description' => fake()->paragraphs(3, true),
            'features' => fake()->randomElements(['Swimming pool', 'Gym', '24/7 security', 'Parking', 'Garden', 'Smart home', 'Boys quarters', 'Solar power'], 4),
            'is_featured' => false,
        ];
    }

    public function forRent(RentPeriod $period = RentPeriod::Year): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => ListingType::Rent,
            'rent_period' => $period,
            'price' => fake()->numberBetween(1, 30) * 500_000,
        ]);
    }

    public function shortlet(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => ListingType::Shortlet,
            'rent_period' => RentPeriod::Night,
            'price' => fake()->numberBetween(5, 40) * 10_000,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => VerificationStatus::Verified,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PropertyStatus::Draft,
        ]);
    }

    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PropertyStatus::Sold,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
