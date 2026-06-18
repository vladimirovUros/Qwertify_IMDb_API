<?php

namespace App\Http\Resources;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tmdb_id' => $this->tmdb_id,
            'imdb_id' => $this->imdb_id,
            'title' => $this->title,
            'original_title' => $this->original_title,
            'overview' => $this->overview,
            'tagline' => $this->tagline,
            'release_date' => $this->release_date?->toDateString(),
            'year' => $this->year,
            'runtime' => $this->runtime,
            'genres' => $this->genres ?? [],
            'original_language' => $this->original_language,
            'poster_url' => $this->imageUrl($this->poster_path, 'w500'),
            'backdrop_url' => $this->imageUrl($this->backdrop_path, 'w780'),
            'vote_average' => $this->vote_average,
            'vote_count' => $this->vote_count,
        ];
    }
    private function imageUrl(?string $path, string $size): ?string
    {
        if (blank($path)) {
            return null;
        }
        return rtrim((string) config('services.tmdb.image_base_url'), '/')."/{$size}{$path}";
    }
}
