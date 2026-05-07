<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertySlide extends Model
{
    protected $table = 'property_slides';

    protected $fillable = [
        'property_id',
        'image',
        'position',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'position' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
