<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Cms\Database\Factories\BlogCategoryFactory;
use Spatie\Translatable\HasTranslations;

class BlogCategory extends Model
{
    use HasFactory;
    use HasTranslations;

    protected static function newFactory(): BlogCategoryFactory
    {
        return BlogCategoryFactory::new();
    }

    public $translatable = ['name', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $appends = ['meta_image_link'];

    protected $fillable = ['name', 'slug', 'add_to_navbar', 'meta_title', 'meta_description', 'meta_keywords', 'meta_image'];

    protected $casts = [
        'add_to_navbar' => 'boolean',
    ];

    public function getMetaImageLinkAttribute(): string
    {
        $path = $this->attributes['meta_image'] ?? null;

        if ($path) {
            return asset('storage/'.$path);
        }

        return asset('images/blank.png');
    }
}
