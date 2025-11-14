<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Application\UseCases\CreateProduct;

use Src\Backoffice\Catalog\Domain\Category\CategoryNotFound;
use Src\Backoffice\Catalog\Domain\Category\CategoryRepositoryInterface;
use Src\Backoffice\Catalog\Domain\Product\ProductBaseInfo;
use Src\Backoffice\Catalog\Domain\Product\ProductRepository;
use Src\Shared\Domain\Bus\EventBusInterface;
use Src\Shared\Domain\CategoryId;
use Src\Shared\Domain\ProductId;
use Src\Shared\Domain\ValueObjects\Money;

final readonly class ProductCreator
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private EventBusInterface $bus
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(
        ProductId $id,
        CategoryId $categoryId,
        ProductBaseInfo $info,
        Money $price,
        array $attributes
    ): void {
        $category = $this->categoryRepository->find($categoryId);

        if (! $category) {
            throw new CategoryNotFound('Category not found');
        }

        $product = $category->createProduct($id, $info, $price, $attributes);

        $this->productRepository->save($product);

        $this->bus->publish(...$product->pullDomainEvents());
    }
}
