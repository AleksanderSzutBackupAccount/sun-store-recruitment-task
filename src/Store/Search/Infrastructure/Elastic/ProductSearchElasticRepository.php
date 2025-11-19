<?php

namespace Src\Store\Search\Infrastructure\Elastic;

use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryAttributeEloquentModel;
use Src\Shared\Infrastructure\Elastic\ElasticClient;
use Src\Store\Search\Domain\ProductSearchRepository;
use Src\Store\Search\Domain\SearchProductsDto;

final class ProductSearchElasticRepository implements ProductSearchRepository
{
    private const INDEX = 'products';

    public function __construct(private ElasticClient $client) {}

    public function search(SearchProductsDto $dto): array
    {
        $query = $this->buildQuery($dto);
        $response = $this->client->search(self::INDEX, $query);

        return $this->formatResponse($response, $dto);
    }

    private function buildQuery(SearchProductsDto $dto): array
    {
        $must = [];

        if ($dto->search) {
            $must[] = [
                'multi_match' => [
                    'query' => $dto->search,
                    'fields' => ['name^3'],
                    'fuzziness' => 'AUTO',
                ],
            ];
        }

        if ($dto->category) {
            $must[] = ['term' => ['category' => $dto->category]];
        }

        if ($dto->minPrice || $dto->maxPrice) {
            $must[] = [
                'range' => [
                    'price' => array_filter([
                        'gte' => $dto->minPrice,
                        'lte' => $dto->maxPrice,
                    ]),
                ],
            ];
        }

        foreach ($dto->filters as $key => $value) {
            $must[] = $this->buildFilterClause($key, $value);
        }

        $aggs = $this->buildAggregations();

        $query = [
            'query' => ['bool' => ['must' => $must]],
            'size' => $dto->perPage,
            'sort' => [
                [$dto->sortBy => ['order' => $dto->sortOrder]],
                ['id' => ['order' => $dto->sortOrder]],
            ],
            'aggs' => $aggs,
        ];

        if ($dto->cursor) {
            $query['search_after'] = json_decode(base64_decode($dto->cursor), true);
        }

        return $query;
    }

    private function buildFilterClause(string $key, mixed $value): array
    {
        if (is_array($value) && count($value) === 2 && is_numeric($value[0]) && is_numeric($value[1])) {
            return [
                'range' => [
                    $key => [
                        'gte' => $value[0],
                        'lte' => $value[1],
                    ],
                ],
            ];
        }

        if (is_array($value)) {
            return ['terms' => [$key => $value]];
        }

        return ['term' => [$key => $value]];
    }

    private function buildAggregations(): array
    {
        $aggs = [
            'category' => ['terms' => ['field' => 'category']],
            'manufacturer' => ['terms' => ['field' => 'manufacturer']],
            'price_stats' => ['stats' => ['field' => 'price']],
        ];

        foreach (CategoryAttributeEloquentModel::all() as $attr) {
            $field = 'attr_'.$attr->name;

            $aggs[$field] = $attr->type->isNumber()
                ? ['stats' => ['field' => $field]]
                : ['terms' => ['field' => $field]];
        }

        return $aggs;
    }

    private function formatResponse(array $response, SearchProductsDto $dto): array
    {
        $hits = $response['hits']['hits'] ?? [];
        $aggs = $response['aggregations'] ?? [];

        $next = null;
        $prev = null;

        if (! empty($hits)) {
            $last = $hits[array_key_last($hits)]['sort'] ?? null;
            $first = $hits[array_key_first($hits)]['sort'] ?? null;

            $next = $last ? base64_encode(json_encode($last)) : null;
            $prev = $first ? base64_encode(json_encode($first)) : null;
        }

        return [
            'data' => array_map(fn ($h) => $h['_source'], $hits),
            'filters' => $this->formatFilters($aggs),
            'meta' => [
                'next_cursor' => $next,
                'previous_cursor' => $prev,
                'per_page' => $dto->perPage,
                'count' => count($hits),
                'total' => $response['hits']['total']['value'] ?? null,
            ],
        ];
    }

    private function formatFilters(array $aggs): array
    {
        $filters = [
            'category' => [
                'ui' => 'select',
                'values' => array_column($aggs['category']['buckets'] ?? [], 'key'),
            ],
            'manufacturer' => [
                'ui' => 'select_many',
                'values' => array_column($aggs['manufacturer']['buckets'] ?? [], 'key'),
            ],
            'price' => [
                'ui' => 'range',
                'unit' => 'zł',
                'min' => $aggs['price_stats']['min'] ?? null,
                'max' => $aggs['price_stats']['max'] ?? null,
            ],
        ];

        foreach (CategoryAttributeEloquentModel::all() as $attr) {
            $field = 'attr_'.$attr->name;
            $agg = $aggs[$field] ?? null;

            if (! $agg) {
                continue;
            }

            $filters[$field] = $attr->type->isNumber()
                ? [
                    'type' => 'number',
                    'ui' => 'range',
                    'unit' => $attr->unit,
                    'min' => $agg['min'] ?? null,
                    'max' => $agg['max'] ?? null,
                ]
                : [
                    'type' => 'string',
                    'ui' => 'select_many',
                    'values' => array_column($agg['buckets'] ?? [], 'key'),
                ];
        }

        return $filters;
    }

    public function getFilters(): array
    {
        return $this->formatFilters(
            $this->client->search(self::INDEX, [
                'size' => 0,
                'aggs' => $this->buildAggregations(),
            ])['aggregations'] ?? []
        );
    }
}
