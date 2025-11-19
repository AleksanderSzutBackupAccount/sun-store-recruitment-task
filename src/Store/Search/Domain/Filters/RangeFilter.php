<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain\Filters;

final readonly class RangeFilter implements Filter
{
    public function __construct(public string $field, public float|int $min, public float|int $max)
    {
        if ($min > $max) {
            throw new \DomainException('Range cannot be greater than max');
        }
    }

    public function field(): string
    {
        return $this->field;
    }
}
