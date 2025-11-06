<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Collection\Dummies;

final readonly class DummyItem
{
    public function __construct(public int $value = 0) {}
}
