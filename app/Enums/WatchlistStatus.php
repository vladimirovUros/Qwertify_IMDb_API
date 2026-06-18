<?php

namespace App\Enums;
enum WatchlistStatus: string
{
    case ToWatch = 'to_watch';
    case Watching = 'watching';
    case Watched = 'watched';
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
