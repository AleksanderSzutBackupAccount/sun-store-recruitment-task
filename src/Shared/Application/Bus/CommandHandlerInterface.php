<?php

declare(strict_types=1);

namespace Src\Shared\Application\Bus;

use Src\Shared\Domain\Bus\CommandInterface;

interface CommandHandlerInterface
{
    public function handle(CommandInterface $command): void;
}
