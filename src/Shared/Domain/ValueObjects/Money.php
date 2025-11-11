<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

final readonly class Money
{
    public function __construct(public float $amount) {}
}
