<?php

namespace Modules\Corporate\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Testimonial extends Model
{
    use HasTranslations;

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
