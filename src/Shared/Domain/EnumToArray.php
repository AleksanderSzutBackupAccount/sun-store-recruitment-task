<?php

declare(strict_types=1);

namespace Src\Shared\Domain;

/**
 * @template T as string|int
 */
trait EnumToArray
{
    /**
     * @return array<string, T>
     */
    public static function toArray(): array
    {
        $arr = [];
        foreach (self::cases() as $case) {
            $arr[$case->name] = $case->value;
        }

        return $arr;
    }

    /**
     * @return string[]
     */
    public static function keys(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * @return T[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function random(): self
    {
        $cases = self::cases();

        return $cases[array_rand($cases)];
    }

    /**
     * @return T
     */
    public static function randomValue(): int|string
    {
        return self::random()->value;
    }
}
