<?php

namespace App\Services;

use App\DataTransferObjects\MovieData;
use App\Exceptions\MovieNotFoundException;
use App\Models\Movie;
use App\Services\Movies\Contracts\MovieDataProvider;
use Illuminate\Support\Carbon;
class MovieCatalogService
{
    public function __construct(private readonly MovieDataProvider $provider) {}
    public function resolveByImdbId(string $imdbId): Movie
    {
        $existing = Movie::where('imdb_id', $imdbId)->first();
        if ($existing && $this->isFresh($existing)) {
            return $existing;
        }
        $data = $this->provider->findByImdbId($imdbId);
        if (! $data) {
            throw MovieNotFoundException::forIdentifier($imdbId);
        }
        return $this->store($data);
    }
    public function resolveByTitle(string $title, ?int $year = null): Movie
    {
        $data = $this->provider->findByTitle($title, $year);
        if (! $data) {
            throw MovieNotFoundException::forIdentifier($title);
        }
        return $this->store($data);
    }
    private function store(MovieData $data): Movie
    {
        return Movie::updateOrCreate(
            ['tmdb_id' => $data->tmdbId],
            [...$data->toAttributes(), 'cached_at' => now()],
        );
    }
    private function isFresh(Movie $movie): bool
    {
        $ttlMinutes = (int) config('services.tmdb.cache_ttl');
        return $movie->cached_at !== null
            && $movie->cached_at->gt(Carbon::now()->subMinutes($ttlMinutes));
    }
}
