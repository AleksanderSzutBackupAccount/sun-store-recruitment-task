<?php

declare(strict_types=1);

namespace Tests\Feature\Backoffice\Catalog\Infrastructure\Eloquent\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Src\Backoffice\Catalog\Domain\Category\Category;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttribute;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeId;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributes;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeType;
use Src\Backoffice\Catalog\Domain\Category\CategoryId;
use Src\Backoffice\Catalog\Domain\Category\CategoryName;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryAttributeEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Repositories\CategoryEloquentRepository;
use Tests\TestCase;

class CategoryEloquentRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private CategoryEloquentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->get(CategoryEloquentRepository::class);
    }

    public function test_save_create(): void
    {
        $category = Category::create(
            CategoryId::generate(),
            new CategoryName($this->faker->name),
            CategoryAttributes::new(
                new CategoryAttribute(
                    CategoryAttributeId::generate(),
                    $this->faker->word,
                    CategoryAttributeType::STRING
                )
            )
        );

        $this->repository->save($category);

        $this->assertEquals(1, CategoryEloquentModel::query()->count());
        $this->assertEquals(1, CategoryAttributeEloquentModel::query()->count());
    }

    public function test_save_update(): void
    {
        $model = CategoryEloquentModel::factory()->create();
        CategoryAttributeEloquentModel::factory()->create(['category_id' => $model->id]);
        $model->refresh();

        $entity = $model->toEntity();
        $entity->attributes->push(new CategoryAttribute(CategoryAttributeId::generate(), $this->faker->name, CategoryAttributeType::STRING));

        $this->repository->save($entity);

        $this->assertEquals(1, CategoryEloquentModel::query()->count());
        $this->assertEquals(2, CategoryAttributeEloquentModel::query()->count());
    }
}
