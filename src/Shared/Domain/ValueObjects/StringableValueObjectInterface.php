<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

use Stringable;

interface StringableValueObjectInterface extends Stringable
{
    public function __toString(): string;

    public function value(): mixed;
}
