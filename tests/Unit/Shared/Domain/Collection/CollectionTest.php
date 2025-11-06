<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Collection;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tests\Unit\Shared\Domain\Collection\Dummies\DummyCollection;
use Tests\Unit\Shared\Domain\Collection\Dummies\DummyItem;
use Tests\Unit\Shared\Domain\Collection\Dummies\DummySecondCollection;
use Tests\Unit\Shared\Domain\Collection\Dummies\DummySecondItem;

class CollectionTest extends TestCase
{
    public function test_construct_failed_on_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DummyCollection([new stdClass]);
    }

    public function test_count(): void
    {
        $expectedCount = 3;

        $collection = new DummyCollection([
            new DummyItem(0),
            new DummyItem(1),
            new DummyItem(2),
        ]);
        $this->assertEquals($expectedCount, $collection->count());
    }

    public function test_some_return_true(): void
    {
        $valueToFind = 4;

        $collection = new DummyCollection([
            new DummyItem(0),
            new DummyItem(1),
            new DummyItem(2),
            new DummyItem($valueToFind),
        ]);

        $this->assertTrue($collection->some(fn (DummyItem $item) => $item->value === $valueToFind));
    }

    public function test_some_return_false(): void
    {
        $notExistingValueInCollection = 10;

        $collection = new DummyCollection([
            new DummyItem(0),
            new DummyItem(1),
            new DummyItem(2),
            new DummyItem(4),
        ]);

        $this->assertFalse($collection->some(fn (DummyItem $item) => $item->value === $notExistingValueInCollection));
    }

    public function test_every_return_true(): void
    {
        $collection = new DummyCollection([
            new DummyItem(1),
            new DummyItem(2),
            new DummyItem(3),
            new DummyItem(4),
        ]);

        $this->assertTrue($collection->some(fn (DummyItem $item) => $item->value !== 0));
    }

    public function test_every_return_false(): void
    {
        $collection = new DummyCollection([
            new DummyItem(0),
            new DummyItem(1),
            new DummyItem(2),
            new DummyItem(3),
        ]);

        $this->assertTrue($collection->some(fn (DummyItem $item) => $item->value !== 0));
    }

    public function test_is_equal_true(): void
    {
        $sameValue = 2;
        $firstCollection = new DummyCollection([new DummyItem($sameValue), new DummyItem($sameValue + 1)]);
        $secondCollection = new DummyCollection([new DummyItem($sameValue + 1), new DummyItem($sameValue)]);
        $this->assertTrue($firstCollection->isEqual($secondCollection));
    }

    public function test_is_equal_with_order_false(): void
    {
        $sameValue = 2;
        $firstCollection = new DummyCollection([new DummyItem($sameValue), new DummyItem($sameValue + 1)]);
        $secondCollection = new DummyCollection([new DummyItem($sameValue + 1), new DummyItem($sameValue)]);
        $this->assertFalse($firstCollection->isEqualWithOrder($secondCollection));
    }

    public function test_is_equal_with_order_true(): void
    {
        $sameValue = 2;
        $firstCollection = new DummyCollection([new DummyItem($sameValue + 1), new DummyItem($sameValue)]);
        $secondCollection = new DummyCollection([new DummyItem($sameValue + 1), new DummyItem($sameValue)]);
        $this->assertTrue($firstCollection->isEqualWithOrder($secondCollection));
    }

    public function test_is_equal_false(): void
    {
        $firstCollection = new DummyCollection([new DummyItem(5)]);
        $secondCollection = new DummyCollection([new DummyItem(10)]);
        $this->assertFalse($firstCollection->isEqual($secondCollection));
    }

    public function test_map(): void
    {
        $itemValue = 5;
        $arrKey = 'testKey';
        $collection = new DummyCollection([new DummyItem($itemValue)]);
        $this->assertEquals([[$arrKey => $itemValue]], $collection->map(fn (DummyItem $item) => [$arrKey => $itemValue]));
    }

    public function test_is_empty(): void
    {
        $emptyCollection = new DummyCollection([]);
        $this->assertTrue($emptyCollection->isEmpty());
    }

    public function test_filter(): void
    {
        $collection = new DummyCollection([
            new DummyItem(1),
            new DummyItem(2),
            new DummyItem(3),
            new DummyItem(4),
        ]);
        $expectedCollectionAfterFiltered = new DummyCollection([
            new DummyItem(2),
            new DummyItem(4),
        ]);

        $filteredCollection = $collection->filter(fn (DummyItem $item) => $item->value % 2 === 0);

        $this->assertTrue($filteredCollection->isEqual($expectedCollectionAfterFiltered));

    }

    public function test_find_success(): void
    {
        $findableValue = 25;

        $collection = new DummyCollection([
            new DummyItem(1),
            new DummyItem(2),
            new DummyItem($findableValue),
            new DummyItem(4),
        ]);

        $this->assertEquals($findableValue, $collection->find(fn (DummyItem $item) => $item->value === $findableValue)->value);
    }

    public function test_find_null(): void
    {
        $notExistingValue = 50;

        $collection = new DummyCollection([
            new DummyItem(1),
            new DummyItem(2),
            new DummyItem(3),
            new DummyItem(4),
        ]);

        $this->assertNull($collection->find(fn (DummyItem $item) => $item->value === $notExistingValue));
    }

    public function test_map_self_success(): void
    {
        $collection = new DummyCollection([new DummyItem(2), new DummyItem(4)]);
        $mappedCollection = $collection->mapSelf(fn (DummyItem $dummyItem) => new DummyItem($dummyItem->value * 2));
        $this->assertEquals(4, $mappedCollection->get(0)->value);
        $this->assertEquals(8, $mappedCollection->get(1)->value);
    }

    public function test_map_self_failed_on_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new DummyCollection([new DummyItem(2)]);
        $collection->mapSelf(fn (DummyItem $dummyItem) => new stdClass);
    }

    public function test_push(): void
    {
        $collection = new DummyCollection([new DummyItem(0)]);
        $collection->push(new DummyItem(1));
        $this->assertCount(2, $collection);
    }

    public function test_push_not_adding_null(): void
    {
        $collection = new DummyCollection([new DummyItem(0)]);
        $collection->push(null);
        $this->assertCount(1, $collection);
    }

    public function test_push_failed_on_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new DummyCollection([new DummyItem(0)]);
        $collection->push(new DummySecondItem(1));
    }

    public function test_get(): void
    {
        $collection = new DummyCollection([new DummyItem(0), new DummyItem(1), new DummyItem(2), new DummyItem(3)]);
        $this->assertEquals(3, $collection->get(3)->value);
    }

    public function test_merge(): void
    {
        $collection1 = new DummyCollection([new DummyItem(0), new DummyItem(1)]);
        $collection2 = new DummyCollection([new DummyItem(3), new DummyItem(4)]);
        $merged = $collection1->merge($collection2);
        $this->assertCount(4, $merged);
    }

    public function test_merge_failed_on_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $collection1 = new DummyCollection([new DummyItem(0), new DummyItem(1)]);
        $collection2 = new DummySecondCollection([new DummySecondItem(2, 'a')]);
        $collection1->merge($collection2);
    }

    public function test_remove(): void
    {
        $expectedCount = 2;

        $collection = new DummyCollection([
            new DummyItem(0),
            new DummyItem(1),
            new DummyItem(2),
        ]);
        $collection->remove(1);

        $this->assertEquals($expectedCount, $collection->count());
    }

    public function test_first(): void
    {
        $first = new DummyItem(1);
        $middle = new DummyItem(2);
        $last = new DummyItem(3);
        $collection = new DummyCollection([
            $first,
            $middle,
            $last,
        ]);

        $this->assertEquals($first, $collection->first());
    }

    public function test_last(): void
    {
        $first = new DummyItem(1);
        $middle = new DummyItem(2);
        $last = new DummyItem(3);
        $collection = new DummyCollection([
            $first,
            $middle,
            $last,
        ]);

        $this->assertEquals($last, $collection->last());
    }

    public function test_contain_find(): void
    {
        $collection = new DummyCollection([
            new DummyItem(0),
            new DummyItem(1),
            new DummyItem(2),
            new DummyItem(4),
        ]);

        $this->assertTrue($collection->contains(new DummyItem(0)));
    }

    public function test_contain_not_find(): void
    {

        $collection = new DummyCollection([
            new DummyItem(0),
            new DummyItem(1),
            new DummyItem(2),
            new DummyItem(4),
        ]);

        $this->assertFalse($collection->contains(new DummyItem(10)));
    }
}
