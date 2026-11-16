<?php

namespace App\Enums;

enum PropertyType: string
{
    case House = 'house';
    case Apartment = 'apartment';
    case Duplex = 'duplex';
    case Townhouse = 'townhouse';
    case Land = 'land';
    case Commercial = 'commercial';
    case Office = 'office';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
