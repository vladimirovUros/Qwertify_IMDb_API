<?php

namespace App\Services\Movies\Tmdb;

use App\DataTransferObjects\MovieData;
use App\Exceptions\MovieProviderException;
use App\Services\Movies\Contracts\MovieDataProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
class TmdbClient implements MovieDataProvider
{
    public function __construct(
        private readonly string $token,
        private readonly string $baseUrl,
        private readonly int $timeout,
        private readonly int $retries,
    ) {}
    public function findByImdbId(string $imdbId): ?MovieData
    {
        $tmdbId = $this->get('/find/'.$imdbId, ['external_source' => 'imdb_id'])
            ->json('movie_results.0.id');
        return $tmdbId ? $this->getDetails((int) $tmdbId) : null;
    }
    public function findByTitle(string $title, ?int $year = null): ?MovieData
    {
        $tmdbId = $this->get('/search/movie', array_filter([
            'query' => $title,
            'year' => $year,
        ]))->json('results.0.id');
        return $tmdbId ? $this->getDetails((int) $tmdbId) : null;
    }
    private function getDetails(int $tmdbId): ?MovieData
    {
        $response = $this->get('/movie/'.$tmdbId);
        if ($response->status() === 404) {
            return null;
        }
        return MovieData::fromTmdb($response->json());
    }
    private function get(string $path, array $query = []): Response
    {
        try {
            $response = $this->request()->get($path, $query);
        } catch (ConnectionException $e) {
            throw MovieProviderException::unreachable($e);
        }
        if ($response->serverError() || ($response->clientError() && $response->status() !== 404)) {
            throw MovieProviderException::badResponse($response->status());
        }
        return $response;
    }
    private function request(): PendingRequest
    {
        if (blank($this->token)) {
            throw MovieProviderException::notConfigured();
        }
        return Http::baseUrl($this->baseUrl)
            ->withToken($this->token)
            ->acceptJson()
            ->timeout($this->timeout)
            ->retry($this->retries, 200, throw: false);
    }
}
