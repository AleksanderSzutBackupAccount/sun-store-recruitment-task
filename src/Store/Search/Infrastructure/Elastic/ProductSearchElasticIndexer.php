<?php

declare(strict_types=1);

namespace Src\Store\Search\Infrastructure\Elastic;

use Src\Shared\Domain\ProductId;
use Src\Shared\Infrastructure\Elastic\ElasticClient;
use Src\Store\Search\Domain\Product;
use Src\Store\Search\Domain\ProductSearchIndexer;

readonly class ProductSearchElasticIndexer implements ProductSearchIndexer
{
    private const string ELASTIC_PRODUCT_INDEX = 'products';

    public function __construct(
        private ElasticClient $elasticClient
    ) {}

    public function index(Product $product)
    {
        return $this->elasticClient->index(self::ELASTIC_PRODUCT_INDEX, $product->id->value, $product->toIndex());
    }

    public function update(Product $product): void
    {
        $this->elasticClient->update(self::ELASTIC_PRODUCT_INDEX,
            $product->id->value,
            $product->toIndex());
    }

    public function delete(ProductId $id)
    {
        $this->elasticClient->delete(self::ELASTIC_PRODUCT_INDEX, $id->value);
    }
}
