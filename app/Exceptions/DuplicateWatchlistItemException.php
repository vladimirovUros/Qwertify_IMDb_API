<?php

namespace App\Exceptions;

use RuntimeException;
class DuplicateWatchlistItemException extends RuntimeException
{
    public static function make(string $title): self
    {
        return new self("'{$title}' is already in your watchlist.");
    }
}
