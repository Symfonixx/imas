<?php

namespace Modules\Base\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaFolder extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'user_id',
    ];

    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStoragePathAttribute(): string
    {
        return 'media-library/'.$this->id.'-'.$this->slug;
    }
}
