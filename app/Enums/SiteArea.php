<?php

namespace App\Enums;

enum SiteArea: string
{
    case Landing = 'landing';
    case Client = 'client';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Landing => 'Landing page',
            self::Client => 'Client dashboard',
            self::Admin => 'Admin dashboard',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Landing => 'The public website: home, properties, investments, land banking, services, insights, about, contact and sign-in.',
            self::Client => 'The signed-in client area: overview, engagements, notifications, browsing and profile.',
            self::Admin => 'This admin area: dashboard, listings, engagements, enquiries, investments, pages, adverts and settings.',
        };
    }

    public function hasSidebar(): bool
    {
        return $this !== self::Landing;
    }

    /**
     * Default settings for the area.
     *
     * @return array{accent: string, primary: string, background: string, heading_font: string, body_font: string, font_size: int, mode: string, sidebar: string, footer: string, animations: bool, glass: string, ambient: bool, tour: bool, contact_strip: bool, preloader: bool}
     */
    public function defaults(): array
    {
        return [
            'glass' => 'frosted',
            'ambient' => true,
            'tour' => true,
            'contact_strip' => true,
            'preloader' => true,
            'accent' => '#b88a41',
            'primary' => '#121825',
            'background' => $this === self::Admin ? '#f6f5f2' : '#faf7f2',
            'heading_font' => 'fraunces',
            'body_font' => 'instrument-sans',
            'font_size' => 16,
            'mode' => 'light',
            'sidebar' => 'auto',
            'footer' => 'dark',
            'animations' => true,
        ];
    }

    /**
     * The guided tour shown on a visitor's first view of the area (and from the profile menu).
     * Each step points at an element by CSS selector; steps whose element is missing or hidden
     * (e.g. the sidebar on a phone) are skipped. A step without a target is shown centred.
     *
     * @return list<array{target: ?string, title: string, body: string}>
     */
    public function tourSteps(): array
    {
        $company = (string) config('listings.company.name');

        return match ($this) {
            self::Landing => [
                ['target' => null, 'title' => "Welcome to {$company}", 'body' => 'Take a quick tour of the website — it takes less than a minute.'],
                ['target' => '[data-tour="main-nav"], [data-toggle="#mobile-nav"]', 'title' => 'Find your way around', 'body' => 'Browse properties for sale, rent and shortlet, investment opportunities, land banking, our services and market insights.'],
                ['target' => '[data-tour="property-search"]', 'title' => 'Search in seconds', 'body' => 'Choose buy, rent, shortlet or lease, then narrow it down by location, category and price.'],
                ['target' => '[data-tour="adviser"]', 'title' => 'Speak to an adviser', 'body' => 'Pick a listed property or describe what you need, and an adviser will get back to you — by form or on WhatsApp.'],
                ['target' => '[data-tour="list-property"]', 'title' => 'Own a property?', 'body' => 'List it with us for sale, rent, shortlet or full property management.'],
                ['target' => '[data-toggle="#profile-menu"], [data-tour="sign-in"]', 'title' => 'Your account', 'body' => 'Sign in to follow every property you engage with, get updates by email and switch between light and dark mode.'],
                ['target' => null, 'title' => 'You are all set', 'body' => 'You can replay this tour any time from the profile menu, or from “Take a tour” at the bottom of the page.'],
            ],
            self::Client => [
                ['target' => null, 'title' => 'Welcome to your dashboard', 'body' => 'Here is a quick look at where everything lives.'],
                ['target' => '[data-toggle="#client-sidebar"]', 'title' => 'Your menu', 'body' => 'Open the menu to reach your engagements, notifications and property browsing.'],
                ['target' => '[data-toggle="#nav-my-engagements"]', 'title' => 'My engagements', 'body' => 'Every property you have engaged with, and how far each one has progressed — from first contact to closing.'],
                ['target' => '[data-toggle="#nav-my-enquiries"]', 'title' => 'My enquiries', 'body' => 'Land banking, investment, property and adviser enquiries — see where each one stands and every message from your adviser.'],
                ['target' => '[data-toggle="#notifications-panel"]', 'title' => 'Notifications', 'body' => 'Progress updates, price changes and messages from our team appear here and in your email.'],
                ['target' => '[data-toggle="#nav-explore"]', 'title' => 'Keep browsing', 'body' => 'Jump back to properties, shortlets, investment opportunities and land banking, or speak to an adviser.'],
                ['target' => '[data-toggle="#profile-menu"]', 'title' => 'Your profile', 'body' => 'Update your details and password, switch between light and dark mode, and replay this tour.'],
            ],
            self::Admin => [
                ['target' => null, 'title' => 'Welcome to the admin dashboard', 'body' => "Everything you need to run the {$company} website is here. Let’s take a quick look around."],
                ['target' => '[data-toggle="#admin-sidebar"]', 'title' => 'The menu', 'body' => 'Open the menu to reach listings, engagements, enquiries, pages and settings.'],
                ['target' => '[data-toggle="#nav-properties"]', 'title' => 'Properties', 'body' => 'Add listings, publish them and jump straight to drafts, sold or rented properties.'],
                ['target' => '[data-toggle="#nav-engagements"]', 'title' => 'Engagements', 'body' => 'Move each engagement through the pipeline. Signed-in clients are notified of every step.'],
                ['target' => '[data-toggle="#nav-enquiries"]', 'title' => 'Enquiries', 'body' => 'Adviser requests from the website, plus the enquiry types and WhatsApp numbers visitors can choose.'],
                ['target' => '[data-toggle="#nav-investments"]', 'title' => 'Investments & land', 'body' => 'Publish investment and land banking opportunities with their documentation and risks.'],
                ['target' => '[data-toggle="#nav-pages"]', 'title' => 'Page content', 'body' => 'Change the wording, images and buttons of every public page, and hide sections you don’t need.'],
                ['target' => '[data-toggle="#nav-site"]', 'title' => 'Site manager', 'body' => 'Colours, fonts, the glass effect, dark mode and this tour — for the website and both dashboards.'],
                ['target' => '[data-toggle="#notifications-panel"]', 'title' => 'Notifications', 'body' => 'New engagements and enquiries arrive here and by email.'],
                ['target' => '[data-toggle="#profile-menu"]', 'title' => 'Replay any time', 'body' => 'Your profile menu has the light/dark switch — and “Take a tour” to see this again.'],
            ],
        };
    }
}
