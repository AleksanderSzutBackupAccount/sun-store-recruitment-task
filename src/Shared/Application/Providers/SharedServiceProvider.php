<?php

declare(strict_types=1);

namespace Src\Shared\Application\Providers;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Src\Shared\Application\Bus\CommandHandler;
use Src\Shared\Application\Bus\CommandHandlerInterface;
use Src\Shared\Application\Bus\Query\Middleware\CacheMiddleware;
use Src\Shared\Application\Bus\Query\QueryBus;
use Src\Shared\Application\Bus\Query\QueryBusInterface;
use Src\Shared\Domain\Bus\EventBusInterface;
use Src\Shared\Infrastructure\Bus\EventBusLaravel;
use Src\Shared\Infrastructure\Providers\BaseContextServiceProvider;

final class SharedServiceProvider extends BaseContextServiceProvider
{
    protected array $binds = [
        EventBusInterface::class => EventBusLaravel::class,
        CommandHandlerInterface::class => CommandHandler::class,
    ];

    public function register(): void
    {
        $this->app->singleton(QueryBusInterface::class, function ($app) {
            /** @var Application $app */
            return new QueryBus(
                $app->make(Dispatcher::class),
                middleware: [
                    $app->make(CacheMiddleware::class),
                ]
            );
        });
        parent::register();
    }

    protected array $providers = [EventSharedServiceProvider::class];
}
