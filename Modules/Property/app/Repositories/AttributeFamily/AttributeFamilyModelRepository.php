<?php

namespace Modules\Property\Repositories\AttributeFamily;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Traits\ExceptionHandlerTrait;
use Modules\Property\Models\AttributeFamily;

class AttributeFamilyModelRepository implements AttributeFamilyRepository
{
    use ExceptionHandlerTrait;

    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return AttributeFamily::query()
            ->select($columns)
            ->orderBy('code')
            ->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?AttributeFamily
    {
        return AttributeFamily::query()->find($id, $columns);
    }

    public function store(array $data): mixed
    {
        return $this->execute(function () use ($data) {
            return AttributeFamily::query()->create($data);
        });
    }

    public function update(array $data, AttributeFamily $family, bool $updateTranslations = false): mixed
    {
        return $this->execute(function () use ($data, $family) {
            $family->update($data);

            return true;
        });
    }

    public function deleteMulti(array $ids): ?bool
    {
        return $this->execute(function () use ($ids) {
            AttributeFamily::destroy($ids);

            return true;
        });
    }
}
