<?php

declare(strict_types=1);

namespace Feature\Store\Search\UI\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Src\Shared\Infrastructure\Elastic\ElasticClient;
use Tests\TestCase;

class ProductSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        cache()->clear();
    }

    public function test_product_search_endpoint_returns_expected_structure(): void
    {
        $mockElastic = Mockery::mock(ElasticClient::class);

        $mockResponse = [
            'hits' => [
                'total' => ['value' => 1],
                'hits' => [
                    [
                        '_source' => [
                            'id' => '123',
                            'name' => 'Test Product',
                            'price' => 999,
                            'manufacturer' => 'EcoCharge',
                            'category' => 'batteries',
                            'description' => 'desc',
                            'created_at' => '2024-01-01',
                        ],
                        'sort' => [999, '123'],
                    ],
                ],
            ],
            'aggregations' => [
                'manufacturer' => [
                    'buckets' => [
                        ['key' => 'EcoCharge'],
                        ['key' => 'SafeLock'],
                    ],
                ],
                'category' => [
                    'buckets' => [
                        ['key' => 'batteries'],
                        ['key' => 'solar_panels'],
                    ],
                ],
                'price_stats' => [
                    'min' => 100,
                    'max' => 2000,
                ],
            ],
        ];

        $mockElastic->shouldReceive('search')
            ->once()
            ->andReturn($mockResponse);

        $this->app->instance(ElasticClient::class, $mockElastic);

        $response = $this->get('api/search/products?query=test&category=batteries&min_price=100&max_price=2000&sort_by=price&sort_order=asc&per_page=10');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'price',
                    'manufacturer',
                    'category',
                ],
            ],
            'meta' => [
                'next_cursor',
                'previous_cursor',
                'per_page',
                'count',
                'total',
            ],
        ]);

        $this->assertEquals('Test Product', $response->json('data.0.name'));
        $this->assertEquals(999, $response->json('data.0.price'));
    }

    public function test_product_search_returns_filters_and_meta(): void
    {
        $mockElastic = Mockery::mock(ElasticClient::class);

        $mockResponse = [
            'hits' => [
                'total' => ['value' => 2],
                'hits' => [
                    [
                        '_source' => [
                            'id' => '1',
                            'name' => 'Battery A',
                            'price' => 500,
                            'manufacturer' => 'EcoCharge',
                            'category' => 'batteries',
                            'description' => 'desc',
                            'created_at' => '2024-01-01',
                        ],
                        'sort' => [500, '1'],
                    ],
                    [
                        '_source' => [
                            'id' => '2',
                            'name' => 'Battery B',
                            'price' => 1500,
                            'manufacturer' => 'SafeLock',
                            'category' => 'batteries',
                            'description' => 'desc',
                            'created_at' => '2024-01-01',
                        ],
                        'sort' => [1500, '2'],
                    ],
                ],
            ],
            'aggregations' => [
                'manufacturer' => [
                    'buckets' => [
                        ['key' => 'EcoCharge'],
                        ['key' => 'SafeLock'],
                    ],
                ],
                'category' => [
                    'buckets' => [
                        ['key' => 'batteries'],
                    ],
                ],
                'price_stats' => [
                    'min' => 500,
                    'max' => 1500,
                ],
            ],
        ];

        $mockElastic->shouldReceive('search')
            ->once()
            ->andReturn($mockResponse);

        $this->app->instance(ElasticClient::class, $mockElastic);

        $response = $this->get('api/search/products?query=battery&category=batteries&sort_by=price&sort_order=asc&per_page=2');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'price',
                    'manufacturer',
                    'category',
                ],
            ],
            'filters' => [
                'category',
                'manufacturer',
                'price',
            ],
            'meta' => [
                'next_cursor',
                'previous_cursor',
                'per_page',
                'count',
                'total',
            ],
        ]);

        $this->assertEquals('Battery A', $response->json('data.0.name'));
        $this->assertEquals(500, $response->json('data.0.price'));

        $this->assertEquals('Battery B', $response->json('data.1.name'));
        $this->assertEquals(1500, $response->json('data.1.price'));

        $this->assertEquals(['batteries'], $response->json('filters.category.values'));
        $this->assertEquals(['EcoCharge', 'SafeLock'], $response->json('filters.manufacturer.values'));

        $this->assertEquals(500, $response->json('filters.price.min'));
        $this->assertEquals(1500, $response->json('filters.price.max'));

        $this->assertEquals(2, $response->json('meta.per_page'));
        $this->assertEquals(2, $response->json('meta.count'));
        $this->assertEquals(2, $response->json('meta.total'));

        $this->assertNotNull($response->json('meta.next_cursor'));
        $this->assertNotNull($response->json('meta.previous_cursor'));

        $this->assertTrue(
            base64_decode($response->json('meta.next_cursor')) !== false,
            'next_cursor is not valid base64!'
        );
    }

    public function test_product_search_filters_by_dynamic_attributes(): void
    {
        $mockElastic = Mockery::mock(ElasticClient::class);
        $mockResponse = [
            'hits' => [
                'total' => ['value' => 2],
                'hits' => [
                    [
                        '_source' => [
                            'id' => '10',
                            'name' => 'Solar Panel X',
                            'price' => 1200,
                            'description' => 'The HomeVault 10 is a 10kWh LiFePO4 battery designed for whole-home backup.',
                            'manufacturer' => 'EcoCharge',
                            'category' => 'solar_panels',
                            'created_at' => '2024-01-01',
                            'attr_color' => 'red',
                            'attr_capacity' => 4,
                        ],
                        'sort' => [1200, '10'],
                    ],
                    [
                        '_source' => [
                            'id' => '11',
                            'name' => 'Solar Panel Y',
                            'price' => 1500,
                            'manufacturer' => 'EcoCharge',
                            'description' => 'The HomeVault 10 is a 10kWh LiFePO4 battery designed for whole-home backup. It features an advanced Battery Management System (BMS) for optimal safety and a 6,000 cycle life.',
                            'category' => 'solar_panels',
                            'created_at' => '2024-01-01',
                            'attr_color' => 'red',
                            'attr_capacity' => 4,
                        ],
                        'sort' => [1500, '11'],
                    ],
                ],
            ],
            'aggregations' => [
                'manufacturer' => [
                    'buckets' => [
                        ['key' => 'EcoCharge'],
                    ],
                ],
                'category' => [
                    'buckets' => [
                        ['key' => 'solar_panels'],
                    ],
                ],
                'price_stats' => [
                    'min' => 1200,
                    'max' => 1500,
                ],
                'attr_color' => [
                    'buckets' => [
                        ['key' => 'red'],
                        ['key' => 'black'],
                    ],
                ],
                'attr_capacity' => [
                    'buckets' => [
                        ['key' => 4],
                        ['key' => 8],
                    ],
                ],
            ],
        ];

        $mockElastic->shouldReceive('search')
            ->once()
            ->andReturn($mockResponse);

        $this->app->instance(ElasticClient::class, $mockElastic);

        $response = $this->get(
            'api/search/products?'.http_build_query([
                'query' => 'solar',
                'category' => 'solar_panels',
                'sort_by' => 'price',
                'sort_order' => 'asc',
                'per_page' => 10,
                'filters' => [
                    'color' => ['red'],
                    'capacity' => [4],
                ],
            ])
        );
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'price',
                    'manufacturer',
                    'category',
                ],
            ],
            'filters' => [
                'category',
                'manufacturer',
                'price',
            ],
            'meta' => [
                'next_cursor',
                'previous_cursor',
                'per_page',
                'count',
                'total',
            ],
        ]);

        $this->assertEquals('Solar Panel X', $response->json('data.0.name'));
        $this->assertEquals('red', $response->json('data.0.attr_color'));

        // --- PRICE STATS ---
        $this->assertEquals(1200, $response->json('filters.price.min'));
        $this->assertEquals(1500, $response->json('filters.price.max'));

        // --- META ---
        $this->assertEquals(10, $response->json('meta.per_page'));
        $this->assertEquals(2, $response->json('meta.count'));
        $this->assertEquals(2, $response->json('meta.total'));
    }
}
