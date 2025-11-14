<?php

declare(strict_types=1);

namespace Src\Store\Search\Integration;

use Src\Store\Search\Domain\Product;

class ProductCreatedMessage
{
    public function __construct(
        public Product $entity
    )
    {
    }
}
