<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain;
use Src\Shared\Domain\ProductId;

readonly class Product
{
    /**
     * @param ProductId $id
     * @param string $name
     * @param string $description
     * @param string $category
     * @param string $manufacture
     * @param int $price
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public ProductId $id,
        public string $name,
        public string $description,
        public string $manufacture,
        public string $category,
        public int $price,
        public array $attributes
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toIndex(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'price' => $this->price,
            'description' => $this->description,
            'attributes' => $this->attributes,
        ];
    }
}
