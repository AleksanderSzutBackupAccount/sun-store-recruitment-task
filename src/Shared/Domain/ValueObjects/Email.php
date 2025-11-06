<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Src\Shared\Domain\Exceptions\InvalidValueObjectException;

readonly class Email extends NotEmptyString
{
    /**
     * @throws InvalidValueObjectException
     */
    public static function fromString(string $email): self
    {
        return new self($email);
    }

    protected function validate(): void
    {
        if (! filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address '.$this->value);
        }
    }
}
