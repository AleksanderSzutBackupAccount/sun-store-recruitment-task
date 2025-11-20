<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('/')->group(function () {
    Route::prefix('search')->group(base_path('src/Store/Search/UI/Http/api.php'));
});
Route::get('/health', \App\Http\Controllers\HealthCheckController::class);
