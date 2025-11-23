<?php

declare(strict_types=1);

namespace Src\Shared\Application\Bus\Query;

interface QueryBusInterface
{
    /**
     * @template R
     *
     * @param  QueryInterface<R>  $query
     * @return R
     */
    public function ask(QueryInterface $query): mixed;
}
