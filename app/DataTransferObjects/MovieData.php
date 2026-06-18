<?php

namespace App\DataTransferObjects;
class MovieData
{
    public function __construct(
        public readonly int $tmdbId,
        public readonly string $title,
        public readonly ?string $imdbId = null,
        public readonly ?string $originalTitle = null,
        public readonly ?string $overview = null,
        public readonly ?string $tagline = null,
        public readonly ?string $releaseDate = null,
        public readonly ?int $runtime = null,
        public readonly array $genres = [],
        public readonly ?string $originalLanguage = null,
        public readonly ?string $posterPath = null,
        public readonly ?string $backdropPath = null,
        public readonly ?float $voteAverage = null,
        public readonly ?int $voteCount = null,
        public readonly ?float $popularity = null,
        public readonly array $raw = [],
    ) {}
    public static function fromTmdb(array $payload): self
    {
        return new self(
            tmdbId: (int) $payload['id'],
            title: $payload['title'] ?? $payload['original_title'] ?? 'Untitled',
            imdbId: $payload['imdb_id'] ?? null,
            originalTitle: $payload['original_title'] ?? null,
            overview: $payload['overview'] ?? null,
            tagline: $payload['tagline'] ?? null,
            releaseDate: ! empty($payload['release_date']) ? $payload['release_date'] : null,
            runtime: $payload['runtime'] ?? null,
            genres: array_values(array_filter(array_map(
                fn (array $genre) => $genre['name'] ?? null,
                $payload['genres'] ?? [],
            ))),
            originalLanguage: $payload['original_language'] ?? null,
            posterPath: $payload['poster_path'] ?? null,
            backdropPath: $payload['backdrop_path'] ?? null,
            voteAverage: isset($payload['vote_average']) ? (float) $payload['vote_average'] : null,
            voteCount: isset($payload['vote_count']) ? (int) $payload['vote_count'] : null,
            popularity: isset($payload['popularity']) ? (float) $payload['popularity'] : null,
            raw: $payload,
        );
    }
    public function toAttributes(): array
    {
        return [
            'tmdb_id' => $this->tmdbId,
            'imdb_id' => $this->imdbId,
            'title' => $this->title,
            'original_title' => $this->originalTitle,
            'overview' => $this->overview,
            'tagline' => $this->tagline,
            'release_date' => $this->releaseDate,
            'runtime' => $this->runtime,
            'genres' => $this->genres,
            'original_language' => $this->originalLanguage,
            'poster_path' => $this->posterPath,
            'backdrop_path' => $this->backdropPath,
            'vote_average' => $this->voteAverage,
            'vote_count' => $this->voteCount,
            'popularity' => $this->popularity,
            'raw_payload' => $this->raw,
        ];
    }
}
