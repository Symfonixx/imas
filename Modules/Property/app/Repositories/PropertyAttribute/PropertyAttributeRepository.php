<?php

namespace Modules\Property\Repositories\PropertyAttribute;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeOption;

interface PropertyAttributeRepository
{
    public function all(array $columns = ['*']): LengthAwarePaginator;

    public function create(array $data): PropertyAttribute;

    public function update(PropertyAttribute $attribute, array $data): bool;

    public function deleteMany(array $ids): bool;

    public function hasValues(PropertyAttribute $attribute): bool;

    public function anyHaveValues(array $ids): bool;

    /**
     * @return list<int>
     */
    public function referencedOptionIds(PropertyAttribute $attribute): array;

    public function saveOption(
        PropertyAttribute $attribute,
        ?PropertyAttributeOption $option,
        array $data
    ): PropertyAttributeOption;

    public function deleteOptions(PropertyAttribute $attribute, array $ids): bool;
}
