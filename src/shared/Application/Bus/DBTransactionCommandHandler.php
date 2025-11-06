<?php

declare(strict_types=1);

namespace Src\Shared\Application\Bus;

use Illuminate\Contracts\Bus\Dispatcher;
use Src\Shared\Domain\Bus\CommandInterface;

readonly class DBTransactionCommandHandler implements DBTransactionCommandHandlerInterface
{
    public function __construct(private Dispatcher $dispatcher)
    {
    }

    public function handle(CommandInterface $commands): void
    {
        $this->dispatcher
            ->pipeThrough([
                UseDatabaseTransactions::class,
            ])
            ->dispatch($commands);
    }
}
