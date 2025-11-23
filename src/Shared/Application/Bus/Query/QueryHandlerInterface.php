<?php

declare(strict_types=1);

namespace Src\Shared\Application\Bus\Query;

/**
 * @template Q of QueryInterface
 * @template R
 */
interface QueryHandlerInterface
{
    /**
     * @param  Q  $query
     * @return R
     */
    public function __invoke(QueryInterface $query): mixed;
}
