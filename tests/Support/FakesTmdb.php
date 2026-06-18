<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Http;
trait FakesTmdb
{
    protected function tmdbMovie(array $overrides = []): array
    {
        return array_merge([
            'id' => 603,
            'imdb_id' => 'tt0133093',
            'title' => 'The Matrix',
            'original_title' => 'The Matrix',
            'overview' => 'A computer hacker learns the true nature of reality.',
            'tagline' => 'Welcome to the Real World.',
            'release_date' => '1999-03-30',
            'runtime' => 136,
            'genres' => [
                ['id' => 28, 'name' => 'Action'],
                ['id' => 878, 'name' => 'Science Fiction'],
            ],
            'original_language' => 'en',
            'poster_path' => '/poster.jpg',
            'backdrop_path' => '/backdrop.jpg',
            'vote_average' => 8.2,
            'vote_count' => 24000,
            'popularity' => 75.5,
        ], $overrides);
    }
    protected function fakeTmdb(?array $movie = null): array
    {
        $movie ??= $this->tmdbMovie();
        Http::fake([
            '*/find/*' => Http::response(['movie_results' => [['id' => $movie['id']]]]),
            '*/search/movie*' => Http::response(['results' => [['id' => $movie['id']]]]),
            '*/movie/*' => Http::response($movie),
        ]);

        return $movie;
    }
    protected function fakeTmdbNotFound(): void
    {
        Http::fake([
            '*/find/*' => Http::response(['movie_results' => []]),
            '*/search/movie*' => Http::response(['results' => []]),
        ]);
    }
}
