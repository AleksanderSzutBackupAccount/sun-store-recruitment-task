<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain;

use Src\Shared\Domain\ProductId;

interface ProductSearchIndexer
{
    public function index(Product $product);

    public function delete(ProductId $id);
}
