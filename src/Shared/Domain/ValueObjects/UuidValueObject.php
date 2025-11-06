<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Src\Shared\Domain\ComparableInterface;
use Stringable;

abstract readonly class UuidValueObject implements ComparableInterface, Stringable, ValueObjectInterface
{
    final public function __construct(public string $value)
    {
        $this->validate();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function validate(): void
    {
        if (! Uuid::isValid($this->value)) {
            throw new InvalidArgumentException('Invalid UUID');
        }
    }

    final public static function random(): self
    {
        return new static(Uuid::uuid4()->toString());
    }

    /**
     * @param  self  $compare
     */
    public function equals(ComparableInterface $compare): bool
    {
        return $this->value === $compare->value;
    }
}
