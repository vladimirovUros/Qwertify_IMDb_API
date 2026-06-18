<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\WatchlistStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class WatchlistItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected function casts(): array
    {
        return [
            'status' => WatchlistStatus::class,
            'priority' => Priority::class,
            'rating' => 'integer',
            'watched_at' => 'datetime',
        ];
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
    public function scopeForUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }
}
