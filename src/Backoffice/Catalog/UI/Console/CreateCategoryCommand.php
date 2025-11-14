<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\UI\Console;

use Illuminate\Console\Command;
use Src\Backoffice\Catalog\Application\UseCases\CreateCategory\CreateCategory;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeType;
use Src\Shared\Application\Bus\CommandHandlerInterface;
use Src\Shared\Domain\CategoryId;

final class CreateCategoryCommand extends Command
{
    protected $signature = 'catalog:create-category
                            {name? : The name of the category}';

    protected $description = 'Create a new product category with attributes';

    public function handle(CommandHandlerInterface $handler): void
    {
        $name = (string) ($this->argument('name') ?? $this->ask('Enter category name'));

        $attributes = [];
        while (true) {
            $attrName = $this->ask('Attribute name (leave empty to finish)');
            if (! $attrName) {
                break;
            }

            $type = $this->choice('Attribute type', CategoryAttributeType::values());
            $unit = $this->ask('Attribute unit');

            $attributes[] = ['name' => $attrName, 'type' => $type, 'unit' => $unit];
        }

        /**
         * @var array{name: string, type: string, unit: string}[] $attributes
         */
        $handler->handle(new CreateCategory(CategoryId::generate()->value, $name, $attributes));

        $this->info("✅ Category '$name' created successfully!");
    }
}
