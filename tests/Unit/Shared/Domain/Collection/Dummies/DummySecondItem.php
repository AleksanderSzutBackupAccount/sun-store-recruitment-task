<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Collection\Dummies;

final readonly class DummySecondItem
{
    public function __construct(public int $value = 0, public string $second = '') {}
}
