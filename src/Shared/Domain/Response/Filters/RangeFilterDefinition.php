<?php

namespace Src\Shared\Domain\Response\Filters;

readonly class RangeFilterDefinition implements FilterDefinition
{
    public function __construct(
        private ?string $unit,
        private null|int|float $min,
        private null|int|float $max,
    ) {}

    public function toResponse(): array
    {
        return [
            'ui' => 'range',
            'unit' => $this->unit,
            'min' => $this->min,
            'max' => $this->max,
        ];
    }
}
