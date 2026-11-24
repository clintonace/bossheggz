<?php

namespace Database\Factories;

use App\Enums\LandingPage;
use App\Models\PageSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageSection>
 */
class PageSectionFactory extends Factory
{
    /**
     * Define the model's default state: a customised home page hero.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page' => LandingPage::Home,
            'section' => 'hero',
            'content' => ['title' => fake()->sentence(3)],
            'is_visible' => true,
        ];
    }

    /**
     * A specific section of a page.
     */
    public function section(LandingPage $page, string $section): static
    {
        return $this->state(fn () => ['page' => $page, 'section' => $section]);
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['is_visible' => false]);
    }
}
