<?php

namespace Src\Store\Search\UI\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Src\Store\Search\Domain\SearchProductsDto;

class SearchProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => ['nullable', 'string', 'max:255'],
            'minPrice' => ['nullable', 'numeric', 'min:0'],
            'maxPrice' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:100'],
            'sortField' => ['nullable', 'string', 'in:id,name,price,created_at'],
            'sortOrder' => ['nullable', 'string', 'in:asc,desc'],
            'cursor' => ['nullable', 'string'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'attributes' => ['nullable', 'array'],
            'attributes.*' => ['string', 'max:255'],
        ];
    }

    public function toDto(): SearchProductsDto
    {
        $data = $this->validated();

        return new SearchProductsDto(
            query: $data['query'] ?? null,
            category: $data['category'] ?? null,
            sortBy: $data['sortField'] ?? 'id',
            sortOrder: $data['sortOrder'] ?? 'asc',
            cursor: $data['cursor'] ?? null,
            minPrice: isset($data['minPrice']) ? (int) $data['minPrice'] : null,
            maxPrice: isset($data['maxPrice']) ? (int) $data['maxPrice'] : null,
            filters: $data['attributes'] ?? [],
            perPage: (int) ($data['perPage'] ?? 15),
        );
    }
}
