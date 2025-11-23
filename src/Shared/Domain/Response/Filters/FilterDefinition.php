<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Response\Filters;

interface FilterDefinition
{
    /**
     * @return array<string, mixed>
     */
    public function toResponse(): array;
}
