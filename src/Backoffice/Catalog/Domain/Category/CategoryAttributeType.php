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

}
