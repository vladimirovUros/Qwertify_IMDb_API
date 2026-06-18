<?php

namespace App\Providers;

use App\Services\Movies\Contracts\MovieDataProvider;
use App\Services\Movies\Tmdb\TmdbClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MovieDataProvider::class, function ($app): TmdbClient {
            $config = $app['config']->get('services.tmdb');

            return new TmdbClient(
                token: (string) ($config['token'] ?? ''),
                baseUrl: rtrim((string) $config['base_url'], '/'),
                timeout: (int) $config['timeout'],
                retries: (int) $config['retries'],
            );
        });
    }
    public function boot(): void
    {
        $this->configureRateLimiting();
    }
    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(10)
            ->by($request->ip()));
    }
}
