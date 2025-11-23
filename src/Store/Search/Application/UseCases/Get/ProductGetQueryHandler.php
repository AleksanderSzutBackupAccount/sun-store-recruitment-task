<?php

declare(strict_types=1);

namespace Src\Store\Search\Application\UseCases\Get;

use Src\Shared\Application\Bus\Query\QueryHandlerInterface;
use Src\Shared\Application\Bus\Query\QueryInterface;
use Src\Shared\Domain\ProductId;
use Src\Store\Search\Domain\Exceptions\ProductNotFound;
use Src\Store\Search\Domain\ProductSearchRepository;
use Src\Store\Search\Domain\Response\ProductResponse;

/**
 * @implements QueryHandlerInterface<ProductGetQuery, ProductResponse>
 */
final readonly class ProductGetQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ProductSearchRepository $repository) {}

    public function __invoke(QueryInterface $query): mixed
    {
        $product = $this->repository->get(new ProductId($query->id));

        if (! $product) {
            throw new ProductNotFound;
        }

        return $product;
    }
}
