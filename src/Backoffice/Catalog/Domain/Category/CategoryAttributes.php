<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Category;

use Src\Shared\Domain\Collection\Collection;

/**
 * @extends Collection<CategoryAttribute>
 */
class CategoryAttributes extends Collection
{
    protected function type(): string
    {
        return CategoryAttribute::class;
    }
}
