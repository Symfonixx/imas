<?php

namespace Modules\Corporate\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Corporate\Database\Factories\CorporateServiceFactory;
use Spatie\Translatable\HasTranslations;

class CorporateService extends Model
{
    use HasFactory;
    use HasTranslations;

    protected static function newFactory(): CorporateServiceFactory
    {
        return CorporateServiceFactory::new();
    }

    protected $table = 'corporate_services';

    public $translatable = ['title', 'description', 'content', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $appends = ['image_link', 'meta_image_link'];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'image',
        'meta_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'featured',
        'visits',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'visits' => 'integer',
    ];

    public function scopeFeatured($q)
    {
        $q->where('status', 'Published')->where('featured', true);
    }

    public function scopePublished($q)
    {
        $q->where('status', 'Published');
    }

    public function getImageLinkAttribute(): string
    {
        return $this->resolveMediaPath($this->attributes['image'] ?? null);
    }

    public function getMetaImageLinkAttribute(): string
    {
        return $this->resolveMediaPath($this->attributes['meta_image'] ?? null);
    }

    private function resolveMediaPath(?string $path): string
    {
        if ($path) {
            return asset('storage/'.$path);
        }

        return asset('images/blank.png');
    }
}
