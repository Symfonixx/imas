<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAttributeValue extends Model
{
    protected $table = 'property_attribute_values';

    protected $fillable = [
        'property_id',
        'attribute_id',
        'value_text',
        'value_number',
        'value_boolean',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'attribute_id' => 'integer',
        'value_number' => 'decimal:4',
        'value_boolean' => 'boolean',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(PropertyAttribute::class, 'attribute_id');
    }
}
