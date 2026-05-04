<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Slide extends Model
{
    use HasTranslations;

    public $translatable = ['main_title', 'subtitle'];

    protected $appends = ['image_link'];

    protected $fillable = [
        'image',
        'main_title',
        'subtitle',
        'link',
        'rank',
        'status',
    ];

    protected $casts = [
        'rank' => 'integer',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'Published');
    }

    public function getImageLinkAttribute(): string
    {
        $path = $this->attributes['image'] ?? null;

        if ($path) {
            return asset('storage/'.$path);
        }

        return asset('images/blank.png');
    }
}
