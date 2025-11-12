<?php

declare(strict_types=1);

namespace Tests\Feature\Products;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryAttributeEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\CategoryEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\ProductAttributeEloquentModel;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Model\ProductEloquentModel;
use Tests\TestCase;

class ProductIndexControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('must be rewrote to elasticsearch.');

        $solarCategory = CategoryEloquentModel::factory()->create(['name' => 'solar_panels']);
        $batteryCategory = CategoryEloquentModel::factory()->create(['name' => 'batteries']);
        $connectorCategory = CategoryEloquentModel::factory()->create(['name' => 'connectors']);

        $powerOutputAttr = CategoryAttributeEloquentModel::factory()->for($solarCategory, 'category')->create([
            'name' => 'power_output',
            'unit' => 'int',
        ]);

        $capacityAttr = CategoryAttributeEloquentModel::factory()->for($batteryCategory, 'category')->create([
            'name' => 'capacity',
            'unit' => 'int',
        ]);

        $connectorTypeAttr = CategoryAttributeEloquentModel::factory()->for($connectorCategory, 'category')->create([
            'name' => 'connector_type',
            'unit' => 'string',
        ]);

        $solarix = ProductEloquentModel::factory()->create([
            'category_id' => $solarCategory->id,
            'name' => 'Solarix Prime 450',
            'manufacturer' => 'Solarix',
            'price' => 27999, // w groszach
            'description' => 'High-efficiency monocrystalline panel 450W',
        ]);

        $ecocharge = ProductEloquentModel::factory()->create([
            'category_id' => $batteryCategory->id,
            'name' => 'EcoCharge HomeVault 10',
            'manufacturer' => 'EcoCharge',
            'price' => 149900,
            'description' => 'LiFePO4 battery designed for whole-home backup',
        ]);

        $safelock = ProductEloquentModel::factory()->create([
            'category_id' => $connectorCategory->id,
            'name' => 'SafeLock Pro Pair M/F',
            'manufacturer' => 'SafeLock',
            'price' => 799,
            'description' => 'MC4 connector pair waterproof connection',
        ]);

        ProductAttributeEloquentModel::factory()->create([
            'product_id' => $solarix->id,
            'value' => $this->faker->randomNumber(),
            'category_attribute_id' => $powerOutputAttr->id,
        ]);

        ProductAttributeEloquentModel::factory()->create([
            'product_id' => $ecocharge->id,
            'value' => $this->faker->randomNumber(),
            'category_attribute_id' => $capacityAttr->id,
        ]);

        ProductAttributeEloquentModel::factory()->create([
            'product_id' => $safelock->id,
            'value' => $this->faker->randomNumber(),
            'category_attribute_id' => $connectorTypeAttr->id,
        ]);
    }

    /** @test */
    public function it_can_search_products_by_name_or_description(): void
    {
        $response = $this->getJson('/api/products?q=Solarix');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Solarix Prime 450',
                'manufacturer' => 'Solarix',
            ])
            ->assertJsonMissing([
                'name' => 'EcoCharge HomeVault 10',
            ]);
    }

    /** @test */
    public function it_can_filter_products_by_category_and_price_range(): void
    {
        $response = $this->getJson('/api/products?category=batteries&price_min=1000&price_max=200000');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'EcoCharge HomeVault 10',
            ])
            ->assertJsonMissing([
                'name' => 'Solarix Prime 450',
            ]);
    }

    /** @test */
    public function it_can_filter_category_specific_attributes(): void
    {
        $response = $this->getJson('/api/products?category=solar_panels&power_output_min=400&power_output_max=500');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Solarix Prime 450',
            ]);
    }
}
