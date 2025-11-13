<?php

declare(strict_types=1);

use Src\Backoffice\Catalog\Application\Providers\CatalogServiceProvider;
use Src\Shared\Application\Providers\SharedServiceProvider;
use Src\Store\Search\Application\Providers\StoreSearchServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    SharedServiceProvider::class,
    CatalogServiceProvider::class,
    StoreSearchServiceProvider::class,
];
