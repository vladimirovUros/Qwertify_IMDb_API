<?php

namespace App\Http\Requests\Watchlist;

use App\Enums\Priority;
use App\Enums\WatchlistStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWatchlistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'imdb_id' => ['required_without:title', 'prohibits:title', 'string', 'regex:/^tt\d+$/'],
            'title' => ['required_without:imdb_id', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'digits:4'],

            'status' => ['sometimes', Rule::enum(WatchlistStatus::class)],
            'priority' => ['sometimes', Rule::enum(Priority::class)],
            'rating' => ['nullable', 'integer', 'between:1,10'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
    public function messages(): array
    {
        return [
            'imdb_id.regex' => 'The imdb_id must look like an IMDb identifier, e.g. "tt0133093".',
            'imdb_id.prohibits' => 'Provide either imdb_id or title, not both.',
        ];
    }
}
