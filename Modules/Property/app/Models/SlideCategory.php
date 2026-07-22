<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\User\Enums\CmsStatus;
use Spatie\Translatable\HasTranslations;

class SlideCategory extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'description',
        'slug',
        'status',
        'position',
    ];

    protected $casts = [
        'status' => CmsStatus::class,
        'position' => 'integer',
    ];

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(
            Property::class,
            'property_slide_category',
            'slide_category_id',
            'property_id'
        )->withTimestamps();
    }

    public function media(): HasMany
    {
        return $this->hasMany(PropertySlideMedia::class, 'slide_category_id')
            ->orderBy('position')
            ->orderBy('id');
    }
}
