<?php

namespace Database\Seeders;

use App\Enums\ArticleCategory;
use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Seed demo Insights articles (idempotent).
     */
    public function run(): void
    {
        $photo = fn (string $id): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1400&q=75";

        $articles = [
            ['Land banking in Nigeria: a beginner’s guide', ArticleCategory::LandBanking, '1500382017468-9049fed747ef', true, 3],
            ['Seven documents to check before you buy land', ArticleCategory::DueDiligence, '1450101499163-c8848c66ca85', false, 8],
            ['Buying your first home: what to budget for', ArticleCategory::FirstTimeBuyers, '1560518883-ce09059eeffa', false, 14],
            ['Lagos property market update: what moved this quarter', ArticleCategory::MarketUpdates, '1486406146926-c627a92ad1ab', false, 21],
            ['Rental income vs. capital growth: choosing your strategy', ArticleCategory::WealthCreation, '1554224155-6726b3ff858f', false, 30],
            ['How shortlet management works for property owners', ArticleCategory::PropertyManagement, '1522708323590-d24dbb6b0267', false, 40],
        ];

        foreach ($articles as [$title, $category, $image, $featured, $daysAgo]) {
            Article::updateOrCreate(['title' => $title], [
                'category' => $category,
                'author_name' => 'Advisory Team',
                'excerpt' => "A practical look at {$category->label()} for individuals and first-time investors.",
                'body' => implode("\n\n", [
                    'Making a property decision is easier when you know what to look for. This guide walks through the key questions our advisers ask on every transaction.',
                    '## Start with the documents',
                    'Ask for the title document and confirm it at the relevant land registry. A Certificate of Occupancy, Governor’s Consent or registered Deed of Assignment are common examples.',
                    '## Visit the site',
                    'A physical inspection confirms the land or building exists as described, shows you the access roads and surroundings, and reveals any encroachment.',
                    '## Understand the numbers',
                    'Treat any projected growth or rental yield as an estimate, not a promise. Ask how it was calculated and what could change it.',
                    'If you would like help with any of these steps, speak to one of our advisers.',
                ]),
                'image_path' => $photo($image),
                'is_featured' => $featured,
                'published_at' => now()->subDays($daysAgo),
            ]);
        }
    }
}
