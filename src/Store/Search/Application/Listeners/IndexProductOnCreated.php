<?php

declare(strict_types=1);

namespace Src\Store\Search\Application\Listeners;

use Src\Store\Search\Domain\ProductSearchIndexer;
use Src\Store\Search\Integration\ProductCreatedMessage;

readonly class IndexProductOnCreated
{
    public function __construct(
        private ProductSearchIndexer $productSearchIndexer,
    ) {}

    public function handle(ProductCreatedMessage $event): void
    {
        $this->productSearchIndexer->index($event->entity);
    }
}
