<?php

declare(strict_types=1);

namespace Src\Shared\Application\Bus\Query\Middleware;

use Src\Shared\Application\Bus\Query\QueryInterface;

interface QueryMiddleware
{
    /**
     * @template R
     *
     * @param  QueryInterface<R>  $query
     * @param  callable(QueryInterface<R>):R  $next
     * @return R
     */
    public function __invoke(QueryInterface $query, callable $next);
}
