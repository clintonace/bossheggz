<?php

namespace App\Enums;

enum EngagementStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case ViewingScheduled = 'viewing_scheduled';
    case Negotiating = 'negotiating';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Received',
            self::Contacted => 'Contacted',
            self::ViewingScheduled => 'Viewing Scheduled',
            self::Negotiating => 'Negotiating',
            self::Closed => 'Closed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::New => 'Your interest has been received by our team.',
            self::Contacted => 'An agent has reached out to you.',
            self::ViewingScheduled => 'A viewing of the property has been arranged.',
            self::Negotiating => 'Terms and offers are being discussed.',
            self::Closed => 'The deal has been completed.',
            self::Cancelled => 'This engagement has been closed without a deal.',
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
            self::ViewingScheduled => 'bg-violet-50 text-violet-700 ring-violet-200',
            self::Negotiating => 'bg-amber-50 text-amber-700 ring-amber-200',
            self::Closed => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Cancelled => 'bg-rose-50 text-rose-700 ring-rose-200',
        };
    }

    /**
     * Position on the progress tracker (cancelled has no step).
     */
    public function step(): ?int
    {
        return match ($this) {
            self::New => 1,
            self::Contacted => 2,
            self::ViewingScheduled => 3,
            self::Negotiating => 4,
            self::Closed => 5,
            self::Cancelled => null,
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Closed, self::Cancelled], true);
    }

    /**
     * The ordered steps shown on the progress tracker.
     *
     * @return array<int, self>
     */
    public static function pipeline(): array
    {
        return [self::New, self::Contacted, self::ViewingScheduled, self::Negotiating, self::Closed];
    }

    /**
     * @return array<int, self>
     */
    public static function openCases(): array
    {
        return array_values(array_filter(self::cases(), fn (self $status): bool => $status->isOpen()));
    }
}
