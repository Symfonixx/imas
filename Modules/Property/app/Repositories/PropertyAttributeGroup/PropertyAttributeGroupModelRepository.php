<?php

namespace Modules\Property\Repositories\PropertyAttributeGroup;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeGroup;

class PropertyAttributeGroupModelRepository implements PropertyAttributeGroupRepository
{
    public function allWithAttributes(): Collection
    {
        return PropertyAttributeGroup::query()
            ->with(['attributes' => fn ($query) => $query->select([
                'property_attributes.id',
                'property_attributes.code',
                'property_attributes.name',
                'property_attributes.is_active',
            ])])
            ->ordered()
            ->get();
    }

    public function unassignedAttributes(): Collection
    {
        return PropertyAttribute::query()
            ->whereDoesntHave('groups')
            ->ordered()
            ->get(['id', 'code', 'name', 'is_active']);
    }

    public function create(array $data): PropertyAttributeGroup
    {
        return PropertyAttributeGroup::query()->create($data);
    }

    public function update(PropertyAttributeGroup $group, array $data): bool
    {
        return $group->update($data);
    }

    public function deleteMany(array $ids): bool
    {
        return (bool) PropertyAttributeGroup::query()->whereKey($ids)->delete();
    }

    public function replaceOrdering(array $groups): void
    {
        DB::table('property_attribute_group_mappings')->delete();

        $mappings = [];
        foreach ($groups as $groupPosition => $group) {
            PropertyAttributeGroup::query()
                ->whereKey($group['id'])
                ->update(['position' => $groupPosition]);

            foreach ($group['attributes'] as $attributePosition => $attributeId) {
                $mappings[] = [
                    'group_id' => $group['id'],
                    'attribute_id' => $attributeId,
                    'position' => $attributePosition,
                ];
            }
        }

        if ($mappings !== []) {
            DB::table('property_attribute_group_mappings')->insert($mappings);
        }
    }
}
