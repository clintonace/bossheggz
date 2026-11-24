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

    /**
     * How the status reads to the client in their dashboard.
     */
    public function clientLabel(): string
    {
        return match ($this) {
            self::New => 'Received',
            self::Contacted => 'Adviser in touch',
            self::FollowUp => 'Following up',
            self::Interested => 'Exploring options',
            self::Converted => 'Completed',
            self::Closed => 'Closed',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::New => 'We have received your enquiry. An adviser will be in touch shortly.',
            self::Contacted => 'An adviser has reached out to you about this enquiry.',
            self::FollowUp => 'Your adviser is following up on the details with you.',
            self::Interested => 'You are reviewing the options with your adviser.',
            self::Converted => 'This enquiry is complete. Thank you for choosing us.',
            self::Closed => 'This enquiry has been closed. You can always send a new one.',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Converted, self::Closed], true);
    }

    /**
     * Position on the client's progress tracker (closed has no step).
     */
    public function step(): ?int
    {
        return match ($this) {
            self::New => 1,
            self::Contacted, self::FollowUp => 2,
            self::Interested => 3,
            self::Converted => 4,
            self::Closed => null,
        };
    }

    /**
     * The steps shown on the client's progress tracker.
     *
     * @return array<int, string>
     */
    public static function clientSteps(): array
    {
        return [1 => 'Received', 2 => 'Adviser in touch', 3 => 'Exploring options', 4 => 'Completed'];
    }
}
