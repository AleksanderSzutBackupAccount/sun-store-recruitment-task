<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain;

interface ProductSearchRepository
{
    public function search(SearchProductsDto $dto);

    public function getFilters();
}
