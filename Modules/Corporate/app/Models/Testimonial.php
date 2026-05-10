<?php

namespace Modules\Corporate\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Corporate\Database\Factories\TestimonialFactory;
use Spatie\Translatable\HasTranslations;

class Testimonial extends Model
{
    use HasFactory;
    use HasTranslations;

    protected static function newFactory(): TestimonialFactory
    {
        return TestimonialFactory::new();
    }

    protected $table = 'testimonials';

    public $translatable = ['name', 'position', 'quote'];

    protected $appends = ['avatar_link'];

    protected $fillable = [
        'name',
        'client',
        'avatar',
        'position',
        'link',
        'quote',
        'rank',
        'status',
    ];

    protected $casts = [
        'rank' => 'integer',
    ];

    public function scopePublished($q)
    {
        $q->where('status', 'Published');
    }

    public function getAvatarLinkAttribute(): string
    {
        $path = $this->attributes['avatar'] ?? null;
        if ($path) {
            return asset('storage/'.$path);
        }

        return asset('images/blank.png');
    }
}
