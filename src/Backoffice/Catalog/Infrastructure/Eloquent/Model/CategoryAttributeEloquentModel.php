<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Infrastructure\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttribute;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeId;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributeType;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Factories\CategoryAttributeEloquentFactory;
use Src\Shared\Infrastructure\Models\CastableModel;

/**
 * @property CategoryAttributeId $id
 * @property string $name
 * @property string $unit
 * @property CategoryAttributeType $type
 */
class CategoryAttributeEloquentModel extends CastableModel
{
    /**
     * @use HasFactory<CategoryAttributeEloquentFactory>
     */
    use HasFactory, HasUuids;

    protected $table = 'category_attributes';

    protected $fillable = [
        'id',
        'category_id',
        'name',
        'type',
        'unit',
    ];

    public $casts = [
        'id' => CategoryAttributeId::class,
        'type' => CategoryAttributeType::class,
    ];

    /**
     * @return BelongsTo<CategoryEloquentModel, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryEloquentModel::class, 'category_id', 'id');
    }

    protected static function newFactory(): CategoryAttributeEloquentFactory
    {
        return CategoryAttributeEloquentFactory::new();
    }

    public function toEntity(): CategoryAttribute
    {
        return new CategoryAttribute(
            $this->id,
            $this->name,
            $this->type,
            $this->unit,
        );
    }
}
