<?php

declare(strict_types=1);

namespace Src\Shared\Application\Bus;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\ServiceProvider;

abstract class BusDispatcherProvider extends ServiceProvider implements CommandHandlerProviderInterface
{
    public function boot(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = app(Dispatcher::class);
        $dispatcher->map($this->getCommandsMap());
    }
}
