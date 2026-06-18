<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Enums\WatchlistStatus;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
class WatchlistItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'movie_id' => Movie::factory(),
            'status' => WatchlistStatus::ToWatch,
            'priority' => Priority::Normal,
            'rating' => null,
            'notes' => null,
            'watched_at' => null,
        ];
    }
    public function watched(): static
    {
        return $this->state(fn () => [
            'status' => WatchlistStatus::Watched,
            'watched_at' => now(),
        ]);
    }
}
