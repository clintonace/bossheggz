<?php

namespace App\Enums;

enum AdvertWidth: string
{
    case Half = 'half';
    case Full = 'full';

    public function label(): string
    {
        return match ($this) {
            self::Half => 'Half width (6 / 12)',
            self::Full => 'Full width (12 / 12)',
        };
    }

    /**
     * Column span inside the slot's 12-column grid (always full width on phones).
     */
    public function columnClasses(): string
    {
        return match ($this) {
            self::Half => 'col-span-12 md:col-span-6',
            self::Full => 'col-span-12',
        };
    }
}
