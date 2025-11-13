<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Infrastructure\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Factories\ProductEloquentFactory;
use Src\Shared\Infrastructure\Models\CastableModel;

class ProductEloquentModel extends CastableModel
{
    /**
     * @use HasFactory<ProductEloquentFactory>
     */
    use HasFactory, HasUuids;

    protected $table = 'products';

    protected $with = ['category', 'attributes'];

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
