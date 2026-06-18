<?php

namespace App\Http\Requests\Watchlist;

use App\Enums\Priority;
use App\Enums\WatchlistStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexWatchlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(WatchlistStatus::class)],
            'priority' => ['sometimes', Rule::enum(Priority::class)],
            'search' => ['sometimes', 'string', 'max:255'],
            'sort' => ['sometimes', Rule::in(['created_at', 'updated_at', 'status', 'priority', 'rating', 'watched_at'])],
            'direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }
}
