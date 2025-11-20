<?php

namespace Src\Shared\Domain\Response\Filters;

interface FilterDefinition
{
    /**
     * @return array<string, mixed>
     */
    public function toResponse(): array;
}
