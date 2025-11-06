<?php

declare(strict_types=1);

namespace Src\Shared\Domain;

/**
 * @template T as object
 */
interface ComparableCollectionInterface
{
    /**
     * @param  T  $object1
     * @param  T  $object2
     */
    public function compare(object $object1, object $object2): bool;
}
