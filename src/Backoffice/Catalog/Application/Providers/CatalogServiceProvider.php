<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Application\Providers;

use Src\Backoffice\Catalog\Application\UseCases\CreateCategory\CreateCategory;
use Src\Backoffice\Catalog\Application\UseCases\CreateCategory\CreateCategoryHandler;
use Src\Backoffice\Catalog\Domain\Category\CategoryRepositoryInterface;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Repositories\CategoryEloquentRepository;
use Src\Backoffice\Catalog\UI\Console\CreateCategoryCommand;
use Src\Shared\Infrastructure\Providers\BaseContextServiceProvider;

class CatalogServiceProvider extends BaseContextServiceProvider
{
    public array $binds = [
        CategoryRepositoryInterface::class => CategoryEloquentRepository::class,
    ];

    protected array $commands = [
        CreateCategoryCommand::class,
    ];

    protected array $useCases = [
        CreateCategory::class => CreateCategoryHandler::class,
    ];
}
