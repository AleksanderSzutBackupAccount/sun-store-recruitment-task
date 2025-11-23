<?php

declare(strict_types=1);

namespace Src\Store\Search\UI\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Src\Store\Search\Domain\Filters\Filters;
use Src\Store\Search\Domain\SearchProductsDto;

/**
 * @property-read string|null $search
 * @property-read string|null $category
 * @property-read string|null $sort_by
 * @property-read string|null $sort_order
 * @property-read string|null $cursor
 * @property-read string|null $per_page
 * @property-read null|array<string, string|mixed[]> $filters
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
            'category' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'string', 'in:id,name.keyword,price,created_at'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
            'cursor' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'filters' => ['nullable', 'array'],
            'filters.*' => ['nullable'],
            'filters.*.*' => ['nullable'],
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
            filters: Filters::fromArray($this->filters ?? []),
            perPage: (int) ($this->per_page ?? 15),
        );
    }
}
