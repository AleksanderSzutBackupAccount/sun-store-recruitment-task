<?php

declare(strict_types=1);

namespace Src\Store\Search\Domain\Response;

use Src\Shared\Domain\Response\ResponseItem;

class ProductResponse implements ResponseItem
{
    /**
     * @param  array<string, mixed>  ...$attributes
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $category,
        public int $price,
        public string $description,
        public string $manufacturer,
        public string $createdAt,
        public array $attributes
    ) {}

    /**
     * @param  array<string, mixed>  $array
     */
    public static function fromArray(array $array): self
    {
        $attributes = array_filter($array, static fn ($key) => str_starts_with($key, 'attr_'), ARRAY_FILTER_USE_KEY);

        return new self(
            id: (string) $array['id'],
            name: (string) $array['name'],
            category: (string) $array['category'],
            price: (int) $array['price'],
            description: (string) $array['description'],
            manufacturer: (string) $array['manufacturer'],
            createdAt: (string) $array['created_at'],
            attributes: $attributes
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toResponse(): array
    {
        return [...[
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'price' => $this->price,
            'description' => $this->description,
            'manufacturer' => $this->manufacturer,
            'created_at' => $this->createdAt,
        ], ...$this->attributes];
    }
}
