<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Product;

use Src\Shared\Domain\Bus\DomainEvent;

readonly class ProductCreated implements DomainEvent
{
    public function __construct(
        public Product $entity
    ) {}
}
