<?php

declare(strict_types=1);

namespace Src\Shared\Application\Bus;

use Illuminate\Support\ServiceProvider;

class CommandHandlerProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(CommandHandlerInterface::class, CommandHandler::class);
        $this->app->bind(DBTransactionCommandHandlerInterface::class, DBTransactionCommandHandler::class);
    }
}
