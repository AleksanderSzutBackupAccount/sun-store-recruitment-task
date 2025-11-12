<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Infrastructure\Eloquent\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\ProductEloquentModel;

/**
 * @extends Factory<ProductEloquentModel>
 */
class ProductEloquentFactory extends Factory
{
    protected $model = ProductEloquentModel::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'category_id' => CategoryEloquentModel::factory(),
            'name' => $this->faker->word,
            'description' => $this->faker->paragraph(),
            'manufacturer' => $this->faker->company(),
            'price' => $this->faker->numberBetween(100, 10000),
        ];
    }
}
