<?php

namespace App\Enums;

use App\Models\PageSection;
use Illuminate\Support\Collection;

/**
 * The public pages whose wording, images and sections are managed from the admin "Pages" CMS.
 * Each page's sections and fields (with their default content) live in config/page_content.php.
 */
enum LandingPage: string
{
    case Home = 'home';
    case Properties = 'properties';
    case Investments = 'investments';
    case LandBanking = 'land-banking';
    case Services = 'services';
    case Insights = 'insights';
    case About = 'about';
    case Contact = 'contact';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Home',
            self::Properties => 'Properties',
            self::Investments => 'Investment Opportunities',
            self::LandBanking => 'Land Banking',
            self::Services => 'Services',
            self::Insights => 'Insights',
            self::About => 'About Us',
            self::Contact => 'Contact Us',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Home => 'The front page: hero, featured listings, services, guarantee, investments and calls to action.',
            self::Properties => 'The listings page heading and search-engine text.',
            self::Investments => 'Hero, how it works, the information checklist and the enquiry form.',
            self::LandBanking => 'Hero, why land banking, the land checklist and the enquiry form.',
            self::Services => 'Hero, the services grid heading, property management and the shortlet panel.',
            self::Insights => 'Hero and the closing call to action of the articles page.',
            self::About => 'Company overview, mission and vision, core values and growth strategy.',
            self::Contact => 'Hero, office details and the Speak to an Adviser form wording.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Home => 'home',
            self::Properties => 'building',
            self::Investments => 'briefcase',
            self::LandBanking => 'leaf',
            self::Services => 'sparkles',
            self::Insights => 'book',
            self::About => 'users',
            self::Contact => 'phone',
        };
    }

    public function url(): string
    {
        return match ($this) {
            self::Home => route('home'),
            self::Properties => route('properties.index'),
            self::Investments => route('investments.index'),
            self::LandBanking => route('land-banking.index'),
            self::Services => route('services'),
            self::Insights => route('insights.index'),
            self::About => route('about'),
            self::Contact => route('contact'),
        };
    }

    /**
     * The page's sections, top to bottom, with their fields and default content.
     *
     * @return array<string, array{label: string, description?: string, hideable?: bool, fields: array<string, array<string, mixed>>}>
     */
    public function sections(): array
    {
        return config("page_content.pages.{$this->value}", []);
    }

    public function hasSection(string $section): bool
    {
        return array_key_exists($section, $this->sections());
    }

    /**
     * The page's content: saved changes merged over the defaults, keyed by section.
     *
     * @return Collection<string, PageSection>
     */
    public function content(): Collection
    {
        return PageSection::forPage($this);
    }
}
