<?php

declare(strict_types=1);

namespace Tests\Feature\Backoffice\Catalog\UI\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Src\Backoffice\Catalog\Domain\Product\ProductCreated;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\ProductEloquentModel;
use Src\Shared\Domain\CategoryId;
use Tests\TestCase;

final class CreateProductCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_new_product_via_artisan_command(): void
    {
        Event::fake([ProductCreated::class]);

        $categoryId = CategoryId::generate();

        /** @var CategoryEloquentModel $category */
        $category = CategoryEloquentModel::query()->create([
            'id' => $categoryId,
            'name' => 'Electronics',
        ]);

        $category->categoryAttributes()->createMany([
            ['name' => 'Weight', 'type' => 'int', 'unit' => 'kg'],
            ['name' => 'Color', 'type' => 'string', 'unit' => null],
        ]);

        $this->artisan('catalog:create-product', ['name' => 'Laptop'])
            ->expectsQuestion('Enter product description', 'High-end laptop')
            ->expectsQuestion('Enter manufacturer name', 'Dell')
            ->expectsQuestion('Enter price (integer value)', '5000')
            ->expectsChoice('Select category', 'Electronics', ['Electronics'])
            ->expectsQuestion('Enter value for Weight (kg)', '2.5')
            ->expectsQuestion('Enter value for Color', 'Black')
            ->expectsOutput("✅ Product 'Laptop' created successfully in category 'Electronics'!")
            ->assertExitCode(0);

        $this->assertDatabaseHas('products', [
            'name' => 'Laptop',
            'manufacturer' => 'Dell',
            'price' => 5000,
        ]);

        $product = ProductEloquentModel::query()->where('name', 'Laptop')->first();
        $this->assertEquals($categoryId->value, $product->category_id);

        Event::assertDispatched(ProductCreated::class);
    }
}
