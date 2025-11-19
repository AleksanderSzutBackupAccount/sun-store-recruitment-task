<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\UI\Console;

use Illuminate\Console\Command;
use Src\Backoffice\Catalog\Application\UseCases\CreateCategory\CreateCategory;
use Src\Backoffice\Catalog\Application\UseCases\CreateProduct\CreateProduct;
use Src\Backoffice\Catalog\Domain\Category\Category;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeType;
use Src\Backoffice\Catalog\Domain\Category\CategoryName;
use Src\Backoffice\Catalog\Domain\Category\CategoryRepositoryInterface;
use Src\Shared\Application\Bus\CommandHandlerInterface;
use Src\Shared\Domain\CategoryId;
use Src\Shared\Domain\ProductId;

final class ImportProductsCommand extends Command
{
    protected $signature = 'import:products';

    protected $description = 'Import products and categories from CSV files';

    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private CommandHandlerInterface $handler,
    ) {
        parent::__construct();
    }

    public function handle(
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

            if (! $fileContent = file($path)) {
                $this->warn("⚠️ File read error: {$path}");

                continue;
            }

            $rows = array_map('str_getcsv', $fileContent);
            $rawHeader = array_shift($rows);
            $header = array_map(
                static fn (?string $v): string => trim((string) $v),
                $rawHeader
            );

            $category = $this->findOrCreateCategory($categoryKey);

            foreach ($rows as $row) {
                $data = array_combine($header, $row);

                $attributes = match ($categoryKey) {
                    'batteries' => ['capacity' => (float) $data['capacity']],
                    'solar_panels' => ['power_output' => (float) $data['power_output']],
                    default => ['connector_type' => $data['connector_type']],
                };

                $productId = ProductId::generate()->value;

                $this->handler->handle(new CreateProduct(
                    $productId,
                    (string) $data['name'],
                    (string) $data['description'],
                    (string) $data['manufacturer'],
                    $category->id->value(),
                    (int) $data['price'],
                    $attributes
                ));
            }

            $this->info('✅ Imported '.count($rows)." products for category '{$categoryKey}'.");
        }

        $this->info('🎉 Import finished successfully!');
    }

    public function findOrCreateCategory(string $categoryKey): Category
    {
        $category = $this->categoryRepository->findByName(new CategoryName($categoryKey));

        if ($category instanceof Category) {
            return $category;
        }

        return $this->createCategory($categoryKey);
    }

    private function createCategory(string $categoryKey): Category
    {
        $this->info("🆕 Creating category '{$categoryKey}'...");

        $categoryAttributes = match ($categoryKey) {
            'batteries' => [['name' => 'capacity', 'type' => CategoryAttributeType::INT->value, 'unit' => 'Ah']],
            'solar_panels' => [['name' => 'power_output', 'type' => CategoryAttributeType::INT->value, 'unit' => 'W']],
            default => [['name' => 'connector_type', 'type' => CategoryAttributeType::STRING->value, 'unit' => '']],
        };

        $this->handler->handle(new CreateCategory(
            CategoryId::generate()->value,
            $categoryKey,
            $categoryAttributes
        ));

        $category = $this->categoryRepository->findByName(new CategoryName($categoryKey));

        if (! $category) {
            throw new \DomainException('Cannot create category: '.$categoryKey);
        }

        return $category;
    }
}
