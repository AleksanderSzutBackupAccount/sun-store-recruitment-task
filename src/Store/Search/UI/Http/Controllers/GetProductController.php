<?php

declare(strict_types=1);

namespace Src\Store\Search\UI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Shared\Application\Bus\Query\QueryBusInterface;
use Src\Shared\Domain\ProductId;
use Src\Store\Search\Application\UseCases\Get\ProductGetQuery;
use Src\Store\Search\Domain\Exceptions\ProductNotFound;

class GetProductController extends Controller
{
    public function __construct(
        private readonly QueryBusInterface $queryBus
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        try {
            $product = $this->queryBus->ask(new ProductGetQuery($id));
        } catch (ProductNotFound $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json($product->toResponse());
    }
}
