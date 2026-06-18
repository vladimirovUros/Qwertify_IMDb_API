<?php

namespace App\Services;

use App\Enums\Priority;
use App\Enums\WatchlistStatus;
use App\Exceptions\DuplicateWatchlistItemException;
use App\Models\User;
use App\Models\WatchlistItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
class WatchlistService
{
    private const SORTABLE = ['created_at', 'updated_at', 'status', 'priority', 'rating', 'watched_at'];
    public function __construct(private readonly MovieCatalogService $catalog) {}
    public function add(User $user, array $data): WatchlistItem
    {
        $movie = filled($data['imdb_id'] ?? null)
            ? $this->catalog->resolveByImdbId($data['imdb_id'])
            : $this->catalog->resolveByTitle($data['title'], $data['year'] ?? null);

        if ($user->watchlistItems()->where('movie_id', $movie->id)->exists()) {
            throw DuplicateWatchlistItemException::make($movie->title);
        }
        $item = new WatchlistItem;
        $item->movie_id = $movie->id;
        $item->fill([
            'status' => $data['status'] ?? WatchlistStatus::ToWatch->value,
            'priority' => $data['priority'] ?? Priority::Normal->value,
            'rating' => $data['rating'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
        $this->applyWatchedAt($item);
        $user->watchlistItems()->save($item);
        return $item->load('movie');
    }
    public function list(User $user, array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? null, self::SORTABLE, true)
            ? $filters['sort']
            : 'created_at';
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 100);
        return $user->watchlistItems()
            ->with('movie')
            ->when(
                $filters['status'] ?? null,
                fn (Builder $query, string $status) => $query->where('status', $status),
            )
            ->when(
                $filters['priority'] ?? null,
                fn (Builder $query, string $priority) => $query->where('priority', $priority),
            )
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->whereHas(
                    'movie',
                    fn (Builder $movie) => $movie
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('original_title', 'like', "%{$search}%"),
                ),
            )
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();
    }
    public function update(WatchlistItem $item, array $data): WatchlistItem
    {
        $item->fill($data);
        $this->applyWatchedAt($item);
        $item->save();

        return $item->load('movie');
    }
    public function delete(WatchlistItem $item): void
    {
        $item->delete();
    }
    private function applyWatchedAt(WatchlistItem $item): void
    {
        if ($item->status === WatchlistStatus::Watched) {
            $item->watched_at ??= now();
        } else {
            $item->watched_at = null;
        }
    }
}
