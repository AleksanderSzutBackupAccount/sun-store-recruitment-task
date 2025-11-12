<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Application\UseCases\CreateProduct;

use Src\Shared\Domain\Bus\CommandInterface;

readonly class CreateProduct implements CommandInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $manufacturer,
        public string $categoryId,
        public int $price,
        public array $attributes
    ) {}
}
