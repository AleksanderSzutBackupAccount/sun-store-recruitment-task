<?php

namespace Src\Shared\Application\Providers;

use Src\Backoffice\Catalog\Domain\Product\ProductCreated;
use Src\Shared\Application\Listeners\PublishProductCreatedMessageEvent;

class EventSharedServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
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
