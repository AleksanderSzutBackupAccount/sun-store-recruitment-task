<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Infrastructure\Eloquent\Repositories;

use Src\Backoffice\Catalog\Domain\Category\CategoryAttribute;
use Src\Backoffice\Catalog\Domain\Product\Product;
use Src\Backoffice\Catalog\Domain\Product\ProductRepository;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\ProductAttributeEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\ProductEloquentModel;

class ProductEloquentRepository implements ProductRepository
{
    public function save(Product $product): void
    {
        ProductEloquentModel::query()->updateOrCreate(
            ['id' => $product->id->value],
            [
                'name' => $product->information->name,
                'description' => $product->information->description,
                'manufacturer' => $product->information->manufacturer,
                'price' => $product->price->amount,
                'category_id' => $product->category->id->value,
            ]
        );

        foreach ($product->attributes as $attr) {
            /** @var CategoryAttribute $categoryAttribute */
            $categoryAttribute = $product->category->attributes->findByName($attr->name);
            ProductAttributeEloquentModel::query()->updateOrCreate(
                [
                    'category_attribute_id' => $categoryAttribute->id,
                    'product_id' => $product->id->value,
                ],
                ['value' => $attr->data]
            );
        }
    }
}
