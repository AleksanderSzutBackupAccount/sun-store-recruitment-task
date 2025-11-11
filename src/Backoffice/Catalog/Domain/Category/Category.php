<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Category;

use Src\Shared\Domain\Aggregate\AggregateRoot;

final class Category extends AggregateRoot
{
    public function __construct(public CategoryId $id, public CategoryName $name, public CategoryAttributes $attributes) {}

    public static function create(CategoryId $id, CategoryName $name, CategoryAttributes $attributes): self
    {
        $entity = new self($id, $name, $attributes);

        $entity->record(new CategoryCreated($entity));

        return $entity;
    }
}
