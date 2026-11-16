<?php

namespace Database\Factories;

use App\Enums\AdvertFormat;
use App\Enums\AdvertPage;
use App\Enums\AdvertPosition;
use App\Enums\AdvertWidth;
use App\Models\Advert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Advert>
 */
class AdvertFactory extends Factory
{
    /**
     * Define the model's default state: a full-width banner after "Latest" on the home page.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->catchPhrase(),
            'sponsor' => fake()->company(),
            'body' => fake()->sentence(14),
            'cta_label' => 'Learn more',
            'link_url' => 'https://example.com/'.fake()->slug(2),
            'format' => AdvertFormat::Inline,
            'page' => AdvertPage::Home->value,
            'section' => 'latest',
            'position' => AdvertPosition::After,
            'width' => AdvertWidth::Full,
            'display_seconds' => 8,
            'is_active' => true,
        ];
    }

    public function at(AdvertPage $page, string $section, AdvertPosition $position, AdvertWidth $width = AdvertWidth::Full): static
    {
        return $this->state(fn (array $attributes) => [
            'page' => $page->value,
            'section' => $section,
            'position' => $position,
            'width' => $width,
        ]);
    }

    public function inListingsGrid(): static
    {
        return $this->at(AdvertPage::Listings, 'grid', AdvertPosition::Inside, AdvertWidth::Half);
    }

    public function popup(string $page = 'all', int $seconds = 8): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => AdvertFormat::Popup,
            'page' => $page,
            'section' => null,
            'position' => null,
            'display_seconds' => $seconds,
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => ['starts_at' => now()->addWeek()]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => ['ends_at' => now()->subDay()]);
    }
}
