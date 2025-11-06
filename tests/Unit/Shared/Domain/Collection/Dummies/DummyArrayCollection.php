<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Collection\Dummies;

use Src\Shared\Domain\Collection\Collection;

/**
 * @extends Collection<DummyItemArray>
 */
final class DummyArrayCollection extends Collection
{
    protected function type(): string
    {
        return DummyItemArray::class;
    }
}
