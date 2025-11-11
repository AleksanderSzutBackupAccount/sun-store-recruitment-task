<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Category;

final readonly class CategoryAttribute
{
    public function __construct(
        public CategoryAttributeId $id,
        public string $name,
        public CategoryAttributeType $type,
        public string $unit,
    ) {}

    public static function create(CategoryAttributeId $id, string $name, CategoryAttributeType $type, string $unit): self
    {
        return new CategoryAttribute(
            $id,
            $name,
            $type,
            $unit
        );
    }
}
