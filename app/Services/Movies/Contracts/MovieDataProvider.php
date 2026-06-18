<?php

namespace App\Services\Movies\Contracts;

use App\DataTransferObjects\MovieData;
use App\Exceptions\MovieProviderException;
interface MovieDataProvider
{
    public function findByImdbId(string $imdbId): ?MovieData;
    public function findByTitle(string $title, ?int $year = null): ?MovieData;
}
