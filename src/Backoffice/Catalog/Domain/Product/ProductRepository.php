<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Product;

interface ProductRepository
{
    public function save(Product $product): void;
}
