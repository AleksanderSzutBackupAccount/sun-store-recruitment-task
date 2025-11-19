<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain\Filters;

final readonly class SelectFilter implements Filter
{
    public function __construct(public string $field, public string $value) {}

    public function field(): string
    {
        return $this->field;
    }
}
