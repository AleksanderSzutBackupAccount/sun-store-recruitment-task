<?php

namespace Src\Shared\Infrastructure\Elastic;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Src\Shared\Infrastructure\Config\ElasticConfig;

class ElasticClient
{
    private Client $client;

    /**
     * @throws AuthenticationException
     */
    public function __construct(ElasticConfig $config)
    {
        $builder = ClientBuilder::create()
            ->setHosts($config->hosts);

        if ($config->apiKey) {
            $builder->setApiKey($config->apiKey);
        }

        $this->client = $builder->build();
    }

    /**
     * @param  array<string, mixed>  $properties
     * @param  array<string, mixed>  $settings
     */
    public function createIndex(string $indexName, array $properties, array $settings = []): void
    {
        $body = [
            'mappings' => [
                'properties' => $properties,
            ],
        ];
        if ($settings) {
            $body['settings'] = $settings;
        }
        $this->client->indices()->create([
            'index' => $indexName,
            'body' => $body,
        ]);
    }

    public function deleteIndex(string $indexName): void
    {
        $this->client->indices()->delete([
            'index' => $indexName,
        ]);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return mixed[]
     */
    public function search(string $index, array $query): array
    {
        /** @var Elasticsearch $response */
        $response = $this->client->search([
            'index' => $index,
            'body' => $query,
        ]);

        return $response->asArray();
    }

    /**
     * Update a document by ID.
     *
     * @param  array<string, mixed>  $body
     * @return mixed[]
     */
    public function update(string $index, string $id, array $body): array
    {
        /** @var Elasticsearch $response */
        $response = $this->client->update([
            'index' => $index,
            'id' => $id,
            'body' => [
                'doc' => $body,
            ],
        ]);

        return $response->asArray();
    }

    /**
     * Delete a document by ID.
     */
    public function delete(string $index, string $id): void
    {
        try {
            $this->client->delete([
                'index' => $index,
                'id' => $id,
            ]);
        } catch (ClientResponseException|ServerResponseException $e) {
            // ignore not_found errors — idempotent delete
            if ($e->getCode() !== 404) {
                throw $e;
            }
        }
    }

    /**
     * Reindex from one index to another (useful for schema changes).
     *
     * @return mixed[]
     */
    public function reindex(string $sourceIndex, string $destinationIndex): array
    {
        /** @var Elasticsearch $response */
        $response = $this->client->reindex([
            'body' => [
                'source' => ['index' => $sourceIndex],
                'dest' => ['index' => $destinationIndex],
            ],
            'refresh' => true,
        ]);

        return $response->asArray();
    }

    /**
     * @param  array<string, mixed>  $body
     * @return mixed[]
     */
    public function index(string $index, string $id, array $body): array
    {
        /** @var Elasticsearch $response */
        $response = $this->client->index([
            'index' => $index,
            'id' => $id,
            'body' => $body,
        ]);

        return $response->asArray();
    }
}
