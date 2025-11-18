<?php

namespace Src\Store\Search\Infrastructure\Elastic;

use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryAttributeEloquentModel;
use Src\Shared\Infrastructure\Elastic\ElasticClient;
use Src\Store\Search\Domain\ProductSearchRepository;
use Src\Store\Search\Domain\SearchProductsDto;

class ProductSearchElasticRepository implements ProductSearchRepository
{
    private const string ELASTIC_PRODUCT_INDEX = 'products';

    public function __construct(
        private ElasticClient $client,
    ) {}

    public function search(SearchProductsDto $dto)
    {
        $must = [];

        if ($dto->search) {
            $must[] = [
                'multi_match' => [
                    'query' => $dto->search,
                    'fields' => ['name^3'],
                    'fuzziness' => 'AUTO',
                    'operator' => 'or',
                ],
            ];
        }

        if ($dto->category) {
            $must[] = ['term' => ['category' => $dto->category]];
        }

        if ($dto->minPrice || $dto->maxPrice) {
            $range = [];
            if ($dto->minPrice) {
                $range['gte'] = $dto->minPrice;
            }
            if ($dto->maxPrice) {
                $range['lte'] = $dto->maxPrice;
            }
            $must[] = ['range' => ['price' => $range]];
        }

        foreach ($dto->filters as $key => $value) {
            if (is_array($value) && count($value) === 2 && is_numeric($value[0]) && is_numeric($value[1])) {
                $must[] = [
                    'range' => [
                        $key => [
                            'gte' => $value[0],
                            'lte' => $value[1],
                        ],
                    ],
                ];
                continue;
            }

            if (is_array($value)) {
                $should = [];
                foreach ($value as $v) {
                    $should[] = [
                        'term' => [
                            $key => $v,
                        ],
                    ];
                }

                $must[] = [
                    'bool' => [
                        'should' => $should,
                        'minimum_should_match' => 1,
                    ],
                ];

                continue;
            }

            $must[] = [
                'term' => [
                    $key => $value,
                ],
            ];
        }

        $query = [
            'query' => ['bool' => ['must' => $must]],
            'size' => $dto->perPage,
            'sort' => [
                [$dto->sortBy => ['order' => $dto->sortOrder]],
                ['id' => ['order' => $dto->sortOrder]],
            ],
            'aggs' => [
                'category' => [
                    'terms' => ['field' => 'category']
                ],
                'manufacturer' => [
                    'terms' => ['field' => 'manufacturer']
                ],
                'price_stats' => [
                    'stats' => ['field' => 'price']
                ],
            ],
        ];

        $attributeModels = CategoryAttributeEloquentModel::all();
        foreach ($attributeModels as $attr) {
            $field = 'attr_' . $attr->name;
            if($attr->type->isNumber()) {
                $query['aggs'][$field] = [
                    'stats' => ['field' => $field]
                ];
                continue;
            }
            $query['aggs'][$field] = [
                'terms' => ['field' => $field ]
            ];
        }
        $response = $this->client->search(self::ELASTIC_PRODUCT_INDEX, $query);

        $filters =  [
            'category' => [
                'ui' => 'select', 'values'=>array_map(
                    fn($bucket) => $bucket['key'],
                    $response['aggregations']['category']['buckets'] ?? [],
                )],
            'price' => [
                'min' => $response['aggregations']['price_stats']['min'] ?? null,
                'max' => $response['aggregations']['price_stats']['max'] ?? null,
                'ui' => 'range'
            ],
        ];
        if ($dto->cursor) {
            $query['search_after'] = json_decode(base64_decode($dto->cursor), true);
        }

        $response = $this->client->search(self::ELASTIC_PRODUCT_INDEX, $query);

        $hits = $response['hits']['hits'] ?? [];

        $nextCursor = null;
        if (count($hits) > 0) {
            $lastSort = $hits[count($hits) - 1]['sort'] ?? null;
            if ($lastSort) {
                $nextCursor = $lastSort;
            }
        }

        $previousCursor = null;
        if ($dto->cursor && count($hits) > 0) {
            $firstSort = $hits[0]['sort'] ?? null;
            if ($firstSort) {
                $previousCursor = $firstSort;
            }
        }

        foreach ($attributeModels as $attr) {
            $field = 'attr_' . $attr->name;
            if ($attr->type->isNumber()) {
                $stats = $response['aggregations'][$field] ?? null;
                if ($stats) {
                    $filters[$field] = [
                        'type' => 'number',
                        'unit' => $attr->unit,
                        'min' => $stats['min'] ?? null,
                        'max' => $stats['max'] ?? null,
                        'ui' => 'range'
                    ];
                }
                continue;
            }
            $terms = $response['aggregations'][$field]['buckets'] ?? [];
            $filters[$field] = [
                'type' => 'string',
                'values' => array_map(fn($b) => $b['key'], $terms),
                'ui' => 'select'
            ];
        }
        return [
            'data' => array_map(static fn ($hit) => $hit['_source'], $hits),
            'filters' => $filters,
            'meta' => [
                'next_cursor' => $nextCursor ? base64_encode(json_encode($nextCursor)) : null,
                'previous_cursor' => $previousCursor ? base64_encode(json_encode($previousCursor)) : null,
                'per_page' => $dto->perPage,
                'count' => count($hits),
                'total' => $response['hits']['total']['value'] ?? null,
            ],
        ];
    }



    public function getFilters(): array
    {
        $query = [
            'size' => 0,
            'aggs' => [
                'category' => [
                    'terms' => ['field' => 'category']
                ],
                'manufacturer' => [
                    'terms' => ['field' => 'manufacturer']
                ],
                'price_stats' => [
                    'stats' => ['field' => 'price']
                ],
            ],
        ];

        // Dynamic attributes
        $attributeModels = CategoryAttributeEloquentModel::all();

        foreach ($attributeModels as $attr) {
            $field = 'attr_' . $attr->name;

            if ($attr->type->isNumber()) {
                $query['aggs'][$field] = [
                    'stats' => ['field' => $field]
                ];
            } else {
                $query['aggs'][$field] = [
                    'terms' => ['field' => $field ]
                ];
            }
        }

        $response = $this->client->search(self::ELASTIC_PRODUCT_INDEX, $query);

        $filters = [
            'category' => [
                'ui' => 'select',
                'values' => array_map(
                    fn($b) => $b['key'],
                    $response['aggregations']['category']['buckets'] ?? []
                )
            ],
            'manufacturer' => [
                'ui' => 'select_many',
                'values' => array_map(
                    fn($b) => $b['key'],
                    $response['aggregations']['manufacturer']['buckets'] ?? []
                )
            ],
            'price' => [
                'ui' => 'range',
                'unit' => 'zł',
                'min' => $response['aggregations']['price_stats']['min'] ?? null,
                'max' => $response['aggregations']['price_stats']['max'] ?? null,
            ],
        ];

        foreach ($attributeModels as $attr) {
            $field = 'attr_' . $attr->name;

            if ($attr->type->isNumber()) {
                $stats = $response['aggregations'][$field] ?? null;
                $filters[$field] = [
                    'type' => 'number',
                    'ui' => 'range',
                    'unit' => $attr->unit,
                    'min' => $stats['min'] ?? null,
                    'max' => $stats['max'] ?? null,
                ];
                continue;
            }

            $terms = $response['aggregations'][$field]['buckets'] ?? [];
            $filters[$field] = [
                'type' => 'string',
                'ui' => 'select_many',
                'values' => array_map(fn($b) => $b['key'], $terms),
            ];
        }

        return $filters;
    }
}
