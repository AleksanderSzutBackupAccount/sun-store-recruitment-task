<?php

declare(strict_types=1);

namespace Src\Shared\Application\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Src\Backoffice\Catalog\Domain\Product\ProductCreated;
use Src\Shared\Application\Listeners\PublishProductCreatedMessageEvent;
use Src\Store\Search\Application\Listeners\IndexProductOnCreated;
use Src\Store\Search\Integration\ProductCreatedMessage;

class EventSharedServiceProvider extends EventServiceProvider
{
    protected $listen = [
        ProductCreated::class => [
            PublishProductCreatedMessageEvent::class,
        ],

        ProductCreatedMessage::class => [
            IndexProductOnCreated::class,
        ],
    ];
}
