<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain\Filters;

use Src\Shared\Domain\Collection\Collection;

/**
 * @extends Collection<Filter>
 */
class Filters extends Collection
{
    protected function type(): string
    {
        return Filter::class;
    }

    /**
     * @param  array<string, string|mixed[]>  $data
     */
    public static function fromArray(array $data): Filters
    {
        $filter = self::new();

        foreach ($data as $field => $value) {
            if (! is_array($value)) {
                $filter->push(new SelectFilter($field, (string) $value));

                continue;
            }
            if (isset($value[0], $value[1]) && is_numeric($value[0]) && is_numeric($value[1])) {
                /** @var array{0:int|float,1:int|float} $value */
                $filter->push(new RangeFilter($field, $value[0], $value[1]));

                continue;
            }
            if (array_is_list($value)) {
                /** @var list<string> $value */
                $filter->push(new SelectManyFilter($field, $value));
            }
        }

        return $filter;
    }
}
