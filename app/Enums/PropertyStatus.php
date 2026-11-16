<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case UnderOffer = 'under_offer';
    case Sold = 'sold';
    case Rented = 'rented';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Available',
            self::UnderOffer => 'Under Offer',
            self::Sold => 'Sold',
            self::Rented => 'Rented',
        };
    }

    /**
     * Tailwind classes for the status pill.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-stone-100 text-stone-600 ring-stone-200',
            self::Published => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::UnderOffer => 'bg-amber-50 text-amber-700 ring-amber-200',
            self::Sold, self::Rented => 'bg-rose-50 text-rose-700 ring-rose-200',
        };
    }

    public function isPublic(): bool
    {
        return $this !== self::Draft;
    }

    public function acceptsEngagements(): bool
    {
        return in_array($this, [self::Published, self::UnderOffer], true);
    }

    /**
     * @return array<int, self>
     */
    public static function publicCases(): array
    {
        return array_values(array_filter(self::cases(), fn (self $status): bool => $status->isPublic()));
    }
}
