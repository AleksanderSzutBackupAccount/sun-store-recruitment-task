<?php

namespace Src\Shared\Domain\Response\Filters;

readonly class SelectManyFilterDefinition implements FilterDefinition
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
            'ui' => 'select_many',
            'values' => $this->values,
        ];
    }
}
