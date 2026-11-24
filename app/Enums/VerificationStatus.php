<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Pending = 'pending';
    case DocumentsAvailable = 'documents_available';
    case Verified = 'verified';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Verification pending',
            self::DocumentsAvailable => 'Documents available',
            self::Verified => 'Title verified',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'clock',
            self::DocumentsAvailable => 'copy',
            self::Verified => 'shield',
        };
    }

    /**
     * Tailwind classes for the verification pill.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-stone-50 text-stone-600 ring-stone-200',
            self::DocumentsAvailable => 'bg-sky-50 text-sky-700 ring-sky-200',
            self::Verified => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        };
    }
}
