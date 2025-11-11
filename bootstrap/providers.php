<?php

use Src\Shared\Application\Providers\SharedServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    SharedServiceProvider::class,
    Src\Backoffice\Catalog\Application\Providers\CatalogServiceProvider::class,
];
