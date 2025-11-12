<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Category;

use Src\Shared\Domain\Collection\Collection;

/**
 * @extends Collection<CategoryAttribute>
 */
class CategoryAttributeCollection extends Collection
{
    protected function type(): string
    {
        return CategoryAttribute::class;
    }

    public function findByName(string $name): ?CategoryAttribute
    {
        return $this->find(static fn (CategoryAttribute $item) => $name === $item->name);
    }
}
