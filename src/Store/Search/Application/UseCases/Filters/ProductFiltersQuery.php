<?php

declare(strict_types=1);

namespace Src\Store\Search\Application\UseCases\Filters;

use Src\Shared\Application\Bus\Query\CacheableQueryInterface;
use Src\Shared\Domain\Response\Filters\FilterDefinitionList;

/**
 * @implements CacheableQueryInterface<FilterDefinitionList>
 */
final readonly class ProductFiltersQuery implements CacheableQueryInterface
{
    public function cacheKey(): string
    {
        return 'product_filters';
    }

    public function ttl(): int
    {
        return 300;
    }
}
