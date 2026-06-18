<?php

namespace Tests\Unit;

use App\DataTransferObjects\MovieData;
use PHPUnit\Framework\TestCase;

class MovieDataTest extends TestCase
{
    public function test_it_maps_a_tmdb_payload_into_normalized_fields(): void
    {
        $dto = MovieData::fromTmdb([
            'id' => 603,
            'imdb_id' => 'tt0133093',
            'title' => 'The Matrix',
            'original_title' => 'The Matrix',
            'release_date' => '1999-03-30',
            'runtime' => 136,
            'genres' => [
                ['id' => 28, 'name' => 'Action'],
                ['id' => 878, 'name' => 'Science Fiction'],
            ],
            'vote_average' => 8.2,
        ]);
        $this->assertSame(603, $dto->tmdbId);
        $this->assertSame('tt0133093', $dto->imdbId);
        $this->assertSame(['Action', 'Science Fiction'], $dto->genres);
        $this->assertSame(8.2, $dto->voteAverage);
    }
    public function test_it_maps_to_persistable_attributes_and_keeps_the_raw_payload(): void
    {
        $payload = ['id' => 1, 'title' => 'Test', 'genres' => []];
        $attributes = MovieData::fromTmdb($payload)->toAttributes();
        $this->assertSame(1, $attributes['tmdb_id']);
        $this->assertSame('Test', $attributes['title']);
        $this->assertSame($payload, $attributes['raw_payload']);
    }
    public function test_it_tolerates_missing_optional_fields(): void
    {
        $dto = MovieData::fromTmdb(['id' => 5, 'title' => 'Bare']);
        $this->assertNull($dto->imdbId);
        $this->assertNull($dto->runtime);
        $this->assertSame([], $dto->genres);
        $this->assertNull($dto->releaseDate);
    }
}
