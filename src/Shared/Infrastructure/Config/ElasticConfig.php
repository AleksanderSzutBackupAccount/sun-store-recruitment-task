<?php

namespace Src\Shared\Infrastructure\Config;

final readonly class ElasticConfig
{
    /**
     * @param  string[]  $hosts
     */
    public function __construct(public array $hosts) {}
}
