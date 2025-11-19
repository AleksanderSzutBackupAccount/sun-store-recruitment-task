<?php

namespace Src\Store\Search\Infrastructure\Elastic;

use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryAttributeEloquentModel;
use Src\Shared\Infrastructure\Elastic\ElasticClient;
use Src\Store\Search\Domain\Filters\Filter;
use Src\Store\Search\Domain\Filters\RangeFilter;
use Src\Store\Search\Domain\Filters\SelectFilter;
use Src\Store\Search\Domain\Filters\SelectManyFilter;
use Src\Store\Search\Domain\ProductSearchRepository;
use Src\Store\Search\Domain\SearchProductsDto;

/**
 * @phpstan-type ElasticsearchResponse array{
 *       took: int,
 *       timed_out: bool,
 *       _shards: array{
 *           total: int,
 *           successful: int,
 *           skipped: int,
 *           failed: int
 *       },
 *       hits: array{
 *           total: array{
 *               value: int,
 *               relation: string
 *           },
 *           max_score: float|null,
 *           hits: list<array{
 *               _index: string,
 *               _id: string,
 *               _score: float|null,
 *               _source: array{
 *                   id: string,
 *                   name: string,
 *                   category: string,
 *                   price: int|float,
 *                   description: string,
 *                   attributes: array<string, string|int|float>,
 *                   manufacturer: string,
 *                   created_at: string,
 *                   attr_connector_type?: string,
 *                   attr_capacity?: int|float,
 *                   attr_power_output?: int|float
 *               },
 *               sort?: list<int|string>
 *           }>
 *       },
 *       aggregations?: array<string, mixed>
 *   }
 * @phpstan-type ElasticsearchAggs array{
 *     category?: array{
 *         buckets: list<array{
 *             key: string,
 *             doc_count: int
 *         }>
 *     },
 *     manufacturer?: array{
 *         buckets: list<array{
 *             key: string,
 *             doc_count: int
 *         }>
 *     },
 *     price_stats?: array{
 *         count: int,
 *         min: float|null,
 *         max: float|null,
 *         avg: float|null,
 *         sum: float|null
 *     },
 *     attr_connector_type?: array{
 *         buckets: list<array{
 *             key: string,
 *             doc_count: int
 *         }>
 *     },
 *     attr_capacity?: array{
 *         count: int,
 *         min: float|null,
 *         max: float|null,
 *         avg: float|null,
 *         sum: float|null
 *     },
 *     attr_power_output?: array{
 *         count: int,
 *         min: float|null,
 *         max: float|null,
 *         avg: float|null,
 *         sum: float|null
 *     }
 * }
 * @phpstan-type Product array{
 *     id: string,
 *     name: string,
 *     category: string,
 *     price: int|float,
 *     description: string,
 *     attributes: array<string, string|int|float>,
 *     manufacturer: string,
 *     created_at: string
 * }
 * @phpstan-type UISelectFilter array{
 *     ui: 'select'|'select_many',
 *     values: list<string>
 * }
 * @phpstan-type UIRangeFilter array{
 *     ui: 'range',
 *     unit?: string,
 *     min: float|int|null,
 *     max: float|int|null
 * }
 * @phpstan-type Filters array<string, UISelectFilter|UIRangeFilter>
 * @phpstan-type SearchResponse array{
 *     data: list<Product>,
 *     filters: Filters,
 *     meta: array{
 *         next_cursor: string|null,
 *         previous_cursor: string|null,
 *         per_page: int,
 *         count: int,
 *         total: int|null
 *     }
 * }
 */
final class ProductSearchElasticRepository implements ProductSearchRepository
{
    private const INDEX = 'products';

    public function __construct(private ElasticClient $client) {}

    /**
     * @phpstan-return SearchResponse
     */
    public function search(SearchProductsDto $dto): array
    {
        $response = $this->callElastic($this->buildQuery($dto));

        return $this->formatResponse($response, $dto);
    }

    /**
     * @return array<string, mixed>
     */
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

        foreach ($dto->filters as $filter) {
            $must[] = $this->buildFilterClause($filter);
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

    /**
     * @return array<string, mixed[]>
     */
    private function buildFilterClause(Filter $filter): array
    {
        if ($filter instanceof RangeFilter) {
            return [
                'range' => [
                    $filter->field => [
                        'gte' => $filter->min,
                        'lte' => $filter->max,
                    ],
                ],
            ];
        }

        if ($filter instanceof SelectManyFilter) {
            return ['terms' => [$filter->field => $filter->values]];
        }

        if ($filter instanceof SelectFilter) {
            return ['term' => [$filter->field => $filter->value]];
        }

        throw new \DomainException('Not implemented');
    }

    /**
     * @return array<mixed>[]
     */
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

    /**
     * @param  ElasticsearchResponse  $response
     *
     * @phpstan-return SearchResponse
     */
    private function formatResponse(array $response, SearchProductsDto $dto): array
    {
        $hits = $response['hits']['hits'];

        /** @var ElasticsearchAggs $aggs */
        $aggs = $response['aggregations'] ?? [];

        $next = null;
        $prev = null;

        if (! empty($hits)) {
            $last = $hits[array_key_last($hits)]['sort'] ?? null;
            $first = $hits[array_key_first($hits)]['sort'] ?? null;

            $next = $this->generateCursor($last);
            $prev = $this->generateCursor($first);
        }

        return [
            'data' => array_map(fn ($h) => $h['_source'], $hits),
            'filters' => $this->formatFilters($aggs),
            'meta' => [
                'next_cursor' => $next,
                'previous_cursor' => $prev,
                'per_page' => $dto->perPage,
                'count' => count($hits),
                'total' => $response['hits']['total']['value'],
            ],
        ];
    }

    /**
     * @param  ElasticsearchAggs  $aggs
     *
     * @phpstan-return Filters
     */
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

    /**
     * @phpstan-return Filters
     */
    public function getFilters(): array
    {
        /** @var ElasticsearchAggs $aggrs */
        $aggrs = $this->callElastic([
            'size' => 0,
            'aggs' => $this->buildAggregations(),
        ])['aggregations'] ?? [];

        return $this->formatFilters(
            $aggrs
        );
    }

    public function generateCursor(mixed $first): ?string
    {
        return $first ? base64_encode(json_encode($first, JSON_THROW_ON_ERROR)) : null;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return ElasticsearchResponse
     */
    private function callElastic(array $query): array
    {
        /** @var ElasticsearchResponse $response */
        $response = $this->client->search(self::INDEX, $query);

        return $response;
    }
}
