<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Application\Providers;

use Src\Backoffice\Catalog\Application\UseCases\CreateCategory\CreateCategory;
use Src\Backoffice\Catalog\Application\UseCases\CreateCategory\CreateCategoryHandler;
use Src\Backoffice\Catalog\Application\UseCases\CreateProduct\CreateProduct;
use Src\Backoffice\Catalog\Application\UseCases\CreateProduct\CreateProductHandler;
use Src\Backoffice\Catalog\Domain\Category\CategoryRepositoryInterface;
use Src\Backoffice\Catalog\Domain\Product\ProductRepository;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Repositories\CategoryEloquentRepository;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Repositories\ProductEloquentRepository;
use Src\Backoffice\Catalog\UI\Console\CreateCategoryCommand;
use Src\Backoffice\Catalog\UI\Console\CreateProductCommand;
use Src\Backoffice\Catalog\UI\Console\ImportProductsCommand;
use Src\Shared\Infrastructure\Providers\BaseContextServiceProvider;
use Src\Store\Search\Application\Providers\EventSearchServiceProvider;

class CatalogServiceProvider extends BaseContextServiceProvider
{
    public array $binds = [
        CategoryRepositoryInterface::class => CategoryEloquentRepository::class,
        ProductRepository::class => ProductEloquentRepository::class,
    ];

    protected array $commands = [
        CreateCategoryCommand::class,
        CreateProductCommand::class,
        ImportProductsCommand::class,
    ];

    protected array $providers = [
        EventSearchServiceProvider::class
    ];
    protected array $useCases = [
        CreateCategory::class => CreateCategoryHandler::class,
        CreateProduct::class => CreateProductHandler::class,
    ];
}
