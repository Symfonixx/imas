<?php

namespace Modules\Property\Repositories\PropertyType;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Traits\ExceptionHandlerTrait;
use Modules\Property\Models\PropertyType;

class PropertyTypeModelRepository implements PropertyTypeRepository
{
    use ExceptionHandlerTrait;

    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return PropertyType::query()
            ->select($columns)
            ->orderBy('slug')
            ->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?PropertyType
    {
        return PropertyType::query()->find($id, $columns);
    }

    public function store(array $data): mixed
    {
        return $this->execute(function () use ($data) {
            PropertyType::query()->create($data);
        });
    }

    public function update(array $data, PropertyType $propertyType, bool $updateTranslations = false): mixed
    {
        return $this->execute(function () use ($data, $propertyType) {
            $propertyType->update($data);

            return true;
        });
    }

    public function deleteMulti(array $ids): ?bool
    {
        return $this->execute(function () use ($ids) {
            PropertyType::destroy($ids);

            return true;
        });
    }
}
