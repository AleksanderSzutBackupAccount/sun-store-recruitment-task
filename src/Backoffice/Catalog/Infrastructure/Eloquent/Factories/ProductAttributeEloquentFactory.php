<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Infrastructure\Eloquent\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryAttributeEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\ProductAttributeEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\ProductEloquentModel;

/**
 * @extends Factory<ProductAttributeEloquentModel>
 */
class ProductAttributeEloquentFactory extends Factory
{
    protected $model = ProductAttributeEloquentModel::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'product_id' => ProductEloquentModel::factory(),
            'category_attribute_id' => CategoryAttributeEloquentModel::factory(),
        ];
    }
}
