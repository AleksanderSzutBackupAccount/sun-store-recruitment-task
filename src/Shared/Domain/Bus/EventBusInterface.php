<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Bus;

interface EventBusInterface
{
    public function publish(DomainEvent ...$events): void;
}
