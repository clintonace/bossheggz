<?php

namespace Database\Seeders;

use App\Enums\AdvertFormat;
use App\Enums\AdvertPage;
use App\Enums\AdvertPosition;
use App\Enums\AdvertWidth;
use App\Models\Advert;
use Illuminate\Database\Seeder;

class AdvertSeeder extends Seeder
{
    /**
     * Demo adverts for products unrelated to property (fictional brands). Safe to run more than once.
     */
    public function run(): void
    {
        Advert::where('title', 'Furnish your new home for less')->get()->each->delete();

        $photo = fn (string $id): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1200&q=80";

        $inline = fn (AdvertPage $page, string $section, AdvertPosition $position, AdvertWidth $width, int $order = 0) => [
            'format' => AdvertFormat::Inline,
            'page' => $page->value,
            'section' => $section,
            'position' => $position,
            'width' => $width,
            'sort_order' => $order,
        ];

        $demos = [
            [
                'title' => 'Beach weekends from ₦85,000',
                'sponsor' => 'Coastline Getaways (demo)',
                'body' => 'Two nights by the sea with breakfast and airport pickup. Book before Friday and save 20%.',
                'cta_label' => 'See weekend deals',
                'link_url' => 'https://example.com/coastline-getaways',
                'image' => $photo('1507525428034-b723cf961d3e'),
                ...$inline(AdvertPage::Home, 'latest', AdvertPosition::After, AdvertWidth::Full),
            ],
            [
                'title' => 'Your first month at the gym is free',
                'sponsor' => 'Pulse Fitness (demo)',
                'body' => 'Modern equipment, group classes and personal trainers. Join this week — no sign-up fee.',
                'cta_label' => 'Claim free month',
                'link_url' => 'https://example.com/pulse-fitness',
                'image' => $photo('1517836357463-d25dfeac3438'),
                ...$inline(AdvertPage::Listings, 'grid', AdvertPosition::Inside, AdvertWidth::Half),
            ],
            [
                'title' => 'Two coffees, one price',
                'sponsor' => 'Bean & Brew Café (demo)',
                'body' => 'Bring a friend every weekday before 10am.',
                'cta_label' => 'Find a café',
                'link_url' => 'https://example.com/bean-and-brew',
                'image' => $photo('1495474472287-4d71bcdd2085'),
                ...$inline(AdvertPage::Listings, 'grid', AdvertPosition::After, AdvertWidth::Half, 1),
            ],
            [
                'title' => 'Fly business class for less',
                'sponsor' => 'Skyward Travel (demo)',
                'body' => 'Lie-flat seats and lounge access on selected routes this season.',
                'cta_label' => 'See fares',
                'link_url' => 'https://example.com/skyward-travel',
                'image' => $photo('1436491865332-7a61a109cc05'),
                ...$inline(AdvertPage::Investments, 'how_it_works', AdvertPosition::After, AdvertWidth::Full),
            ],
            [
                'title' => 'Lakeside boat tours',
                'sponsor' => 'Bluewater Tours (demo)',
                'body' => 'Sunrise and sunset cruises every day. Kids ride free.',
                'cta_label' => 'Book a tour',
                'link_url' => 'https://example.com/bluewater-tours',
                'image' => $photo('1476514525535-07fb3b4ae5f1'),
                ...$inline(AdvertPage::Listings, 'grid', AdvertPosition::After, AdvertWidth::Half, 2),
            ],
            [
                'title' => 'Meet the Nova X — launching this week',
                'sponsor' => 'Nova Mobile (demo)',
                'body' => 'All-day battery, a brilliant camera and free delivery on launch orders.',
                'cta_label' => 'Pre-order now',
                'link_url' => 'https://example.com/nova-x',
                'image' => $photo('1511707171634-5f897ff02aa9'),
                'format' => AdvertFormat::Popup,
                'page' => AdvertPage::Home->value,
                'section' => null,
                'position' => null,
                'display_seconds' => 8,
            ],
        ];

        foreach ($demos as $demo) {
            $advert = Advert::firstOrNew(['title' => $demo['title']]);
            $advert->fill([...collect($demo)->except('image')->all(), 'is_active' => true]);
            $advert->image_path = $demo['image'];
            $advert->save();
        }
    }
}
