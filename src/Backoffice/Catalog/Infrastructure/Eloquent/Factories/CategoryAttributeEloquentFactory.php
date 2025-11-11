<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Infrastructure\Eloquent\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeId;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeType;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryAttributeEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryEloquentModel;

/**
 * @extends Factory<CategoryAttributeEloquentModel>
 */
class CategoryAttributeEloquentFactory extends Factory
{
    protected $model = CategoryAttributeEloquentModel::class;

    public function definition(): array
    {
        return [
            'id' => CategoryAttributeId::generate(),
            'category_id' => CategoryEloquentModel::factory(),
            'name' => $this->faker->word(),
            'unit' => CategoryAttributeType::randomValue(),
        ];
    }
}
