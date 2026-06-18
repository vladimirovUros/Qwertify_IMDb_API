<?php

namespace Tests\Feature;

use App\Enums\WatchlistStatus;
use App\Models\Movie;
use App\Models\User;
use App\Models\WatchlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\Support\FakesTmdb;
use Tests\TestCase;

class WatchlistTest extends TestCase
{
    use FakesTmdb, RefreshDatabase;

    public function test_a_user_can_add_a_movie_by_imdb_id(): void
    {
        Sanctum::actingAs($user = User::factory()->create());
        $this->fakeTmdb();

        $this->postJson('/api/watchlist', ['imdb_id' => 'tt0133093'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'to_watch')
            ->assertJsonPath('data.movie.title', 'The Matrix')
            ->assertJsonPath('data.movie.genres', ['Action', 'Science Fiction'])
            ->assertJsonPath('data.movie.poster_url', 'https://image.tmdb.org/t/p/w500/poster.jpg');

        $this->assertDatabaseHas('movies', ['imdb_id' => 'tt0133093']);
        $this->assertDatabaseHas('watchlist_items', ['user_id' => $user->id]);
    }

    public function test_a_user_can_add_a_movie_by_title_with_initial_state(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->fakeTmdb();

        $this->postJson('/api/watchlist', [
            'title' => 'The Matrix',
            'status' => 'watching',
            'priority' => 'high',
            'notes' => 'Recommended by a friend.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'watching')
            ->assertJsonPath('data.priority', 'high')
            ->assertJsonPath('data.movie.title', 'The Matrix');
    }

    public function test_adding_an_unknown_movie_returns_404(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->fakeTmdbNotFound();

        $this->postJson('/api/watchlist', ['imdb_id' => 'tt0000000'])->assertNotFound();
    }

    public function test_store_requires_an_identifier(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/watchlist', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['imdb_id', 'title']);
    }

    public function test_a_movie_is_fetched_from_tmdb_only_once_across_users(): void
    {
        $this->fakeTmdb();

        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/watchlist', ['imdb_id' => 'tt0133093'])->assertCreated();

        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/watchlist', ['imdb_id' => 'tt0133093'])->assertCreated();

        // First add costs two calls (/find + /movie); the cached second add costs none.
        Http::assertSentCount(2);
        $this->assertSame(1, Movie::count());
    }

    public function test_a_user_cannot_add_the_same_movie_twice(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->fakeTmdb();

        $this->postJson('/api/watchlist', ['imdb_id' => 'tt0133093'])->assertCreated();
        $this->postJson('/api/watchlist', ['imdb_id' => 'tt0133093'])->assertStatus(409);
    }

    public function test_a_user_lists_only_their_own_items_and_can_filter_by_status(): void
    {
        Sanctum::actingAs($user = User::factory()->create());

        WatchlistItem::factory()->for($user)->count(2)->create(['status' => WatchlistStatus::ToWatch]);
        WatchlistItem::factory()->for($user)->watched()->create();
        WatchlistItem::factory()->create();

        $this->getJson('/api/watchlist')->assertOk()->assertJsonCount(3, 'data');
        $this->getJson('/api/watchlist?status=watched')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_the_list_is_paginated(): void
    {
        Sanctum::actingAs($user = User::factory()->create());
        WatchlistItem::factory()->for($user)->count(3)->create();

        $this->getJson('/api/watchlist?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_a_user_can_view_their_own_item(): void
    {
        Sanctum::actingAs($user = User::factory()->create());
        $item = WatchlistItem::factory()->for($user)->create();

        $this->getJson("/api/watchlist/{$item->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $item->id);
    }

    public function test_a_user_cannot_view_another_users_item(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $other = WatchlistItem::factory()->create();

        $this->getJson("/api/watchlist/{$other->id}")->assertForbidden();
    }

    public function test_updating_status_to_watched_stamps_watched_at(): void
    {
        Sanctum::actingAs($user = User::factory()->create());
        $item = WatchlistItem::factory()->for($user)->create(['status' => WatchlistStatus::ToWatch]);

        $this->patchJson("/api/watchlist/{$item->id}", ['status' => 'watched', 'rating' => 9])
            ->assertOk()
            ->assertJsonPath('data.status', 'watched')
            ->assertJsonPath('data.rating', 9);

        $this->assertNotNull($item->fresh()->watched_at);
    }

    public function test_a_user_cannot_update_another_users_item(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $other = WatchlistItem::factory()->create();

        $this->patchJson("/api/watchlist/{$other->id}", ['status' => 'watched'])->assertForbidden();
    }

    public function test_a_user_can_delete_their_item(): void
    {
        Sanctum::actingAs($user = User::factory()->create());
        $item = WatchlistItem::factory()->for($user)->create();

        $this->deleteJson("/api/watchlist/{$item->id}")->assertNoContent();
        $this->assertSoftDeleted($item);
    }
}
