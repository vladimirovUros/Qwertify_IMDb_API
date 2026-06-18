<?php

namespace App\Http\Resources;

use App\Models\WatchlistItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class WatchlistItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'rating' => $this->rating,
            'notes' => $this->notes,
            'watched_at' => $this->watched_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'movie' => new MovieResource($this->whenLoaded('movie')),
        ];
    }
}
