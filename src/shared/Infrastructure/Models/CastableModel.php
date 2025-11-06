<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Src\Shared\Infrastructure\Casts\ModelCasts;

abstract class CastableModel extends Model
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->casts = ModelCasts::make($this->casts);
        parent::__construct($attributes);
    }
}
