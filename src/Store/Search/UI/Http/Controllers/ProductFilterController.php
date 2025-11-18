<?php

namespace Src\Store\Search\UI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Store\Search\Application\UseCases\Search\ProductSearchQuery;
use Src\Store\Search\Domain\ProductSearchRepository;
use Src\Store\Search\Infrastructure\Elastic\ProductSearchElasticRepository;
use Src\Store\Search\UI\Http\Requests\SearchProductRequest;

class ProductFilterController extends Controller
{
    public function __construct(
        private readonly ProductSearchRepository $repository
    ) {}

    public function __invoke(SearchProductRequest $request): JsonResponse
    {
        $result = $this->repository->getFilters();

        return response()->json($result);
    }
}
