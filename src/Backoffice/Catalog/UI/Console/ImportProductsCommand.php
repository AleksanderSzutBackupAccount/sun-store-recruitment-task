<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\UI\Console;

use Illuminate\Console\Command;
use Src\Backoffice\Catalog\Application\UseCases\CreateCategory\CreateCategory;
use Src\Backoffice\Catalog\Application\UseCases\CreateProduct\CreateProduct;
use Src\Backoffice\Catalog\Domain\Category\Category;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeType;
use Src\Backoffice\Catalog\Domain\Category\CategoryId;
use Src\Backoffice\Catalog\Domain\Category\CategoryName;
use Src\Backoffice\Catalog\Domain\Category\CategoryRepositoryInterface;
use Src\Backoffice\Catalog\Domain\Product\ProductId;
use Src\Shared\Application\Bus\CommandHandlerInterface;

final class ImportProductsCommand extends Command
{
    protected $signature = 'import:products';

    protected $description = 'Import products and categories from CSV files';

    public function handle(
        CommandHandlerInterface $handler,
        CategoryRepositoryInterface $categoryRepository
    ): void {
        $files = [
            'batteries.csv' => 'batteries',
            'solar_panels.csv' => 'solar_panels',
            'connectors.csv' => 'connectors',
        ];

        foreach ($files as $file => $categoryKey) {
            $path = base_path("data/{$file}");
            if (! file_exists($path)) {
                $this->warn("⚠️ File not found: {$path}");

                continue;
            }

            $rows = array_map('str_getcsv', file($path));
            $header = array_map('trim', array_shift($rows));

            $category = $categoryRepository->findByName(new CategoryName($categoryKey));
            if (! $category instanceof Category) {
                $this->info("🆕 Creating category '{$categoryKey}'...");

                $attributes = match ($categoryKey) {
                    'batteries' => [['name' => 'capacity', 'type' => CategoryAttributeType::INT->value, 'unit' => 'Ah']],
                    'solar_panels' => [['name' => 'power_output', 'type' => CategoryAttributeType::INT->value, 'unit' => 'W']],
                    'connectors' => [['name' => 'connector_type', 'type' => CategoryAttributeType::STRING->value, 'unit' => null]],
                    default => [],
                };

                $handler->handle(new CreateCategory(
                    CategoryId::generate()->value,
                    $categoryKey,
                    $attributes
                ));

                $category = $categoryRepository->findByName(new CategoryName($categoryKey));
            }

            foreach ($rows as $row) {
                $data = array_combine($header, $row);

                $attributes = match ($categoryKey) {
                    'batteries' => ['capacity' => (float) $data['capacity']],
                    'solar_panels' => ['power_output' => (float) $data['power_output']],
                    'connectors' => ['connector_type' => $data['connector_type']],
                    default => [],
                };

                $productId = ProductId::generate()->value;

                $handler->handle(new CreateProduct(
                    $productId,
                    $data['name'],
                    $data['description'],
                    $data['manufacturer'],
                    $category->id->value(),
                    (int) $data['price'],
                    $attributes
                ));
            }

            $this->info('✅ Imported '.count($rows)." products for category '{$categoryKey}'.");
        }

        $this->info('🎉 Import finished successfully!');
    }
}
