<?php

declare(strict_types=1);

namespace Src\Shared\Application\Bus\Query\Middleware;

use Illuminate\Support\Facades\Cache;
use Src\Shared\Application\Bus\Query\CacheableQueryInterface;
use Src\Shared\Application\Bus\Query\QueryInterface;

class CacheMiddleware implements QueryMiddleware
{
    /**
     * @template R
     *
     * @param  QueryInterface<R>  $query
     * @param  callable(QueryInterface<R>):R  $next
     * @return R
     */
    public function __invoke(QueryInterface $query, callable $next)
    {
        if (! $query instanceof CacheableQueryInterface) {
            return $next($query);
        }

        $key = $query->cacheKey();
        $ttl = $query->ttl();

        /** @var R $value */
        $value = Cache::tags(['query'])->remember(
            $key,
            $ttl,
            fn () => $next($query)
        );

        return $value;
    }
}
