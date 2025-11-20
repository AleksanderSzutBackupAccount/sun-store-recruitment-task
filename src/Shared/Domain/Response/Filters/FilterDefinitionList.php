<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Response\Filters;

readonly class FilterDefinitionList
{
    /**
     * @param  array<string, FilterDefinition>  $filters
     */
    public function __construct(public array $filters) {}

    /**
     * @return array<string, mixed>
     */
    public function toResponse(): array
    {
        return array_map(static fn (FilterDefinition $definition) => $definition->toResponse(), $this->filters);
    }
}
