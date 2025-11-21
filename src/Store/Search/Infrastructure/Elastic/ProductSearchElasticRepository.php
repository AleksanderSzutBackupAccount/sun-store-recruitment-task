<?php

namespace Src\Store\Search\Infrastructure\Elastic;

use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryAttributeEloquentModel;
use Src\Shared\Domain\ProductId;
use Src\Shared\Domain\Response\Filters\FilterDefinitionList;
use Src\Shared\Domain\Response\Filters\RangeFilterDefinition;
use Src\Shared\Domain\Response\Filters\SelectFilterDefinition;
use Src\Shared\Domain\Response\Filters\SelectManyFilterDefinition;
use Src\Shared\Domain\Response\MetaResponse;
use Src\Shared\Infrastructure\Elastic\ElasticClient;
use Src\Store\Search\Domain\Filters\Filter;
use Src\Store\Search\Domain\Filters\RangeFilter;
use Src\Store\Search\Domain\Filters\SelectFilter;
use Src\Store\Search\Domain\Filters\SelectManyFilter;
use Src\Store\Search\Domain\ProductSearchRepository;
use Src\Store\Search\Domain\Response\ProductResponse;
use Src\Store\Search\Domain\Response\ProductSearchPaginatedResponse;
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

    public function search(SearchProductsDto $dto): ProductSearchPaginatedResponse
    {
        $response = $this->callElastic($this->buildQuery($dto));

        return $this->formatResponse($response, $dto);
    }

    public function getFilters(): FilterDefinitionList
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

    public function get(ProductId $id): ?ProductResponse
    {
        $query = [
            'query' => [
                'term' => [
                    'id' => (string) $id,
                ],
            ],
            'size' => 1,
        ];

        $response = $this->callElastic($query);

        $hit = $response['hits']['hits'][0]['_source'] ?? null;

        return $hit ? ProductResponse::fromArray($hit) : null;
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
     */
    private function formatResponse(array $response, SearchProductsDto $dto): ProductSearchPaginatedResponse
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

        /** @var ProductResponse[] $items */
        $items = array_map(static fn ($h) => ProductResponse::fromArray($h['_source']), $hits);
        $meta = new MetaResponse(
            nextCursor: $next,
            previousCursor: $prev,
            perPage: $dto->perPage,
            count: count($hits),
            total: $response['hits']['total']['value'],
        );

        return new ProductSearchPaginatedResponse(
            meta: $meta,
            data: $items,
            filters: $this->formatFilters($aggs)
        );
    }

    /**
     * @param  ElasticsearchAggs  $aggs
     */
    private function formatFilters(array $aggs): FilterDefinitionList
    {

        $filters = [
            'category' => new SelectFilterDefinition(array_column($aggs['category']['buckets'] ?? [], 'key')),
            'manufacturer' => new SelectManyFilterDefinition(array_column($aggs['manufacturer']['buckets'] ?? [], 'key')),
            'price' => new RangeFilterDefinition(
                'zł',
                $aggs['price_stats']['min'] ?? null,
                $aggs['price_stats']['max'] ?? null
            ),
        ];

        foreach (CategoryAttributeEloquentModel::all() as $attr) {
            $field = 'attr_'.$attr->name;
            $agg = $aggs[$field] ?? null;

            if (! $agg) {
                continue;
            }

            $filters[$field] = $attr->type->isNumber()
                ? new RangeFilterDefinition(
                    $attr->unit,
                    $agg['min'] ?? null,
                    $agg['max'] ?? null
                )
                : new SelectManyFilterDefinition(array_column($agg['buckets'] ?? [], 'key'));
        }

        return new FilterDefinitionList($filters);
    }

    private function generateCursor(mixed $first): ?string
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
