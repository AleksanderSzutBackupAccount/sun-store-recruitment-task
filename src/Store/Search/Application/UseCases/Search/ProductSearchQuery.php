<?php

declare(strict_types=1);

namespace Src\Store\Search\Application\UseCases\Search;

use Src\Shared\Application\Bus\Query\CacheableQueryInterface;
use Src\Store\Search\Domain\Response\ProductSearchPaginatedResponse;
use Src\Store\Search\Domain\SearchProductsDto;

/**
 * @implements CacheableQueryInterface<ProductSearchPaginatedResponse>
 */
final readonly class ProductSearchQuery implements CacheableQueryInterface
{
    public function __construct(public SearchProductsDto $dto) {}

    public function cacheKey(): string
    {
        return 'product_search_'.$this->dto->getHash();
    }

    public function ttl(): int
    {
        return 300;
    }
}
