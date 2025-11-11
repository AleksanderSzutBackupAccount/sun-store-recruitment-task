<?php

namespace Src\Backoffice\Catalog\Domain\Category;

use Src\Shared\Domain\Bus\DomainEvent;

class CategoryCreated implements DomainEvent
{
    public function __construct(public Category $category) {}

}
