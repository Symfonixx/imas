<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertySlideMedia extends Model
{
    public const TYPE_IMAGE = 'image';

    public const TYPE_VIDEO = 'video';

    protected $table = 'property_slide_media';

    protected $fillable = [
        'property_id',
        'slide_category_id',
        'type',
        'path',
        'position',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'slide_category_id' => 'integer',
        'position' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function slideCategory(): BelongsTo
    {
        return $this->belongsTo(SlideCategory::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->path);
    }

    public static function isOwnedStoragePath(string $path): bool
    {
        $path = ltrim($path, '/');

        return str_starts_with($path, 'properties/slides/')
            || str_starts_with($path, 'property/slides/')
            || str_starts_with($path, 'property/slide-categories/');
    }
}
