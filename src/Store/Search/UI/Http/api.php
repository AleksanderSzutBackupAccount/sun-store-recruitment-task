<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Store\Search\UI\Http\Controllers\GetProductController;
use Src\Store\Search\UI\Http\Controllers\ProductFilterController;
use Src\Store\Search\UI\Http\Controllers\ProductSearchController;

Route::get('/products', ProductSearchController::class);
Route::get('/products/filters', ProductFilterController::class);
Route::get('/products/{id}', GetProductController::class);
