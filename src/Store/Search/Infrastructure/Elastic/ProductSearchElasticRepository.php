<?php

namespace Src\Store\Search\Infrastructure\Elastic;

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

        if ($dto->query) {
            $must[] = [
                'multi_match' => [
                    'query' => $dto->query,
                    'fields' => ['name', 'description', 'manufacturer'],
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
            $must[] = ['term' => ["$key.keyword" => $value]];
        }

        $query = [
            'query' => ['bool' => ['must' => $must]],
            'size' => $dto->perPage,
            'sort' => [
                [$dto->sortBy => ['order' => $dto->sortOrder]],
                ['id' => ['order' => $dto->sortOrder]],
            ],
        ];

        if ($dto->cursor) {
            $query['search_after'] = $dto->cursor;
        }

        $response = $this->client->search(self::ELASTIC_PRODUCT_INDEX, $query);

        $hits = $response['hits']['hits'] ?? [];

        $nextCursor = null;
        if (count($hits) > 0) {
            $lastSort = end($hits)['_sort'] ?? null;
            if ($lastSort) {
                $nextCursor = $lastSort;
            }
        }

        return [
            'data' => array_map(static fn ($hit) => $hit['_source'], $hits),
            'nextCursor' => $nextCursor,
        ];
    }
}
