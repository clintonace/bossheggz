<?php

namespace App\Enums;

enum AdvertPage: string
{
    case Home = 'home';
    case Listings = 'listings';
    case Property = 'property';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Home page',
            self::Listings => 'Listings page',
            self::Property => 'Property page',
        };
    }

    /**
     * The page's sections, top to bottom, and where adverts may go around each.
     *
     * @return array<string, array{label: string, positions: array<int, AdvertPosition>}>
     */
    public function sections(): array
    {
        $around = [AdvertPosition::Before, AdvertPosition::After];

        return match ($this) {
            self::Home => [
                'hero' => ['label' => 'Hero & search', 'positions' => $around],
                'featured' => ['label' => 'Featured properties', 'positions' => $around],
                'services' => ['label' => 'Our services', 'positions' => $around],
                'how_it_works' => ['label' => 'How it works', 'positions' => $around],
                'guarantee' => ['label' => 'Why choose us (guarantee)', 'positions' => $around],
                'investments' => ['label' => 'Investment opportunities', 'positions' => $around],
                'latest' => ['label' => 'Latest on the market', 'positions' => $around],
                'insights' => ['label' => 'Insights and resources', 'positions' => $around],
                'cta' => ['label' => 'Speak to an adviser call to action', 'positions' => $around],
                'signup' => ['label' => 'Sign-up call to action', 'positions' => $around],
            ],
            self::Listings => [
                'header' => ['label' => 'Page title & search filters', 'positions' => $around],
                'grid' => ['label' => 'Property results', 'positions' => [AdvertPosition::Before, AdvertPosition::Inside, AdvertPosition::After]],
            ],
            self::Property => [
                'overview' => ['label' => 'Title & photo gallery', 'positions' => $around],
                'about' => ['label' => 'Description, features & location', 'positions' => $around],
                'similar' => ['label' => 'Similar properties', 'positions' => $around],
            ],
        };
    }

    public function sectionLabel(?string $section): string
    {
        return $this->sections()[$section]['label'] ?? (string) $section;
    }

    /**
     * Pages a pop-up can target ("all" means every public page).
     *
     * @return array<string, string>
     */
    public static function popupTargets(): array
    {
        return ['all' => 'All public pages', ...collect(self::cases())->mapWithKeys(fn (self $page) => [$page->value => $page->label()])->all()];
    }
}
