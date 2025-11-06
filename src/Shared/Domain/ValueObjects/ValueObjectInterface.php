<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

interface ValueObjectInterface
{
    public function value(): mixed;
}
