<?php

namespace Src\Store\Search\UI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Shared\Domain\ProductId;
use Src\Store\Search\Domain\ProductSearchRepository;

class GetProductController extends Controller
{
    public function __construct(
        private readonly ProductSearchRepository $repository
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        $product = $this->repository->get(new ProductId($id));

        if (! $product) {
            return new JsonResponse([], 404);
        }

        return response()->json($product->toResponse());
    }
}
