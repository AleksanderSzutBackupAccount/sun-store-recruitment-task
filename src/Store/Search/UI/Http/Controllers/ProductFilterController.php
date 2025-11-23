<?php

declare(strict_types=1);

namespace Src\Store\Search\UI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Shared\Application\Bus\Query\QueryBusInterface;
use Src\Store\Search\Application\UseCases\Filters\ProductFiltersQuery;

class ProductFilterController extends Controller
{
    public function __construct(
        private readonly QueryBusInterface $bus
    ) {}

    public function __invoke(): JsonResponse
    {
        return response()->json($this->bus->ask(new ProductFiltersQuery)->toResponse());
    }
}
