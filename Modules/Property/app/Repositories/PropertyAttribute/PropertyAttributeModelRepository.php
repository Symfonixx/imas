<?php

namespace Modules\Property\Repositories\PropertyAttribute;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeOption;

class PropertyAttributeModelRepository implements PropertyAttributeRepository
{
    public function all(array $columns = ['*']): LengthAwarePaginator
    {
        return PropertyAttribute::query()
            ->select($columns)
            ->withCount(['options', 'values'])
            ->ordered()
            ->paginate(config('core.page_size', 10));
    }

    public function create(array $data): PropertyAttribute
    {
        return PropertyAttribute::query()->create($data);
    }

    public function update(PropertyAttribute $attribute, array $data): bool
    {
        return $attribute->update($data);
    }

    public function deleteMany(array $ids): bool
    {
        return (bool) PropertyAttribute::query()->whereKey($ids)->delete();
    }

    public function hasValues(PropertyAttribute $attribute): bool
    {
        return $attribute->values()->exists();
    }

    public function anyHaveValues(array $ids): bool
    {
        return PropertyAttribute::query()
            ->whereKey($ids)
            ->whereHas('values')
            ->exists();
    }

    public function referencedOptionIds(PropertyAttribute $attribute): array
    {
        $ids = [];

        foreach ($attribute->values()
            ->select(['id', 'integer_value', 'json_value'])
            ->lazyById() as $value) {
            if ($value->integer_value !== null) {
                $ids[] = (int) $value->integer_value;
            }

            foreach ((array) $value->json_value as $optionId) {
                if (is_numeric($optionId)) {
                    $ids[] = (int) $optionId;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    public function saveOption(
        PropertyAttribute $attribute,
        ?PropertyAttributeOption $option,
        array $data
    ): PropertyAttributeOption {
        if ($option === null) {
            return $attribute->options()->create($data);
        }

        $option->update($data);

        return $option;
    }

    public function deleteOptions(PropertyAttribute $attribute, array $ids): bool
    {
        if ($ids === []) {
            return true;
        }

        return (bool) $attribute->options()->whereKey($ids)->delete();
    }
}
