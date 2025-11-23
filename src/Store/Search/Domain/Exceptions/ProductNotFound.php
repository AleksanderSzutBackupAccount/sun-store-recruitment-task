<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain\Exceptions;

class ProductNotFound extends \DomainException
{
    protected $message = 'Product not found';
}
