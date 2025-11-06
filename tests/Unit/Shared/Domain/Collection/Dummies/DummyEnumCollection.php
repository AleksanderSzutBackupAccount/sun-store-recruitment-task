<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Collection\Dummies;

use Src\Shared\Domain\Collection\Collection;

/**
 * @extends Collection<DummyItem>
 */
final class DummyEnumCollection extends Collection
{
    protected function type(): string
    {
        return DummyEnum::class;
    }
}
