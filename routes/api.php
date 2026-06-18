<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WatchlistController;
use Illuminate\Support\Facades\Route;
Route::middleware('throttle:auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'me']);
    Route::apiResource('watchlist', WatchlistController::class);
});
