<?php

namespace Src\Shared\Domain\Response;

use Src\Shared\Domain\Response\Filters\FilterDefinitionList;

/**
 * @template ItemType as ResponseItem
 */
abstract readonly class AbstractPaginatedResponse
{
    /**
     * @param  ItemType[]  $data
     */
    public function __construct(
        public MetaResponse $meta,
        public array $data,
        public FilterDefinitionList $filters,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toResponse(): array
    {
        return [
            'meta' => $this->meta->toResponse(),
            'filters' => $this->filters->toResponse(),
            'data' => array_map(fn ($item) => $item->toResponse(), $this->data),
        ];
    }
}
