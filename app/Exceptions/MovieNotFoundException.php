<?php

namespace App\Exceptions;

use RuntimeException;
class MovieNotFoundException extends RuntimeException
{
    public static function forIdentifier(string $identifier): self
    {
        return new self("No movie matching '{$identifier}' could be found.");
    }
}
