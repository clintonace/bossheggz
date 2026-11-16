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
            self::Landing => 'The public website: home, listings, property pages and sign-in.',
            self::Client => 'The signed-in client area: overview, engagements, notifications and profile.',
            self::Admin => 'This admin area: dashboard, properties, engagements, adverts and settings.',
        };
    }

    public function hasSidebar(): bool
    {
        return $this !== self::Landing;
    }

    /**
     * Default settings for the area.
     *
     * @return array{accent: string, primary: string, background: string, heading_font: string, body_font: string, font_size: int, mode: string, sidebar: string, footer: string, animations: bool}
     */
    public function defaults(): array
    {
        return [
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
}
