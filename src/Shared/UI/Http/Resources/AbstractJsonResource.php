<?php

declare(strict_types=1);

namespace Src\Shared\UI\Http\Resources;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract readonly class AbstractJsonResource implements Responsable
{
    /**
     * @return array<int|string, mixed>
     */
    abstract public function toArray(Request $request): array;

    public function toResponse($request): JsonResponse
    {
        return new JsonResponse($this->toResponseArray($request));
    }

    /**
     * @return array<int|string, mixed>
     */
    private function toResponseArray(Request $request): array
    {
        return array_map(static function ($item) use ($request) {
            if (! $item instanceof self) {
                return $item;
            }

            return $item->toResponseArray($request);
        }, $this->toArray($request));
    }
}
