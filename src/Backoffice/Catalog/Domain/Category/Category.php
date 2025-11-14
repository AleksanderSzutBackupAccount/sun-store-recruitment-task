<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Category;

use DomainException;
use Src\Backoffice\Catalog\Domain\Product\Product;
use Src\Backoffice\Catalog\Domain\Product\ProductAttribute;
use Src\Backoffice\Catalog\Domain\Product\ProductAttributeCollection;
use Src\Backoffice\Catalog\Domain\Product\ProductAttributeId;
use Src\Backoffice\Catalog\Domain\Product\ProductBaseInfo;
use Src\Shared\Domain\Aggregate\AggregateRoot;
use Src\Shared\Domain\CategoryId;
use Src\Shared\Domain\ProductId;
use Src\Shared\Domain\ValueObjects\Money;

final class Category extends AggregateRoot
{
    public function __construct(public CategoryId $id, public CategoryName $name, public CategoryAttributeCollection $attributes) {}

    public static function create(CategoryId $id, CategoryName $name, CategoryAttributeCollection $attributes): self
    {
        $entity = new self($id, $name, $attributes);

        $entity->record(new CategoryCreated($entity));

        return $entity;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createProduct(
        ProductId $id,
        ProductBaseInfo $info,
        Money $price,
        array $attributes
    ): Product {
        $productAttributes = $this->createAttributes($attributes);

        return Product::create(
            $id,
            $info,
            $this,
            $price,
            $productAttributes
        );
    }

    /**
     * @param  array<string, mixed>  $givenAttributes
     */
    private function createAttributes(array $givenAttributes): ProductAttributeCollection
    {
        $productAttributes = ProductAttributeCollection::new();

        foreach ($this->attributes as $expectedAttr) {
            $name = $expectedAttr->name;

            if (! array_key_exists($name, $givenAttributes)) {
                throw new DomainException("Missing attribute: {$name}");
            }

            $value = $givenAttributes[$name];

            if ($expectedAttr->type->isInt() && ! is_numeric($value)) {
                throw new DomainException("Attribute {$name} must be numeric");
            }

            if ($expectedAttr->type->isString() && ! is_string($value)) {
                throw new DomainException("Attribute {$name} must be string");
            }

            if ($expectedAttr->type->isFloat() && ! is_bool($value)) {
                throw new DomainException("Attribute {$name} must be boolean");
            }
            $productAttributes->push(new ProductAttribute(ProductAttributeId::generate(), $name, (string) $value));
        }

        return $productAttributes;
    }
}
