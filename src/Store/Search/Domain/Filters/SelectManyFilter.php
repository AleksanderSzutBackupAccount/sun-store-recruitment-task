<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain\Filters;

final readonly class SelectManyFilter implements Filter
{
    /**
     * @param  list<string>  $values
     */
    public function __construct(public string $field, public array $values) {}

    public function field(): string
    {
        return $this->field;
    }
}
