<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\UI\Console;

use Illuminate\Console\Command;
use Src\Backoffice\Catalog\Application\UseCases\CreateProduct\CreateProduct;
use Src\Backoffice\Catalog\Domain\Category\Category;
use Src\Backoffice\Catalog\Domain\Category\CategoryRepositoryInterface;
use Src\Backoffice\Catalog\Domain\Product\ProductId;
use Src\Shared\Application\Bus\CommandHandlerInterface;

final class CreateProductCommand extends Command
{
    protected $signature = 'catalog:create-product
                            {name? : Product name}';

    protected $description = 'Create a new product within a category (CQRS style)';

    public function handle(
        CommandHandlerInterface $handler,
        CategoryRepositoryInterface $categoryRepository
    ): void {
        $categories = $categoryRepository->all();

        if ($categories->isEmpty()) {
            $this->error('⚠️ No categories found! Please create one first.');

            return;
        }

        $name = (string) ($this->argument('name') ?? $this->ask('Enter product name'));
        $description = (string) $this->ask('Enter product description');
        $manufacturer = (string) $this->ask('Enter manufacturer name');
        $price = (int) $this->ask('Enter price (integer value)');

        $categoryNames = $categories->getNames();
        $categoryName = $this->choice('Select category', $categoryNames);

        if (is_array($categoryName)) {
            $this->error('⚠️ Select only one category');

            return;
        }

        /** @var Category $category */
        $category = $categories->findByName($categoryName);

        $categoryId = $category->id->value();

        $attributes = [];

        $this->info("Now enter values for attributes of category: {$categoryName}");

        foreach ($category->attributes as $attribute) {
            $attrName = $attribute->name;
            $attrType = $attribute->type;
            $unit = $attribute->unit;

            $label = $unit ? "{$attrName} ({$unit})" : $attrName;

            $value = $this->ask("Enter value for {$label}");

            $attributes[$attrName] = (string) $value;
        }

        $handler->handle(new CreateProduct(
            ProductId::generate()->value,
            $name,
            $description,
            $manufacturer,
            $categoryId,
            $price,
            $attributes
        ));

        $this->info("✅ Product '{$name}' created successfully in category '{$categoryName}'!");
    }
}
