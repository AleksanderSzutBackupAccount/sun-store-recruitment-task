<?php

namespace Src\Store\Search\UI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Shared\Application\Bus\Query\QueryBusInterface;
use Src\Store\Search\Application\UseCases\Search\ProductSearchQuery;
use Src\Store\Search\UI\Http\Requests\SearchProductRequest;

class ProductSearchController extends Controller
{
    public function __construct(
        private readonly QueryBusInterface $queryBus
    ) {}

    public function __invoke(SearchProductRequest $request): JsonResponse
    {
        $dto = $request->toDto();

        return response()->json($this->queryBus->ask(new ProductSearchQuery($dto))->toResponse());
    }
}
