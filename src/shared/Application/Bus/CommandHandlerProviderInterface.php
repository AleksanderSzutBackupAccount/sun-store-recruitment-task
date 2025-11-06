<?php

declare(strict_types=1);

namespace Src\Shared\Application\Bus;

interface CommandHandlerProviderInterface
{
    /**
     * @return array<string, string>
     */
    public function getCommandsMap(): array;
}
