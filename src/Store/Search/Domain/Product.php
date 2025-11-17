<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain;

use Src\Shared\Domain\ProductId;

readonly class Product
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public ProductId $id,
        public string $name,
        public string $description,
        public string $manufacturer,
        public string $category,
        public int $price,
        public array $attributes,
        public \DateTimeImmutable $createdAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toIndex(): array
    {
        return [
            'id' => $this->id->value,
            'name' => $this->name,
            'category' => $this->category,
            'price' => $this->price,
            'description' => $this->description,
            'attributes' => $this->attributes,
            'manufacturer' => $this->manufacturer,
            'created_at' => $this->createdAt->format(DATE_ATOM),
        ];
    }
}
