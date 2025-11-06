<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Collection\Dummies;

use Src\Shared\Domain\Collection\Collection;

/**
 * @extends Collection<DummySecondItem>
 */
final class DummySecondCollection extends Collection
{
    protected function type(): string
    {
        return DummySecondItem::class;
    }
}
