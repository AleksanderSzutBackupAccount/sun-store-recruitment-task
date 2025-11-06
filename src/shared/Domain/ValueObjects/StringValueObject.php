<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\ComparableInterface;
use Stringable;

abstract readonly class StringValueObject implements ComparableInterface, Stringable, ValueObjectInterface
{
    final public function __construct(public string $value)
    {
        $this->validate();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function isEqual(?self $value): bool
    {
        return $this->value === $value?->value;
    }

    public static function fromNullable(?string $value): ?static
    {
        if ($value === null) {
            return null;
        }

        return new static($value);
    }

    public function value(): string
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

    protected function validate(): void {}
}
