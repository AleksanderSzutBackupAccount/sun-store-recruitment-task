<?php

namespace Src\Shared\Infrastructure\Elastic;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Src\Shared\Infrastructure\Config\ElasticConfig;

class ElasticClient
{
    private Client $client;

    /**
     * @throws AuthenticationException
     */
    public function __construct(ElasticConfig $config)
    {
        $this->client = ClientBuilder::create()
            ->setHosts($config->hosts)
            ->build();
    }

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

    public function search(string $index, array $query)
    {
        return $this->client->search([
            'index' => $index,
            'body' => $query,
        ]);
    }

    /**
     * Update a document by ID.
     */
    public function update(string $index, string $id, array $body): array
    {
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
     */
    public function reindex(string $sourceIndex, string $destinationIndex): array
    {
        $response = $this->client->reindex([
            'body' => [
                'source' => ['index' => $sourceIndex],
                'dest' => ['index' => $destinationIndex],
            ],
            'refresh' => true,
        ]);

        return $response->asArray();
    }

    public function index(string $index, string $id, array $body)
    {
        return $this->client->index([
            'index' => $index,
            'id' => $id,
            'body' => $body,
        ]);
    }
}
