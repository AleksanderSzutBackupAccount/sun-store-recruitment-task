<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain;

use Src\Shared\Domain\ProductId;

interface ProductSearchIndexer
{
    /**
     * @return mixed[]
     */
    public function index(Product $product): array;

    public function delete(ProductId $id): void;
}
