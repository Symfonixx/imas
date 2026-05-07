<?php

namespace Modules\Property\Repositories\AttributeFamily;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Property\Models\AttributeFamily;

interface AttributeFamilyRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?AttributeFamily;

    public function store(array $data): mixed;

    public function update(array $data, AttributeFamily $family, bool $updateTranslations = false): mixed;

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): ?bool;
}
