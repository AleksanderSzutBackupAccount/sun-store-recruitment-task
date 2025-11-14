<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Application\UseCases\CreateCategory;

use Src\Backoffice\Catalog\Domain\Category\Category;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttribute;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeCollection;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeId;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeType;
use Src\Backoffice\Catalog\Domain\Category\CategoryName;
use Src\Backoffice\Catalog\Domain\Category\CategoryRepositoryInterface;
use Src\Shared\Domain\Bus\EventBusInterface;
use Src\Shared\Domain\CategoryId;

final readonly class CategoryCreator
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
        private EventBusInterface $bus
    ) {}

    /**
     * @param  array{name: string, type: string, unit: string}[]  $attributesDefinition
     */
    public function create(
        CategoryId $id,
        CategoryName $name,
        array $attributesDefinition
    ): void {
        $attributes = $this->createAttributes($attributesDefinition);

        $category = Category::create($id, $name, $attributes);

        $this->repository->save($category);

        $this->bus->publish(...$category->pullDomainEvents());
    }

    /**
     * /**
     * @param  array{name: string, type: string, unit: string}[]  $attributesDefinition
     */
    private function createAttributes(array $attributesDefinition): CategoryAttributeCollection
    {
        $attributes = new CategoryAttributeCollection([]);

        foreach ($attributesDefinition as $attribute) {
            $attributes->push(
                CategoryAttribute::create(
                    CategoryAttributeId::generate(),
                    $attribute['name'],
                    CategoryAttributeType::from($attribute['type']),
                    $attribute['unit'],
                )
            );
        }

        return $attributes;
    }
}
