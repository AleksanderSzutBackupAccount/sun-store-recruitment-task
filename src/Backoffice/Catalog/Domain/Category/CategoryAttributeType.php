<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Domain\Category;

use Src\Shared\Domain\EnumToArray;

enum CategoryAttributeType: string
{
    /**
     * @use EnumToArray<string>
     */
    use EnumToArray;

    case INT = 'int';
    case STRING = 'string';
    case FLOAT = 'float';
    case BOOL = 'bool';

    public function isInt(): bool
    {
        return $this === self::INT;
    }

    public function isNumber(): bool
    {
        return in_array($this, [self::INT,  self::FLOAT]);
    }

    public function isString(): bool
    {
        return $this === self::STRING;
    }

    public function isFloat(): bool
    {
        return $this === self::FLOAT;
    }

    public function isBool(): bool
    {
        return $this === self::BOOL;
    }
}
