<?php

declare(strict_types=1);

namespace Src\Backoffice\Catalog\Infrastructure\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Src\Backoffice\Catalog\Domain\Category\Category;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttribute;
use Src\Backoffice\Catalog\Domain\Category\CategoryAttributes;
use Src\Backoffice\Catalog\Domain\Category\CategoryId;
use Src\Backoffice\Catalog\Domain\Category\CategoryName;
use Src\Backoffice\Catalog\Infrastructure\Eloquent\Factories\CategoryEloquentFactory;
use Src\Shared\Infrastructure\Models\CastableModel;

/**
 * @property Collection<int, CategoryAttributeEloquentModel> $categoryAttributes
 * @property CategoryId $id
 * @property CategoryName $name
 */
class CategoryEloquentModel extends CastableModel
{
    /** @use HasFactory<CategoryEloquentFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'id',
    ];

    protected $casts = [
        'name' => CategoryName::class,
        'id' => CategoryId::class,
    ];

    protected $table = 'categories';

    /**
     * @return HasMany<CategoryAttributeEloquentModel, $this>
     */
    public function categoryAttributes(): HasMany
    {
        return $this->hasMany(CategoryAttributeEloquentModel::class, 'category_id', 'id');
    }

//    /**
//     * @return HasMany<ProductEloquentModel, $this>
//     */
//    public function products(): HasMany
//    {
//        return $this->hasMany(ProductEloquentModel::class);
//    }

    protected static function newFactory(): CategoryEloquentFactory
    {
        return CategoryEloquentFactory::new();
    }

    public function toEntity(): Category
    {
        /** @var CategoryAttribute[] $attributes t */
        $attributes = $this->categoryAttributes->map(static fn (CategoryAttributeEloquentModel $model) => $model->toEntity())->toArray();
        $this->refresh();

        return new Category(
            $this->id,
            $this->name,
            new CategoryAttributes(
                $attributes
            )
        );
    }
}
