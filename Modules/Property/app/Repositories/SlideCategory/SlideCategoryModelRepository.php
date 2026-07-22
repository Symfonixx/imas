<?php

namespace Modules\Property\Repositories\SlideCategory;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Property\Models\SlideCategory;

class SlideCategoryModelRepository implements SlideCategoryRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return SlideCategory::query()
            ->select($columns)
            ->when(
                isset($filters['publish']) && $filters['publish'] !== null && $filters['publish'] !== '',
                fn ($query) => $query->where('status', $filters['publish'])
            )
            ->orderBy('position')
            ->orderBy('id')
            ->paginate(config('core.page_size', 10));
    }

    public function store(array $data): ?SlideCategory
    {
        return SlideCategory::query()->create($data);
    }

    public function update(array $data, SlideCategory $slideCategory): bool
    {
        return $slideCategory->update($data);
    }

    public function deleteMulti(array $ids): bool
    {
        return (bool) SlideCategory::query()->whereKey($ids)->delete();
    }
}
