<?php

use App\Exceptions\DuplicateWatchlistItemException;
use App\Exceptions\MovieNotFoundException;
use App\Exceptions\MovieProviderException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
        $exceptions->render(fn (MovieNotFoundException $e) => response()->json(
            ['message' => $e->getMessage()], HttpResponse::HTTP_NOT_FOUND,
        ));

        $exceptions->render(fn (DuplicateWatchlistItemException $e) => response()->json(
            ['message' => $e->getMessage()], HttpResponse::HTTP_CONFLICT,
        ));

        $exceptions->render(fn (MovieProviderException $e) => response()->json(
            ['message' => $e->getMessage()], HttpResponse::HTTP_SERVICE_UNAVAILABLE,
        ));
    })->create();
