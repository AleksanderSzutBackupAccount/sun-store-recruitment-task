<?php

declare(strict_types=1);

namespace Src\Store\Search\Application\Providers;

use Src\Shared\Infrastructure\Config\ElasticConfig;
use Src\Shared\Infrastructure\Providers\BaseContextServiceProvider;
use Src\Store\Search\Application\UseCases\Filters\ProductFiltersQuery;
use Src\Store\Search\Application\UseCases\Filters\ProductFiltersQueryHandler;
use Src\Store\Search\Application\UseCases\Get\ProductGetQuery;
use Src\Store\Search\Application\UseCases\Get\ProductGetQueryHandler;
use Src\Store\Search\Application\UseCases\Search\ProductSearchQuery;
use Src\Store\Search\Application\UseCases\Search\ProductSearchQueryHandler;
use Src\Store\Search\Domain\ProductSearchIndexer;
use Src\Store\Search\Domain\ProductSearchRepository;
use Src\Store\Search\Infrastructure\Elastic\ProductSearchElasticIndexer;
use Src\Store\Search\Infrastructure\Elastic\ProductSearchElasticRepository;
use Src\Store\Search\UI\Console\ReindexProductCommand;

class StoreSearchServiceProvider extends BaseContextServiceProvider
{
    public array $binds = [
        ProductSearchIndexer::class => ProductSearchElasticIndexer::class,
        ProductSearchRepository::class => ProductSearchElasticRepository::class,
    ];

    protected array $commands = [
        ReindexProductCommand::class,
    ];

    protected array $providers = [
        EventSearchServiceProvider::class,
    ];

    protected array $useCases = [
        ProductSearchQuery::class => ProductSearchQueryHandler::class,
        ProductGetQuery::class => ProductGetQueryHandler::class,
        ProductFiltersQuery::class => ProductFiltersQueryHandler::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(ElasticConfig::class, function ($app) {
            /** @var string[] $hosts */
            $hosts = config('database.elastic.hosts');
            /** @var ?string $apiKey */
            $apiKey = config('database.elastic.api_key');

            return new ElasticConfig($hosts, $apiKey);
        });
    }
}
