<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Collection\Dummies;

use Src\Shared\Domain\ComparableInterface;

readonly class DummyComparableDto implements ComparableInterface
{
    public function __construct(public string $value, private string $privateUniqueKey) {}

    /**
     * @param  self  $compare
     */
    public function equals(ComparableInterface $compare): bool
    {
        return $this->privateUniqueKey === $compare->privateUniqueKey;
    }
}
