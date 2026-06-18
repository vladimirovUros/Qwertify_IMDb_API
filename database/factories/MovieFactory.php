<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
class MovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tmdb_id' => $this->faker->unique()->numberBetween(1, 999999),
            'imdb_id' => 'tt'.$this->faker->unique()->numerify('#######'),
            'title' => $this->faker->sentence(3),
            'original_title' => $this->faker->sentence(3),
            'overview' => $this->faker->paragraph(),
            'tagline' => $this->faker->sentence(),
            'release_date' => $this->faker->date(),
            'runtime' => $this->faker->numberBetween(80, 180),
            'genres' => ['Drama', 'Action'],
            'original_language' => 'en',
            'poster_path' => '/'.$this->faker->lexify('????????').'.jpg',
            'backdrop_path' => '/'.$this->faker->lexify('????????').'.jpg',
            'vote_average' => $this->faker->randomFloat(2, 1, 10),
            'vote_count' => $this->faker->numberBetween(0, 10000),
            'popularity' => $this->faker->randomFloat(3, 0, 100),
            'raw_payload' => [],
            'cached_at' => now(),
        ];
    }
}
