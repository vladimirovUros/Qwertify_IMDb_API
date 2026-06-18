<?php

namespace App\Http\Requests\Watchlist;

use App\Enums\Priority;
use App\Enums\WatchlistStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWatchlistItemRequest extends FormRequest
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
            'rating' => ['sometimes', 'nullable', 'integer', 'between:1,10'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
