<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Property\Database\Factories\PropertyAttributeOptionFactory;
use Spatie\Translatable\HasTranslations;

class PropertyAttributeOption extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['label'];

    protected $fillable = [
        'attribute_id',
        'label',
        'icon',
        'position',
        'is_active',
    ];

    protected $casts = [
        'attribute_id' => 'integer',
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): PropertyAttributeOptionFactory
    {
        return PropertyAttributeOptionFactory::new();
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(PropertyAttribute::class, 'attribute_id');
    }

    /**
     * @param  Builder<PropertyAttributeOption>  $query
     * @return Builder<PropertyAttributeOption>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<PropertyAttributeOption>  $query
     * @return Builder<PropertyAttributeOption>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }
}
