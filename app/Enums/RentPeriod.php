<?php

namespace App\Enums;

enum RentPeriod: string
{
    case Night = 'night';
    case Month = 'month';
    case Year = 'year';

    public function label(): string
    {
        return match ($this) {
            self::Night => 'per night',
            self::Month => 'per month',
            self::Year => 'per year',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Night => '/night',
            self::Month => '/mo',
            self::Year => '/yr',
        };
    }
}
