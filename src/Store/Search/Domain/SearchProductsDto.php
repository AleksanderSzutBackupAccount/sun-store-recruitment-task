<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain;

final readonly class SearchProductsDto
{
    public function __construct(
        public ?string $search = null,
        public ?string $category = null,
        public string $sortBy = 'created_at',
        public string $sortOrder = 'asc',
        public ?string $cursor = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public array $filters = [],
        public int $perPage = 15
    ) {}
}
