<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Product;

final readonly class ProductAttribute
{
    public function __construct(
        public ProductAttributeId $id,
        public string $name,
        public mixed $data
    ) {}
}
