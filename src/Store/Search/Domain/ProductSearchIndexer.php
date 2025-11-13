<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain;

use Src\Backoffice\Catalog\Domain\Product\ProductId;

interface ProductSearchIndexer
{
    public function index(ProductId $id, array $data);

    public function delete(ProductId $id);
}
