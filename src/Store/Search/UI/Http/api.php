<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Store\Search\UI\Http\Controllers\ProductSearchController;

Route::get('/products', ProductSearchController::class);
