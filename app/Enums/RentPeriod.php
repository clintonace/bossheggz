<?php

namespace App\Enums;

enum RentPeriod: string
{
    case Month = 'month';
    case Year = 'year';

    public function label(): string
    {
        return match ($this) {
            self::Month => 'per month',
            self::Year => 'per year',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Month => '/mo',
            self::Year => '/yr',
        };
    }
}
