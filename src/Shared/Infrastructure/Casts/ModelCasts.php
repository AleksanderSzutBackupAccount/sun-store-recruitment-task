<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Casts;

use Src\Shared\Domain\ValueObjects\ValueObjectInterface;

final readonly class ModelCasts
{
    /**
     * @param  array<string, class-string<ValueObjectInterface>|string>  $casts
     * @return array<string, string>
     */
    public static function make(array $casts): array
    {
        return array_map(function ($cast) {
            if (! is_subclass_of($cast, ValueObjectInterface::class)) {
                return $cast;
            }

            return ValueObjectCast::class.':'.$cast;
        }, $casts);
    }
}
