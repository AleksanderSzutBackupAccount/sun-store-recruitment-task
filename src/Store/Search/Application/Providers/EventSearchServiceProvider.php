<?php

declare(strict_types=1);

namespace Src\Store\Search\Application\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Src\Store\Search\Application\Listeners\IndexProductOnCreated;
use Src\Store\Search\Integration\ProductCreatedMessage;

class EventSearchServiceProvider extends EventServiceProvider
{
    protected $listen = [
        ProductCreatedMessage::class => [
            IndexProductOnCreated::class,
        ],
    ];
}
