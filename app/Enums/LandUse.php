<?php

namespace App\Enums;

enum LandUse: string
{
    case Residential = 'residential';
    case Commercial = 'commercial';
    case Agricultural = 'agricultural';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Residential => 'Residential Land',
            self::Commercial => 'Commercial Land',
            self::Agricultural => 'Agricultural Land',
            self::Mixed => 'Mixed-use Land',
        };
    }
}
