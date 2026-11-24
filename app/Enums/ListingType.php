<?php

namespace App\Enums;

enum ListingType: string
{
    case Sale = 'sale';
    case Rent = 'rent';
    case Shortlet = 'shortlet';
    case Lease = 'lease';

    public function label(): string
    {
        return match ($this) {
            self::Sale => 'For Sale',
            self::Rent => 'For Rent',
            self::Shortlet => 'Shortlet',
            self::Lease => 'For Lease',
        };
    }

    /**
     * Heading used on the listings page when filtered to this type.
     */
    public function heading(): string
    {
        return match ($this) {
            self::Sale => 'Properties for sale',
            self::Rent => 'Properties for rent',
            self::Shortlet => 'Shortlet apartments',
            self::Lease => 'Properties for lease',
        };
    }

    /**
     * Whether the price is paid per period (rent, shortlet or lease) rather than once.
     */
    public function isPeriodic(): bool
    {
        return $this !== self::Sale;
    }

    public function defaultPeriod(): ?RentPeriod
    {
        return match ($this) {
            self::Sale => null,
            self::Shortlet => RentPeriod::Night,
            self::Rent, self::Lease => RentPeriod::Year,
        };
    }

    /**
     * Listing types that are paid per period.
     *
     * @return array<int, string>
     */
    public static function periodicValues(): array
    {
        return collect(self::cases())->filter->isPeriodic()->map->value->values()->all();
    }
}
