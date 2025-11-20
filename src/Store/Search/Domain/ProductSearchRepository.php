<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain;

use Src\Shared\Domain\Response\Filters\FilterDefinitionList;
use Src\Store\Search\Domain\Response\ProductSearchPaginatedResponse;

interface ProductSearchRepository
{
    public function search(SearchProductsDto $dto): ProductSearchPaginatedResponse;

    public function getFilters(): FilterDefinitionList;
}
