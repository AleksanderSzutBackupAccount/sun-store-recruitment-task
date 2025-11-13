<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Category;

interface CategoryRepositoryInterface
{
    public function save(Category $category): void;

    public function find(CategoryId $id): ?Category;

    public function findByName(CategoryName $name): ?Category;

    public function all(): CategoryCollection;
}
