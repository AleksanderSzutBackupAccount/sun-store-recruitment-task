<?php

declare(strict_types=1);

namespace Src\Shared\Application\Providers;

use Src\Shared\Application\Bus\CommandHandler;
use Src\Shared\Application\Bus\CommandHandlerInterface;
use Src\Shared\Domain\Bus\EventBusInterface;
use Src\Shared\Infrastructure\Bus\EventBusLaravel;
use Src\Shared\Infrastructure\Providers\BaseContextServiceProvider;

final class SharedServiceProvider extends BaseContextServiceProvider
{
    protected array $binds = [
        EventBusInterface::class => EventBusLaravel::class,
        CommandHandlerInterface::class => CommandHandler::class,
    ];

    protected array $providers = [EventSharedServiceProvider::class];
}
