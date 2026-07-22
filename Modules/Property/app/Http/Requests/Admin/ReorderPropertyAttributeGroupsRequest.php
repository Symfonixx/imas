<?php

namespace Modules\Property\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeGroup;

class ReorderPropertyAttributeGroupsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('Property Management') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'groups' => ['present', 'array'],
            'groups.*.id' => ['required', 'integer', 'distinct', 'exists:property_attribute_groups,id'],
            'groups.*.attributes' => ['present', 'array'],
            'groups.*.attributes.*' => ['required', 'integer', 'exists:property_attributes,id'],
            'unassigned' => ['present', 'array'],
            'unassigned.*' => ['required', 'integer', 'distinct', 'exists:property_attributes,id'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $submittedGroupIds = collect($this->input('groups', []))
                    ->pluck('id')
                    ->map(static fn ($id): int => (int) $id)
                    ->all();
                $existingGroupIds = PropertyAttributeGroup::query()->orderBy('id')->pluck('id')->all();

                if (! $this->sameCompleteSet($submittedGroupIds, $existingGroupIds)) {
                    $validator->errors()->add('groups', __('Every attribute group must be represented exactly once.'));
                }

                $groupedAttributeIds = collect($this->input('groups', []))
                    ->flatMap(static fn (array $group): array => $group['attributes'] ?? [])
                    ->map(static fn ($id): int => (int) $id)
                    ->all();
                $unassignedIds = collect($this->input('unassigned', []))
                    ->map(static fn ($id): int => (int) $id)
                    ->all();
                $submittedAttributeIds = [...$groupedAttributeIds, ...$unassignedIds];
                $existingAttributeIds = PropertyAttribute::query()->orderBy('id')->pluck('id')->all();

                if (! $this->sameCompleteSet($submittedAttributeIds, $existingAttributeIds)) {
                    $validator->errors()->add(
                        'groups',
                        __('Every property attribute must be represented exactly once.')
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $groups = $this->input('groups', []);
        if (! is_array($groups)) {
            $groups = [];
        }

        foreach ($groups as &$group) {
            if (! is_array($group)) {
                continue;
            }

            $attributes = $group['attributes'] ?? [];
            if ($attributes === '' || $attributes === null) {
                $group['attributes'] = [];
            } elseif (! is_array($attributes)) {
                $group['attributes'] = [(int) $attributes];
            } else {
                $group['attributes'] = array_values($attributes);
            }
        }
        unset($group);

        $unassigned = $this->input('unassigned', []);
        if (! is_array($unassigned)) {
            $unassigned = [];
        } else {
            $unassigned = array_values($unassigned);
        }

        $this->merge([
            'groups' => array_values($groups),
            'unassigned' => $unassigned,
        ]);
    }

    /**
     * @param  list<int>  $submitted
     * @param  list<int>  $existing
     */
    private function sameCompleteSet(array $submitted, array $existing): bool
    {
        if (count($submitted) !== count(array_unique($submitted))) {
            return false;
        }

        sort($submitted);
        sort($existing);

        return $submitted === $existing;
    }
}
