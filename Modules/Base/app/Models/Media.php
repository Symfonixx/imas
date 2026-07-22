<?php

namespace Modules\Base\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'folder_id',
        'name',
        'alt_text',
        'title',
        'caption',
        'path',
        'disk',
        'mime_type',
        'size',
        'width',
        'height',
        'user_id',
        'archived_at',
    ];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->path);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function isImage(): bool
    {
        $mime = (string) ($this->mime_type ?? '');

        return str_starts_with($mime, 'image/');
    }

    public function archive(): void
    {
        if ($this->archived_at !== null) {
            return;
        }

        $this->forceFill(['archived_at' => now()])->save();
    }
}
