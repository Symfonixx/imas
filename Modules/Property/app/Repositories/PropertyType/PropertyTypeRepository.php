<?php

namespace Modules\Property\Repositories\PropertyType;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Property\Models\PropertyType;

interface PropertyTypeRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?PropertyType;

    public function store(array $data): mixed;

    public function update(array $data, PropertyType $propertyType, bool $updateTranslations = false): mixed;

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): ?bool;
}
