<?php

declare(strict_types=1);

namespace Src\Store\Search\Application\UseCases\Search;

use Src\Shared\Application\Bus\Query\QueryHandlerInterface;
use Src\Shared\Application\Bus\Query\QueryInterface;
use Src\Store\Search\Domain\ProductSearchRepository;
use Src\Store\Search\Domain\Response\ProductSearchPaginatedResponse;

/**
 * @implements QueryHandlerInterface<ProductSearchQuery, ProductSearchPaginatedResponse>
 */
final readonly class ProductSearchQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ProductSearchRepository $repository) {}

    public function __invoke(QueryInterface $query): mixed
    {
        return $this->repository->search($query->dto);
    }
}
