<?php

declare(strict_types=1);

namespace Src\Store\Search\Application\UseCases\Filters;

use Src\Shared\Application\Bus\Query\QueryHandlerInterface;
use Src\Shared\Application\Bus\Query\QueryInterface;
use Src\Shared\Domain\Response\Filters\FilterDefinitionList;
use Src\Store\Search\Domain\ProductSearchRepository;

/**
 * @implements QueryHandlerInterface<ProductFiltersQuery, FilterDefinitionList>
 */
final readonly class ProductFiltersQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ProductSearchRepository $repository) {}

    public function __invoke(QueryInterface $query): mixed
    {
        return $this->repository->getFilters();
    }
}
