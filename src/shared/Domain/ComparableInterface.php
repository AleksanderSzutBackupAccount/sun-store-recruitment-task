<?php

declare(strict_types=1);

namespace Src\Shared\Domain;

interface ComparableInterface
{
    public function equals(self $compare): bool;
}
