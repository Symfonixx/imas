<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class AttributeFamily extends Model
{
    use HasTranslations;

    protected $table = 'attribute_families';

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'code',
    ];

    public function propertyTypes(): HasMany
    {
        return $this->hasMany(PropertyType::class, 'attribute_family_id');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            PropertyAttribute::class,
            'attribute_family_attribute',
            'attribute_family_id',
            'attribute_id'
        )->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
}
