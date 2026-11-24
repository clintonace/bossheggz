<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case FollowUp = 'follow_up';
    case Interested = 'interested';
    case Converted = 'converted';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::FollowUp => 'Follow-up',
            self::Interested => 'Interested',
            self::Converted => 'Converted',
            self::Closed => 'Closed',
        };
    }

    /**
     * Tailwind classes for the status pill.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::New => 'bg-sky-50 text-sky-700 ring-sky-200',
            self::Contacted => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            self::FollowUp => 'bg-amber-50 text-amber-700 ring-amber-200',
            self::Interested => 'bg-violet-50 text-violet-700 ring-violet-200',
            self::Converted => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Closed => 'bg-stone-100 text-stone-600 ring-stone-200',
        };
    }
}
