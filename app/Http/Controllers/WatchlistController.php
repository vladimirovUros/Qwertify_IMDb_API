<?php

namespace App\Http\Controllers;

use App\Http\Requests\Watchlist\IndexWatchlistRequest;
use App\Http\Requests\Watchlist\StoreWatchlistItemRequest;
use App\Http\Requests\Watchlist\UpdateWatchlistItemRequest;
use App\Http\Resources\WatchlistItemResource;
use App\Models\WatchlistItem;
use App\Services\WatchlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class WatchlistController extends Controller
{
    public function __construct(private readonly WatchlistService $service) {}
    public function index(IndexWatchlistRequest $request): AnonymousResourceCollection
    {
        $items = $this->service->list($request->user(), $request->validated());

        return WatchlistItemResource::collection($items);
    }
    public function store(StoreWatchlistItemRequest $request): JsonResponse
    {
        $item = $this->service->add($request->user(), $request->validated());

        return (new WatchlistItemResource($item))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
    public function show(WatchlistItem $watchlist): WatchlistItemResource
    {
        Gate::authorize('view', $watchlist);

        return new WatchlistItemResource($watchlist->load('movie'));
    }
    public function update(UpdateWatchlistItemRequest $request, WatchlistItem $watchlist): WatchlistItemResource
    {
        Gate::authorize('update', $watchlist);

        $item = $this->service->update($watchlist, $request->validated());

        return new WatchlistItemResource($item);
    }
    public function destroy(WatchlistItem $watchlist): Response
    {
        Gate::authorize('delete', $watchlist);

        $this->service->delete($watchlist);

        return response()->noContent();
    }
}
