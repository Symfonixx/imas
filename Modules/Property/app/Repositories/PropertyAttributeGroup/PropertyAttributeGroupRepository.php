<?php

namespace Modules\Property\Repositories\PropertyAttributeGroup;

use Illuminate\Support\Collection;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeGroup;

interface PropertyAttributeGroupRepository
{
    /**
     * @return Collection<int, PropertyAttributeGroup>
     */
    public function allWithAttributes(): Collection;

    /**
     * @return Collection<int, PropertyAttribute>
     */
    public function unassignedAttributes(): Collection;

    public function create(array $data): PropertyAttributeGroup;

    public function update(PropertyAttributeGroup $group, array $data): bool;

    public function deleteMany(array $ids): bool;

    /**
     * @param  list<array{id: int, attributes: list<int>}>  $groups
     */
    public function replaceOrdering(array $groups): void;
}
