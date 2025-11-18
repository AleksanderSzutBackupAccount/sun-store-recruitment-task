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
        try {

            $elasticClient->deleteIndex('products');

        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
        }

        $attributeMappings = [];
        $attributes = ProductAttributeEloquentModel::with('categoryAttribute')->get();

        foreach ($attributes as $attribute) {
            $key = 'attr_' . $attribute->categoryAttribute->name;

            if ($attribute->categoryAttribute->type->isNumber()) {
                $attributeMappings[$key] = ['type' => 'float'];
            } else {
                $attributeMappings[$key] = [
                    'type' => 'keyword',
                    'fields' => [
                        'text' => ['type' => 'text']
                    ]
                ];
            }
        }

        $elasticClient->createIndex('products', array_merge([
            'id' => ['type' => 'keyword'],
            'name' => [
                'type' => 'text',
                'analyzer' => 'autocomplete_analyzer',
                'search_analyzer' => 'autocomplete_search_analyzer',
                'fields' => [
                    'keyword' => ['type' => 'keyword', 'ignore_above' => 256],
                ],
            ],
            'manufacturer' => ['type' => 'keyword'],
            'description' => ['type' => 'text'],
            'price' => ['type' => 'integer'],
            'category' => ['type' => 'keyword'],
            'created_at' => [
                'type' => 'date',
                'format' => 'strict_date_time',
            ],
        ], $attributeMappings), [
            'analysis' => [
                'filter' => [
                    'my_word_delimiter' => [
                        'type' => 'word_delimiter_graph',
                        'split_on_case_change' => true,
                        'generate_word_parts' => true,
                        'generate_number_parts' => true,
                        'catenate_all' => true,
                    ],
                ],
                'analyzer' => [
                    'my_custom_analyzer' => [
                        'tokenizer' => 'standard',
                        'filter' => [
                            'lowercase',
                            'my_word_delimiter',
                        ],
                    ],
                    'autocomplete_analyzer' => [
                        'tokenizer' => 'autocomplete_tokenizer',
                        'filter' => ['lowercase'],
                    ],
                    'autocomplete_search_analyzer' => [
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase'],
                    ],
                ],
                'tokenizer' => [
                    'autocomplete_tokenizer' => [
                        'type' => 'edge_ngram',
                        'min_gram' => 2,
                        'max_gram' => 20,
                        'token_chars' => ['letter', 'digit'],
                    ],
                ],
            ],
        ]);

        $products = ProductEloquentModel::all();

        foreach ($products as $product) {
            $flatAttributes = [];

            foreach ($product->attributes()->get() as $attribute) {
                $val = $attribute->value;
                $key = 'attr_' . $attribute->categoryAttribute->name;
                if (is_numeric($val)) {
                    $flatAttributes[$key] = (float) $val;
                } else {
                    $flatAttributes[$key] = $val;
                }
            }

            /** @var ProductEloquentModel $product */
            $indexer->index(
                new Product(
                    id: new ProductId($product->id),
                    name: $product->name,
                    description: $product->description,
                    manufacturer: $product->manufacturer,
                    category: $product->category->name->value,
                    price: $product->price,
                    attributes: $flatAttributes,
                    createdAt: $product->created_at
                )
            );
        }
    }
}
