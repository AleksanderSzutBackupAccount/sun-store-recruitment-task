<?php

declare(strict_types=1);

namespace Tests\Feature\Backoffice\Catalog\UI\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeType;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryEloquentModel;
use Tests\TestCase;

class CreateCategoryCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_category_with_argument(): void
    {
        // Run the Artisan command with a name argument
        $this->artisan('catalog:create-category', ['name' => 'Electronics'])
            ->expectsOutput("✅ Category 'Electronics' created successfully!")
            ->expectsQuestion('Attribute name (leave empty to finish)', null)
            ->assertExitCode(0);

        $category = CategoryEloquentModel::query()->where('name', 'Electronics')->firstOrFail();

        $this->assertNotNull($category, 'Category should exist after running the command.');
        $this->assertEquals('Electronics', $category->name);
        $this->assertCount(0, $category->categoryAttributes);
    }

    /** @test */
    public function it_creates_a_category_with_interactive_input(): void
    {
        $this->artisan('catalog:create-category')
            ->expectsQuestion('Enter category name', 'Shoes')
            ->expectsQuestion('Attribute name (leave empty to finish)', 'size')
            ->expectsChoice('Attribute type', CategoryAttributeType::INT->value, CategoryAttributeType::values())
            ->expectsQuestion('Attribute unit', 'cm')
            ->expectsQuestion('Attribute name (leave empty to finish)', null)
            ->expectsOutput("✅ Category 'Shoes' created successfully!")
            ->assertExitCode(0);

        $category = CategoryEloquentModel::query()->where('name', 'Shoes')->firstOrFail();

        $this->assertNotNull($category);
        $this->assertEquals('Shoes', $category->name);
        $this->assertCount(1, $category->categoryAttributes);
        $this->assertEquals('size', $category->categoryAttributes->first->get()->name);
    }
}
