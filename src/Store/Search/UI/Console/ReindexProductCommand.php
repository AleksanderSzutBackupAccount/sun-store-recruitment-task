<?php

namespace Src\Store\Search\UI\Console;

use Illuminate\Console\Command;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\ProductAttributeEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\ProductEloquentModel;
use Src\Shared\Domain\ProductId;
use Src\Shared\Infrastructure\Elastic\ElasticClient;
use Src\Store\Search\Domain\Product;
use Src\Store\Search\Domain\ProductSearchIndexer;

class ReindexProductCommand extends Command
{
    protected $signature = 'reindex:product';

    protected $description = 'XS';

    public function handle(ProductSearchIndexer $indexer, ElasticClient $elasticClient): void
    {
        $elasticClient->deleteIndex('products');
        $elasticClient->createIndex('products', [
            'id' => ['type' => 'keyword'],
            'name' => ['type' => 'text'],
            'manufacturer' => ['type' => 'text'],
            'description' => ['type' => 'text'],
            'price' => ['type' => 'integer'],
            'category' => ['type' => 'keyword'],
            'attributes' => ['type' => 'object'],
        ]);

        $products = ProductEloquentModel::all();

        foreach ($products as $product) {
            $mappedAttributes = [];

            foreach ($product->attributes()->get() as $attribute) {
                /** @var ProductAttributeEloquentModel $attribute */
                $mappedAttributes[$attribute->categoryAttribute->name] = $attribute->value;
            }
            /** @var ProductEloquentModel $product */
            $indexer->index(
                new Product(
                    id: new ProductId($product->id),
                    name: $product->name,
                    description: $product->description,
                    manufacture: $product->manufacture,
                    category: $product->category->name->value,
                    price: $product->price,
                    attributes: $mappedAttributes
                )
            );
        }
    }
}
