<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Infrastructure\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Factories\ProductAttributeEloquentFactory;
use Src\Shared\Infrastructure\Models\CastableModel;

class ProductAttributeEloquentModel extends CastableModel
{
    /**
     * @use HasFactory<ProductAttributeEloquentFactory>
     */
    use HasFactory, HasUuids;

    protected $table = 'product_attributes';

    protected $fillable = ['product_id', 'category_attribute_id', 'name', 'value'];

    /**
     * @return BelongsTo<ProductEloquentModel, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductEloquentModel::class);
    }

    /**
     * @return BelongsTo<CategoryAttributeEloquentModel, $this>
     */
    public function categoryAttribute(): BelongsTo
    {
        return $this->belongsTo(CategoryAttributeEloquentModel::class);
    }

    protected static function newFactory(): ProductAttributeEloquentFactory
    {
        return ProductAttributeEloquentFactory::new();
    }
}
