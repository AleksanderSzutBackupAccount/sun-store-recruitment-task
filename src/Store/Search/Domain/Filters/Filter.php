<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain\Filters;

interface Filter
{
    public function field(): string;
}
