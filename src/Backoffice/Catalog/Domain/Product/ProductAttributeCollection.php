<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Product;

use Src\Shared\Domain\Collection\Collection;

/**
 * @extends Collection<ProductAttribute>
 */
class ProductAttributeCollection extends Collection
{
    protected function type(): string
    {
        return ProductAttribute::class;
    }
}
