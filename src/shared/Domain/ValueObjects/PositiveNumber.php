<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\ComparableInterface;
use Src\Shared\Domain\Exceptions\InvalidValueObjectException;

readonly class PositiveNumber implements ComparableInterface, StringableValueObjectInterface, ValueObjectInterface
{
    /**
     * @throws InvalidValueObjectException
     */
    public function __construct(public int $value)
    {
        if ($this->value <= 0) {
            throw new InvalidValueObjectException('Value must be greater than 0');
        }
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function value(): int
    {
        return $this->value;
    }

    /**
     * @param  self  $compare
     */
    public function equals(ComparableInterface $compare): bool
    {
        return $this->value === $compare->value;
    }

    public function equalsPrimitive(int $value): bool
    {
        return $this->value === $value;
    }
}
