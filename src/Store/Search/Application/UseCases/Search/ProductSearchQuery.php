<?php

declare(strict_types=1);

namespace Src\Store\Search\Application\UseCases\Search;

use Src\Store\Search\Domain\ProductSearchRepository;
use Src\Store\Search\Domain\Response\ProductSearchPaginatedResponse;
use Src\Store\Search\Domain\SearchProductsDto;

final readonly class ProductSearchQuery
{
    public function __construct(private ProductSearchRepository $repository) {}

    public function handle(SearchProductsDto $dto): ProductSearchPaginatedResponse
    {
        return $this->repository->search($dto);
    }
}
