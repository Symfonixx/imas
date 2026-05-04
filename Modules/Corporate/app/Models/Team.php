<?php

namespace Modules\Corporate\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Team extends Model
{
    use HasTranslations;

    protected $table = 'teams';

    public $translatable = ['name', 'position'];

    protected $appends = ['avatar_link'];

    protected $fillable = [
        'name',
        'avatar',
        'position',
        'link',
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
