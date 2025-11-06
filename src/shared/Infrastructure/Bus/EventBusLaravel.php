<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Bus;

use Src\Shared\Domain\Bus\DomainEvent;
use Src\Shared\Domain\Bus\EventBusInterface;

final readonly class EventBusLaravel implements EventBusInterface
{
    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            event($event);
        }
    }
}
