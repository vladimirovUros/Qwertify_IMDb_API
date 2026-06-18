<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WatchlistItem;

class WatchlistItemPolicy
{
    public function view(User $user, WatchlistItem $item): bool
    {
        return $this->owns($user, $item);
    }
    public function update(User $user, WatchlistItem $item): bool
    {
        return $this->owns($user, $item);
    }
    public function delete(User $user, WatchlistItem $item): bool
    {
        return $this->owns($user, $item);
    }
    private function owns(User $user, WatchlistItem $item): bool
    {
        return $item->user_id === $user->id;
    }
}
