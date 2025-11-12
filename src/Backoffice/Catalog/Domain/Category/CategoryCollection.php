<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Category;

use Src\Shared\Domain\Collection\Collection;

/**
 * @extends Collection<Category>
 */
class CategoryCollection extends Collection
{
    public function findByName(string $name): ?Category
    {
        return $this->find(static fn (Category $item) => $name === $item->name->value);
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return $this->map(static fn (Category $item) => $item->name->value);
    }

    protected function type(): string
    {
        return Category::class;
    }
}
