<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Infrastructure\Eloquent\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryEloquentModel;

/**
 * @extends Factory<CategoryEloquentModel>
 */
class CategoryEloquentFactory extends Factory
{
    protected $model = CategoryEloquentModel::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => ucfirst($this->faker->word()),
        ];
    }
}
