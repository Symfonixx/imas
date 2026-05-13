<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitType extends Model
{
    protected $table = 'unit_types';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_id',
        'catalog_id',
        'name',
        'min_area',
        'max_area',
        'price',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'catalog_id' => 'integer',
        'min_area' => 'decimal:2',
        'max_area' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(ProjectUnitType::class, 'catalog_id');
    }
}
