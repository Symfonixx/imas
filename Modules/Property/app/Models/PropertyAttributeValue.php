<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Property\Database\Factories\PropertyAttributeValueFactory;

class PropertyAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'attribute_id',
        'text_value',
        'decimal_value',
        'boolean_value',
        'integer_value',
        'date_value',
        'datetime_value',
        'json_value',
        'unique_hash',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'attribute_id' => 'integer',
        'decimal_value' => 'decimal:6',
        'boolean_value' => 'boolean',
        'integer_value' => 'integer',
        'date_value' => 'date',
        'datetime_value' => 'datetime',
        'json_value' => 'array',
    ];

    protected static function newFactory(): PropertyAttributeValueFactory
    {
        return PropertyAttributeValueFactory::new();
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(PropertyAttribute::class, 'attribute_id');
    }

    /**
     * @param  Builder<PropertyAttributeValue>  $query
     * @return Builder<PropertyAttributeValue>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('attribute_id')->orderBy('id');
    }
}
