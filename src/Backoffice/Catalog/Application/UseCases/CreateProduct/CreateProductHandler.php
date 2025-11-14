<?php

namespace Src\Backoffice\Catalog\Application\UseCases\CreateProduct;

use Src\Backoffice\Catalog\Domain\Product\ProductBaseInfo;
use Src\Shared\Domain\CategoryId;
use Src\Shared\Domain\ProductId;
use Src\Shared\Domain\ValueObjects\Money;

final readonly class CreateProductHandler
{
    public function __construct(private ProductCreator $productCreator) {}

    public function handle(CreateProduct $command): void
    {
        $this->productCreator->create(
            new ProductId($command->id),
            new CategoryId($command->categoryId),
            new ProductBaseInfo(
                $command->name,
                $command->manufacturer,
                $command->description
            ),
            new Money($command->price),
            $command->attributes
        );
    }
}
