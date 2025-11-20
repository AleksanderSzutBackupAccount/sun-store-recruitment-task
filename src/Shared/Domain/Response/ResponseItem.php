<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Response;

interface ResponseItem
{
    /**
     * @return array<string, mixed>
     */
    public function toResponse(): array;
}
