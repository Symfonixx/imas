<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Modules\Property\Enums\LocationType;
use Spatie\Translatable\HasTranslations;

class Location extends Model
{
    use HasTranslations;

    protected $table = 'locations';

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'parent_id',
        'type',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'type' => LocationType::class,
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return array<int, int>
     */
    public static function descendantIdsOf(int $id): array
    {
        $ids = [];
        $queue = [$id];

        while ($queue !== []) {
            $current = array_shift($queue);
            $childIds = static::query()->where('parent_id', $current)->pluck('id')->all();
            foreach ($childIds as $childId) {
                $ids[] = (int) $childId;
                $queue[] = (int) $childId;
            }
        }

        return $ids;
    }

    /**
     * @return Collection<int, self>
     */
    public static function orderedForAdmin(): Collection
    {
        return static::query()
            ->with('parent:id,name')
            ->orderByRaw('parent_id is null desc')
            ->orderBy('id')
            ->get();
    }

    /**
     * Nested roots with `treeChildren` relation on each node (recursive).
     *
     * @return Collection<int, self>
     */
    public static function nestedForest(?string $typeFilter = null): Collection
    {
        if ($typeFilter !== null && $typeFilter !== '') {
            $matching = static::query()->where('type', $typeFilter)->get(['id', 'parent_id']);
            $byId = static::query()->get(['id', 'parent_id'])->keyBy('id');

            $ids = [];
            foreach ($matching as $loc) {
                $ids[] = $loc->id;
                $pid = $loc->parent_id;
                while ($pid !== null) {
                    $ids[] = $pid;
                    $pid = $byId->get($pid)?->parent_id;
                }
            }
            $ids = array_values(array_unique($ids));

            $flat = static::query()
                ->whereIn('id', $ids)
                ->orderByRaw('parent_id is null desc')
                ->orderBy('id')
                ->get();
        } else {
            $flat = static::query()
                ->orderByRaw('parent_id is null desc')
                ->orderBy('id')
                ->get();
        }

        return collect(static::buildTreeNodes($flat, null));
    }

    /**
     * @param  Collection<int, self>  $flat
     * @return array<int, self>
     */
    protected static function buildTreeNodes(Collection $flat, ?int $parentId): array
    {
        $branch = [];
        foreach ($flat as $item) {
            if ($item->parent_id === $parentId) {
                $children = static::buildTreeNodes($flat, $item->id);
                $item->setRelation('treeChildren', collect($children));
                $branch[] = $item;
            }
        }

        return $branch;
    }
}
