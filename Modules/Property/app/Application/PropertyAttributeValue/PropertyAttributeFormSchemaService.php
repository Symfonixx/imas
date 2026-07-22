<?php

namespace Modules\Property\Application\PropertyAttributeValue;

use Illuminate\Support\Collection;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeGroup;
use Modules\Property\Models\PropertyAttributeValue;

final class PropertyAttributeFormSchemaService
{
    /**
     * @return Collection<int, array{id: int, name: string, position: int, attributes: Collection<int, array<string, mixed>>}>
     */
    public function forProperty(?Property $property = null): Collection
    {
        if ($property === null) {
            return collect();
        }

        $groupIds = $property->relationLoaded('attributeGroups')
            ? $property->attributeGroups->pluck('id')->map(fn ($id) => (int) $id)->all()
            : $property->attributeGroups()->pluck('property_attribute_groups.id')->map(fn ($id) => (int) $id)->all();

        return $this->forGroups($groupIds, $property);
    }

    /**
     * @param  list<int>  $groupIds
     * @return Collection<int, array{id: int, name: string, position: int, attributes: Collection<int, array<string, mixed>>}>
     */
    public function forGroups(array $groupIds, ?Property $property = null): Collection
    {
        $groupIds = array_values(array_unique(array_filter(
            array_map('intval', $groupIds),
            static fn (int $id): bool => $id > 0
        )));

        if ($groupIds === []) {
            return collect();
        }

        $values = $property === null
            ? collect()
            : $property->attributeValues()
                ->get()
                ->keyBy('attribute_id');

        $groups = PropertyAttributeGroup::query()
            ->active()
            ->whereIn('id', $groupIds)
            ->ordered()
            ->with(['attributes' => fn ($query) => $query
                ->where('property_attributes.is_active', true)
                ->with(['options' => fn ($options) => $options->ordered()])])
            ->get()
            ->sortBy(fn (PropertyAttributeGroup $group) => array_search($group->id, $groupIds, true))
            ->values();

        return $groups->map(fn (PropertyAttributeGroup $group): array => [
            'id' => $group->id,
            'name' => $group->name,
            'position' => $group->position,
            'attributes' => $group->attributes->map(
                fn (PropertyAttribute $attribute): array => [
                    'id' => $attribute->id,
                    'code' => $attribute->code,
                    'name' => $attribute->name,
                    'help_text' => $attribute->help_text,
                    'type' => $attribute->type->value,
                    'is_required' => $attribute->is_required,
                    'is_unique' => $attribute->is_unique,
                    'validation' => $attribute->validation,
                    'regex' => $attribute->regex,
                    'default_value' => data_get($attribute->default_value, 'value'),
                    'value' => $this->valueOf($attribute, $values->get($attribute->id)),
                    'options' => $attribute->options,
                ]
            )->values(),
        ]);
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    public function activeGroupOptions(): array
    {
        return PropertyAttributeGroup::query()
            ->active()
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (PropertyAttributeGroup $group): array => [
                'id' => $group->id,
                'name' => $group->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, name: string, position: int, attributes: Collection<int, array<string, mixed>>}|null
     */
    public function forGroup(?int $groupId, ?Property $property = null): ?array
    {
        if ($groupId === null || $groupId <= 0) {
            return null;
        }

        return $this->forGroups([$groupId], $property)->first();
    }

    private function valueOf(PropertyAttribute $attribute, ?PropertyAttributeValue $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return $value->{$attribute->type->valueColumn()};
    }
}
