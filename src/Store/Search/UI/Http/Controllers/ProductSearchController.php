<?php

namespace Src\Store\Search\UI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Store\Search\Application\UseCases\Search\ProductSearchQuery;
use Src\Store\Search\UI\Http\Requests\SearchProductRequest;

class ProductSearchController extends Controller
{
    public function __construct(
        private readonly ProductSearchQuery $searchQuery
    ) {}

    public function __invoke(SearchProductRequest $request): JsonResponse
    {
        $result = $this->searchQuery->handle($request->toDto());

        return response()->json($result);
    }
}
