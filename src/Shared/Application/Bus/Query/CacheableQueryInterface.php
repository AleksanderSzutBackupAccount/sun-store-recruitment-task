<?php

declare(strict_types=1);

namespace Src\Shared\Application\Bus\Query;

/**
 * @template-covariant  TResponse
 *
 * @extends QueryInterface<TResponse>
 */
interface CacheableQueryInterface extends QueryInterface
{
    public function cacheKey(): string;

    public function ttl(): int;
}
