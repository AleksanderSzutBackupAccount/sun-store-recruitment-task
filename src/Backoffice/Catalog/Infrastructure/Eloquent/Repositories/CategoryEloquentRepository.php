<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Infrastructure\Eloquent\Repositories;

use Src\Backoffice\Catalog\Domain\Category\Category;
use Src\Backoffice\Catalog\Domain\Category\CategoryCollection;
use Src\Backoffice\Catalog\Domain\Category\CategoryName;
use Src\Backoffice\Catalog\Domain\Category\CategoryRepositoryInterface;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryAttributeEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryEloquentModel;
use Src\Shared\Domain\CategoryId;

final readonly class CategoryEloquentRepository implements CategoryRepositoryInterface
{
    public function save(Category $category): void
    {
        $model = CategoryEloquentModel::query()->updateOrCreate(
            ['id' => $category->id->value],
            ['id' => $category->id->value, 'name' => $category->name->value]
        );

        foreach ($category->attributes as $attribute) {
            CategoryAttributeEloquentModel::query()->updateOrCreate(
                ['id' => $attribute->id],
                [
                    'id' => $attribute->id,
                    'unit' => $attribute->unit,
                    'type' => $attribute->type,
                    'name' => $attribute->name,
                    'category_id' => $category->id->value,
                ]
            );
        }
    }

    public function find(CategoryId $id): ?Category
    {
        /** @var ?CategoryEloquentModel $model */
        $model = CategoryEloquentModel::with('categoryAttributes')->find($id);

        return $model?->toEntity();
    }

    public function all(): CategoryCollection
    {
        /** @var Category[] $categories */
        $categories = CategoryEloquentModel::all()->map(static fn (CategoryEloquentModel $model) => $model->toEntity())->toArray();

        return new CategoryCollection($categories);
    }

    public function findByName(CategoryName $name): ?Category
    {
        /** @var ?CategoryEloquentModel $model */
        $model = CategoryEloquentModel::with('categoryAttributes')->where('name', $name)->first();

        return $model?->toEntity();
    }
}
