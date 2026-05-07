<?php

namespace Modules\Property\Repositories\Attribute;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Property\Models\PropertyAttribute;

interface AttributeRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?PropertyAttribute;

    public function store(array $data): mixed;

    public function update(array $data, PropertyAttribute $attribute, bool $updateTranslations = false): mixed;

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): ?bool;
}
