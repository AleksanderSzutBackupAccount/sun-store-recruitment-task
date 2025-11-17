<?php

namespace Src\Store\Search\UI\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Src\Store\Search\Domain\SearchProductsDto;

/**
 * @property-read string|null $search
 * @property-read int|null $min_price
 * @property-read int|null $max_price
 * @property-read string|null $category
 * @property-read string|null $sort_by
 * @property-read string|null $sort_order
 * @property-read string|null $cursor
 * @property-read int|null $perPage
 * @property-read array<string>|null $filters
 */
class SearchProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'query' => ['nullable', 'string', 'max:255'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'string', 'in:id,name.keyword,price,created_at'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
            'cursor' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'filters' => ['nullable', 'array'],
            'filters.*' => ['string', 'max:255'],
        ];
    }

    public function toDto(): SearchProductsDto
    {
        return new SearchProductsDto(
            search: $this->search,
            category: $this->category,
            sortBy: $this->sort_by ?? 'created_at',
            sortOrder: $this->sort_order ?? 'asc',
            cursor: $this->cursor,
            minPrice: $this->min_price,
            maxPrice: $this->max_price,
            filters: $this->filters ?? [],
            perPage: $this->perPage ?? 15,
        );
    }
}
