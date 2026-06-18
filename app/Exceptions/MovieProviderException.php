<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;
class MovieProviderException extends RuntimeException
{
    public static function unreachable(?Throwable $previous = null): self
    {
        return new self('The movie service is currently unreachable. Please try again later.', 0, $previous);
    }
    public static function badResponse(int $status): self
    {
        return new self("The movie service returned an unexpected response (HTTP {$status}).");
    }
    public static function notConfigured(): self
    {
        return new self('The movie service is not configured. Set TMDB_TOKEN in your environment.');
    }
}
