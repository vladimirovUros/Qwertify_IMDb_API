<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Movie extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return [
            'genres' => 'array',
            'raw_payload' => 'array',
            'release_date' => 'date',
            'cached_at' => 'datetime',
            'vote_average' => 'float',
            'popularity' => 'float',
        ];
    }
    public function watchlistItems(): HasMany
    {
        return $this->hasMany(WatchlistItem::class);
    }
    public function getYearAttribute(): ?int
    {
        return $this->release_date?->year;
    }
}
