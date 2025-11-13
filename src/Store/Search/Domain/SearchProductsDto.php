<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain;

final readonly class SearchProductsDto
{
    public function __construct(
        public ?string $query = null,
        public ?string $category = null,
        public ?string $sortBy = 'id',
        public ?string $sortOrder = 'asc',
        public ?string $cursor = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public array $filters = [],
        public int $perPage = 15
    ) {}
}
