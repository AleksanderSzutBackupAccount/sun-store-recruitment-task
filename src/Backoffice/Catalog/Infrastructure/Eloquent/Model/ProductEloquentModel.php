<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Infrastructure\Eloquent\Model;

use DateTimeImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Factories\ProductEloquentFactory;
use Src\Shared\Domain\ProductId;
use Src\Shared\Infrastructure\Models\CastableModel;

/**
 * @property ProductId $id
 * @property string $name
 * @property string $description
 * @property string $manufacturer
 * @property CategoryEloquentModel $category
 * @property int $price
 * @property DateTimeImmutable $created_at
 */
class ProductEloquentModel extends CastableModel
{
    /**
     * @use HasFactory<ProductEloquentFactory>
     */
    use HasFactory, HasUuids;

    protected $table = 'products';

    protected $with = ['category', 'attributes'];

    public $casts = [
        'created_at' => 'immutable_datetime',
        'id' => ProductId::class,
    ];

    protected $fillable = [
        'id', 'category_id', 'name', 'description', 'manufacturer', 'price',
    ];

    /**
     * @return BelongsTo<CategoryEloquentModel, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryEloquentModel::class, 'category_id', 'id');
    }

    /**
     * @return HasMany<ProductAttributeEloquentModel, $this>
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttributeEloquentModel::class, 'product_id', 'id');
    }

    protected static function newFactory(): ProductEloquentFactory
    {
        return ProductEloquentFactory::new();
    }
}
