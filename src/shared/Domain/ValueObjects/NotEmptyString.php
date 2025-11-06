<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\Exceptions\InvalidValueObjectException;

readonly class NotEmptyString extends StringValueObject
{
    /**
     * @throws InvalidValueObjectException
     */
    protected function validate(): void
    {
        if (empty($this->value)) {
            throw new InvalidValueObjectException('Value cannot be empty');
        }
    }
}
