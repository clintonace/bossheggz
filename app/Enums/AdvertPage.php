<?php

namespace App\Enums;

enum AdvertPage: string
{
    case Home = 'home';
    case Listings = 'listings';
    case Property = 'property';
    case Investments = 'investments';
    case LandBanking = 'land_banking';
    case Services = 'services';
    case Insights = 'insights';
    case About = 'about';
    case Contact = 'contact';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Home page',
            self::Listings => 'Listings page',
            self::Property => 'Property page',
            self::Investments => 'Investments page',
            self::LandBanking => 'Land banking page',
            self::Services => 'Services page',
            self::Insights => 'Insights page',
            self::About => 'About page',
            self::Contact => 'Contact page',
        };
    }

    /**
     * A public URL showing the page (the listings page stands in for individual property pages).
     */
    public function url(): string
    {
        return match ($this) {
            self::Home => route('home'),
            self::Listings, self::Property => route('properties.index'),
            self::Investments => route('investments.index'),
            self::LandBanking => route('land-banking.index'),
            self::Services => route('services'),
            self::Insights => route('insights.index'),
            self::About => route('about'),
            self::Contact => route('contact'),
        };
    }

    /**
     * The advert page for the current public route, if adverts can appear on it.
     */
    public static function forCurrentRoute(): ?self
    {
        return match (true) {
            request()->routeIs('home') => self::Home,
            request()->routeIs('properties.index') => self::Listings,
            request()->routeIs('properties.show') => self::Property,
            request()->routeIs('investments.index') => self::Investments,
            request()->routeIs('land-banking.index') => self::LandBanking,
            request()->routeIs('services') => self::Services,
            request()->routeIs('insights.index') => self::Insights,
            request()->routeIs('about') => self::About,
            request()->routeIs('contact') => self::Contact,
            default => null,
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
            self::Investments => [
                'hero' => ['label' => 'Hero banner', 'positions' => $around],
                'opportunities' => ['label' => 'Categories & opportunities', 'positions' => $around],
                'how_it_works' => ['label' => 'How it works', 'positions' => $around],
                'checklist' => ['label' => 'Checklist & enquiry form', 'positions' => $around],
                'cta' => ['label' => 'Call to action', 'positions' => $around],
            ],
            self::LandBanking => [
                'hero' => ['label' => 'Hero banner', 'positions' => $around],
                'why' => ['label' => 'Why land banking', 'positions' => $around],
                'opportunities' => ['label' => 'Filters & land opportunities', 'positions' => $around],
                'checklist' => ['label' => 'Checklist & enquiry form', 'positions' => $around],
                'cta' => ['label' => 'Call to action', 'positions' => $around],
            ],
            self::Services => [
                'hero' => ['label' => 'Hero banner', 'positions' => $around],
                'list' => ['label' => 'Services grid', 'positions' => $around],
                'management' => ['label' => 'Property management & shortlet', 'positions' => $around],
                'cta' => ['label' => 'Call to action', 'positions' => $around],
            ],
            self::Insights => [
                'hero' => ['label' => 'Hero banner', 'positions' => $around],
                'articles' => ['label' => 'Categories & articles', 'positions' => $around],
                'cta' => ['label' => 'Call to action', 'positions' => $around],
            ],
            self::About => [
                'hero' => ['label' => 'Hero banner', 'positions' => $around],
                'overview' => ['label' => 'Company overview', 'positions' => $around],
                'mission' => ['label' => 'Mission & vision', 'positions' => $around],
                'values' => ['label' => 'Core values', 'positions' => $around],
                'growth' => ['label' => 'Growth strategy', 'positions' => $around],
                'cta' => ['label' => 'Call to action', 'positions' => $around],
            ],
            self::Contact => [
                'hero' => ['label' => 'Hero banner', 'positions' => $around],
                'contact' => ['label' => 'Contact details & enquiry form', 'positions' => $around],
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
