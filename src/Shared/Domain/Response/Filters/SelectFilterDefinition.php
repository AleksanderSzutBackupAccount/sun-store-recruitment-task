<?php

namespace Src\Shared\Domain\Response\Filters;

readonly class SelectFilterDefinition implements FilterDefinition
{
    /**
     * @param  mixed[]  $values
     */
    public function __construct(
        private array $values,
    ) {}

    public function toResponse(): array
    {
        return [
            'ui' => 'select',
            'values' => $this->values,
        ];
    }
}
