<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Product;

use Src\Backoffice\Catalog\Domain\Category\Category;
use Src\Shared\Domain\Aggregate\AggregateRoot;
use Src\Shared\Domain\ValueObjects\Money;

class Product extends AggregateRoot
{
    public function __construct(
        public readonly ProductId $id,
        public ProductBaseInfo $information,
        public Category $category,
        public Money $price,
        public ProductAttributeCollection $attributes
    ) {}

    public static function create(
        ProductId $id,
        ProductBaseInfo $information,
        Category $category,
        Money $price,
        ProductAttributeCollection $attributes
    ): self {
        $entity = new self(
            $id,
            $information,
            $category,
            $price,
            $attributes
        );

        $entity->record(new ProductCreated($id));

        return $entity;
    }
}
