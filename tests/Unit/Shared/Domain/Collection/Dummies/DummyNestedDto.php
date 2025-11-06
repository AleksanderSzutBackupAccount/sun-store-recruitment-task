<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Collection\Dummies;

readonly class DummyNestedDto
{
    public function __construct(public DummyDto $value) {}
}
