<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Property\Enums\AttributeType;
use Spatie\Translatable\HasTranslations;

class PropertyAttribute extends Model
{
    use HasTranslations;

    protected $table = 'attributes';

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'code',
        'type',
        'options',
        'is_filterable',
        'is_required',
        'is_trans',
    ];

    protected $casts = [
        'type' => AttributeType::class,
        'options' => 'array',
        'is_filterable' => 'boolean',
        'is_required' => 'boolean',
        'is_trans' => 'boolean',
    ];

    public function families(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeFamily::class,
            'attribute_family_attribute',
            'attribute_id',
            'attribute_family_id'
        )->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function values(): HasMany
    {
        return $this->hasMany(PropertyAttributeValue::class, 'attribute_id');
    }
}
