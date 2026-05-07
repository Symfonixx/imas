<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class PropertyType extends Model
{
    use HasTranslations;

    protected $table = 'property_types';

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'attribute_family_id',
    ];

    protected $casts = [
        'attribute_family_id' => 'integer',
    ];

    public function attributeFamily(): BelongsTo
    {
        return $this->belongsTo(AttributeFamily::class, 'attribute_family_id');
    }
}
