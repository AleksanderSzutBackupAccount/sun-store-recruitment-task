<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain;

use Src\Store\Search\Domain\Filters\Filters;

final readonly class SearchProductsDto
{
    public function __construct(
        public ?string $search,
        public ?string $category,
        public string $sortBy,
        public string $sortOrder,
        public ?string $cursor,
        public Filters $filters,
        public int $perPage = 15
    ) {}

    public function getHash(): string
    {
        return md5(json_encode($this, JSON_THROW_ON_ERROR));
    }
}
