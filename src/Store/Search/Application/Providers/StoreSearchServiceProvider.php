<?php

declare(strict_types=1);

namespace Src\Store\Search\Application\Providers;

use Src\Shared\Infrastructure\Config\ElasticConfig;
use Src\Shared\Infrastructure\Providers\BaseContextServiceProvider;
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
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(ElasticConfig::class, function ($app) {
            $hosts = config('database.elastic.hosts');

            return new ElasticConfig($hosts);
        });
    }
}
