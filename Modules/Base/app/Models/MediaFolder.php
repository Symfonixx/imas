<?php

namespace Modules\Base\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class MediaFolder extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'user_id',
    ];

    protected $casts = [
        'parent_id' => 'integer',
    ];

    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function getStoragePathAttribute(): string
    {
        return 'media-library/'.$this->id.'-'.$this->slug;
    }

    /**
     * @return list<int>
     */
    public static function descendantIdsOf(int $folderId): array
    {
        $ids = [];
        $frontier = [$folderId];

        while ($frontier !== []) {
            $childIds = static::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->all();

            if ($childIds === []) {
                break;
            }

            foreach ($childIds as $childId) {
                $ids[] = (int) $childId;
            }

            $frontier = $childIds;
        }

        return $ids;
    }

    /**
     * @param  Collection<int, self>  $folders
     * @return list<self>
     */
    public static function sortTree(Collection $folders, ?int $parentId = null): array
    {
        $nodes = [];

        foreach ($folders as $folder) {
            $folderParentId = $folder->parent_id !== null ? (int) $folder->parent_id : null;
            if ($folderParentId === $parentId) {
                $nodes[] = $folder;
                foreach (static::sortTree($folders, (int) $folder->id) as $child) {
                    $nodes[] = $child;
                }
            }
        }

        return $nodes;
    }
}
