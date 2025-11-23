<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Src\Shared\Infrastructure\Elastic\ElasticClient;

final readonly class HealthCheckController
{
    public function __construct(
        private ElasticClient $client,
        private DatabaseManager $db,
        private RedisFactory $redis,
    ) {}

    public function __invoke(): JsonResponse
    {
        $errors = [];
        try {
            $this->db->connection()->getPdo();
            $dbStatus = 'ok';
        } catch (\Throwable $e) {
            $errors['mysql'] = $e->getMessage();
            $dbStatus = 'error';
        }
        try {
            $this->redis->connection()->ping();
            $redisStatus = 'ok';
        } catch (\Throwable $e) {
            $errors['redis'] = $e->getMessage();
            $redisStatus = 'error';
        }
        try {
            $this->client->search('products', []);
            $esStatus = 'ok';
        } catch (\Throwable $e) {
            $errors['elasticsearch'] = $e->getMessage();
            $esStatus = 'error';
        }

        return response()->json([
            'status' => 'ok',
            'services' => [
                'database' => $dbStatus,
                'elasticsearch' => $esStatus,
                'redis' => $redisStatus,
            ],
            'timestamp' => now()->toISOString(),
            'app' => config('app.name'),
            'php' => PHP_VERSION,
            'errors' => $errors,
        ]);
    }
}
