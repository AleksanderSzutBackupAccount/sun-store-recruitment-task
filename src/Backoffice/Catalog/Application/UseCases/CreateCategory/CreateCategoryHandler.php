<?php

namespace Src\Backoffice\Catalog\Application\UseCases\CreateCategory;

use Src\Backoffice\Catalog\Domain\Category\CategoryName;
use Src\Shared\Domain\CategoryId;

final readonly class CreateCategoryHandler
{
    public function __construct(private CategoryCreator $categoryCreator) {}

    public function handle(CreateCategory $command): void
    {
        $this->categoryCreator->create(
            new CategoryId($command->id),
            new CategoryName($command->name),
            $command->attributes
        );
    }
}
