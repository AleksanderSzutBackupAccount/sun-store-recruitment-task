<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\EnumToArray;

enum UnitType: string
{
    /**
     * @use EnumToArray<string>
     */
    use EnumToArray;

    case INT = 'int';
    case STRING = 'string';
    case FLOAT = 'float';
    case BOOL = 'bool';
}
