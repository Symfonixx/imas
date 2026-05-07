<?php

namespace Modules\Property\Repositories\Location;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Property\Models\Location;

interface LocationRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?Location;

    public function store(array $data): mixed;

    public function update(array $data, Location $location, bool $updateTranslations = false): mixed;

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): ?bool;
}
