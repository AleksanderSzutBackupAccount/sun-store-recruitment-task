<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Src\Shared\Domain\ValueObjects\ValueObjectInterface;

/**
 * @implements CastsAttributes<mixed, mixed>
 */
final readonly class ValueObjectCast implements CastsAttributes
{
    private const INVALID_CLASS_OBJECT = 'Type of cast should be an instance of %s';

    /**
     * @param  class-string<ValueObjectInterface>  $valueObjectClass
     */
    public function __construct(private string $valueObjectClass)
    {
        if (! is_subclass_of($this->valueObjectClass, ValueObjectInterface::class)) {
            throw new InvalidArgumentException(sprintf(self::INVALID_CLASS_OBJECT, $this->valueObjectClass));
        }
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): ?ValueObjectInterface
    {
        if (is_null($value)) {
            return null;
        }

        return new $this->valueObjectClass($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof $this->valueObjectClass) {
            return $value->value();
        }

        return $value;
    }
}
