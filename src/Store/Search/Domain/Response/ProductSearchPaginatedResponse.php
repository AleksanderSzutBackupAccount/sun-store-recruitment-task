<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain\Response;

use Src\Shared\Domain\Response\AbstractPaginatedResponse;

/**
 * @extends AbstractPaginatedResponse<ProductResponse>
 */
readonly class ProductSearchPaginatedResponse extends AbstractPaginatedResponse {}
