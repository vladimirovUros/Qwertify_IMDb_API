<?php

namespace App\Enums;
enum Priority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
