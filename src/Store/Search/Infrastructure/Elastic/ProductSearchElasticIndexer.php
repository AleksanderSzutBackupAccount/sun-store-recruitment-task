<?php

declare(strict_types=1);

namespace Src\Store\Search\Infrastructure\Elastic;

use Src\Backoffice\Catalog\Domain\Product\ProductId;
use Src\Shared\Infrastructure\Elastic\ElasticClient;
use Src\Store\Search\Domain\ProductSearchIndexer;

class ProductSearchElasticIndexer implements ProductSearchIndexer
{
    private const string ELASTIC_PRODUCT_INDEX = 'products';

    public function __construct(
        private ElasticClient $elasticClient
    ) {}

    public function index(ProductId $id, array $data)
    {
        return $this->elasticClient->index(self::ELASTIC_PRODUCT_INDEX, $id->value, $data);
    }

    public function update(ProductId $id, array $data)
    {
        return $this->elasticClient->update(self::ELASTIC_PRODUCT_INDEX,
            $id->value, $data);
    }

    public function deleteIndex()
    {
        $this->elasticClient->deleteIndex(self::ELASTIC_PRODUCT_INDEX);
    }

    public function delete(ProductId $id)
    {
        $this->elasticClient->delete(self::ELASTIC_PRODUCT_INDEX, $id->value);
    }
}
