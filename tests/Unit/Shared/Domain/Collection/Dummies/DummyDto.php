<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Collection\Dummies;

readonly class DummyDto
{
    public function __construct(public string $value, public ?string $optionalUnique = null) {}
}
