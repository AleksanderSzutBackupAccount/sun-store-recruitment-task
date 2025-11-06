<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Collection\Dummies;

final readonly class DummyEntity
{
    public function __construct(public int $id, public string $rand) {}
}
