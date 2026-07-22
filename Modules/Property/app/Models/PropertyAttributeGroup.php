<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Property\Database\Factories\PropertyAttributeGroupFactory;
use Spatie\Translatable\HasTranslations;

class PropertyAttributeGroup extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'position',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): PropertyAttributeGroupFactory
    {
        return PropertyAttributeGroupFactory::new();
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(
            Property::class,
            'property_attribute_group',
            'attribute_group_id',
            'property_id'
        )->withPivot('position')->withTimestamps();
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            PropertyAttribute::class,
            'property_attribute_group_mappings',
            'group_id',
            'attribute_id'
        )->withPivot('position')->orderByPivot('position');
    }

    /**
     * @param  Builder<PropertyAttributeGroup>  $query
     * @return Builder<PropertyAttributeGroup>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<PropertyAttributeGroup>  $query
     * @return Builder<PropertyAttributeGroup>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }
}
