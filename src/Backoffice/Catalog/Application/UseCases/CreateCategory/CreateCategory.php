<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Application\UseCases\CreateCategory;

use Src\Shared\Domain\Bus\CommandInterface;

readonly class CreateCategory implements CommandInterface
{
    /**
     * @param  array{name: string, type: string, unit: string}[]  $attributes
     */
    public function __construct(
        public string $id,
        public string $name,
        public array $attributes,
    ) {}
}
