<?php

namespace Database\Factories;

use App\Enums\ArticleCategory;
use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->sentence(6), '.'),
            'category' => fake()->randomElement(ArticleCategory::cases()),
            'author_name' => fake()->name(),
            'excerpt' => fake()->sentence(18),
            'body' => fake()->paragraphs(5, true),
            'is_featured' => false,
            'published_at' => now()->subDays(fake()->numberBetween(1, 60)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['published_at' => null]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => ['published_at' => now()->addWeek()]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['is_featured' => true]);
    }
}
