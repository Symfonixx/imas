<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;
use Modules\Property\Database\Factories\PropertyAttributeFactory;
use Modules\Property\Enums\AttributeType;
use Spatie\Translatable\HasTranslations;

class PropertyAttribute extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['name', 'help_text'];

    protected $fillable = [
        'code',
        'name',
        'help_text',
        'type',
        'is_required',
        'is_unique',
        'is_active',
        'validation',
        'regex',
        'default_value',
    ];

    protected $casts = [
        'type' => AttributeType::class,
        'is_required' => 'boolean',
        'is_unique' => 'boolean',
        'is_active' => 'boolean',
        'default_value' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $attribute): void {
            if ($attribute->isDirty('code')) {
                throw new LogicException('A property attribute code cannot be changed after creation.');
            }
        });
    }

    protected static function newFactory(): PropertyAttributeFactory
    {
        return PropertyAttributeFactory::new();
    }

    public function options(): HasMany
    {
        return $this->hasMany(PropertyAttributeOption::class, 'attribute_id')->ordered();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            PropertyAttributeGroup::class,
            'property_attribute_group_mappings',
            'attribute_id',
            'group_id'
        )->withPivot('position')->orderByPivot('position');
    }

    public function values(): HasMany
    {
        return $this->hasMany(PropertyAttributeValue::class, 'attribute_id');
    }

    /**
     * @param  Builder<PropertyAttribute>  $query
     * @return Builder<PropertyAttribute>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<PropertyAttribute>  $query
     * @return Builder<PropertyAttribute>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('code')->orderBy('id');
    }
}
