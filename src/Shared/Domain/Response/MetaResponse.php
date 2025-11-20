<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Response;

final readonly class MetaResponse
{
    public function __construct(
        public ?string $nextCursor,
        public ?string $previousCursor,
        public int $perPage,
        public int $count,
        public int $total
    ) {}

    /**
     * @return mixed[]
     */
    public function toResponse(): array
    {
        return [
            'next_cursor' => $this->nextCursor,
            'previous_cursor' => $this->previousCursor,
            'per_page' => $this->perPage,
            'count' => $this->count,
            'total' => $this->total,
        ];
    }
}
