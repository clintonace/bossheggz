<?php

namespace App\Enums;

enum PropertyType: string
{
    case House = 'house';
    case Apartment = 'apartment';
    case Duplex = 'duplex';
    case Townhouse = 'townhouse';
    case Land = 'land';
    case ResidentialLand = 'residential_land';
    case CommercialLand = 'commercial_land';
    case Commercial = 'commercial';
    case Office = 'office';

    public function label(): string
    {
        return match ($this) {
            self::ResidentialLand => 'Residential Land',
            self::CommercialLand => 'Commercial Land',
            self::Commercial => 'Commercial Property',
            default => ucfirst($this->value),
        };
    }

    public function isLand(): bool
    {
        return in_array($this, [self::Land, self::ResidentialLand, self::CommercialLand], true);
    }
}
