<?php

declare(strict_types=1);

namespace Src\Store\Search\Application\UseCases\Get;

use Src\Shared\Application\Bus\Query\CacheableQueryInterface;
use Src\Store\Search\Domain\Response\ProductResponse;

/**
 * @implements CacheableQueryInterface<ProductResponse>
 */
final readonly class ProductGetQuery implements CacheableQueryInterface
{
    public function __construct(public string $id) {}

    public function cacheKey(): string
    {
        return 'product_'.$this->id;
    }

    public function ttl(): int
    {
        return 300;
    }
}
