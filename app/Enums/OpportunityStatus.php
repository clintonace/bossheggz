<?php

namespace App\Enums;

enum OpportunityStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Open => 'Open to enquiries',
            self::Closed => 'Closed',
        };
    }

    /**
     * Tailwind classes for the status pill.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-stone-100 text-stone-600 ring-stone-200',
            self::Open => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Closed => 'bg-rose-50 text-rose-700 ring-rose-200',
        };
    }

    public function isPublic(): bool
    {
        return $this !== self::Draft;
    }
}
