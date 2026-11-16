<?php

namespace App\Enums;

enum AdvertPosition: string
{
    case Before = 'before';
    case After = 'after';
    case Inside = 'inside';

    public function label(): string
    {
        return match ($this) {
            self::Before => 'Before',
            self::After => 'After',
            self::Inside => 'Inside (as a card between properties)',
        };
    }
}
