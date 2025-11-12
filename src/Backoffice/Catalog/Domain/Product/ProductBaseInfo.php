<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Product;

final class ProductBaseInfo
{
    public function __construct(
        public string $name,
        public string $manufacturer,
        public string $description
    ) {}
}
