<?php

declare(strict_types=1);

namespace Src\Shared\Application\Listeners;

use Src\Backoffice\Catalog\Domain\Product\Product as BackOfficeProduct;
use Src\Backoffice\Catalog\Domain\Product\ProductCreated;
use Src\Store\Search\Domain\Product;
use Src\Store\Search\Integration\ProductCreatedMessage;

class PublishProductCreatedMessageEvent
{

    public function handle(ProductCreated $event): void
    {
        event(new ProductCreatedMessage($this->map($event->entity)));
    }

    protected function map(BackOfficeProduct $product): Product
    {
        $attributes = [];

        foreach ($product->attributes as $attribute) {
            $attributes[$attribute->name] = $attribute->data;
        }

        return new Product(
            $product->id,
            $product->information->name,
            $product->information->description,
            $product->information->manufacturer,
            $product->category->name->value,
            $product->price->amount,
            $attributes
        );
    }
}
