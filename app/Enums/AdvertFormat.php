<?php

namespace App\Enums;

enum AdvertFormat: string
{
    case Inline = 'inline';
    case Popup = 'popup';

    public function label(): string
    {
        return match ($this) {
            self::Inline => 'In-page advert',
            self::Popup => 'Pop-up (major) advert',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Inline => 'Sits before or after a section of a page, at half or full width.',
            self::Popup => 'Covers the screen for a few seconds, once per visit. Clicking it opens the advertiser; only ✕ closes it.',
        };
    }
}
