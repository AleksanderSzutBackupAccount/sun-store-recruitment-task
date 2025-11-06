<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use Src\Shared\Domain\ValueObjects\ValueObjectInterface;

class ValueObjectsCast implements Castable
{
    /**
     * @param  array<class-string<ValueObjectInterface>>  $arguments
     * @return CastsAttributes<mixed, mixed>
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        if (count($arguments) !== 1) {
            throw new InvalidArgumentException('Value mast contain exactly one argument');
        }

        foreach ($arguments as $argument) {
            if (! is_subclass_of($argument, ValueObjectInterface::class)) {
                throw new InvalidArgumentException('Value mast not be an instance of '.ValueObjectInterface::class);
            }
        }

        return new ValueObjectCast($arguments[0]);
    }
}
