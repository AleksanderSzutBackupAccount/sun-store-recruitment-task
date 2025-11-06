<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Collection\Dummies;

final readonly class DummyItemArray
{
    public function __construct(public array $value = []) {}
}
