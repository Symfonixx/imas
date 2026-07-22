<?php

namespace Modules\Property\Repositories\SlideCategory;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Property\Models\SlideCategory;

interface SlideCategoryRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    public function store(array $data): ?SlideCategory;

    public function update(array $data, SlideCategory $slideCategory): bool;

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): bool;
}
