<?php

namespace Modules\Property\Repositories\Attribute;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Traits\ExceptionHandlerTrait;
use Modules\Property\Models\PropertyAttribute;

class AttributeModelRepository implements AttributeRepository
{
    use ExceptionHandlerTrait;

    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return PropertyAttribute::query()
            ->select($columns)
            ->when(
                isset($filters['type']) && $filters['type'] !== null && $filters['type'] !== '',
                fn ($q) => $q->where('type', $filters['type'])
            )
            ->orderBy('code')
            ->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?PropertyAttribute
    {
        return PropertyAttribute::query()->find($id, $columns);
    }

    public function store(array $data): mixed
    {
        return $this->execute(function () use ($data) {
            PropertyAttribute::query()->create($data);
        });
    }

    public function update(array $data, PropertyAttribute $attribute, bool $updateTranslations = false): mixed
    {
        return $this->execute(function () use ($data, $attribute) {
            $attribute->update($data);

            return true;
        });
    }

    public function deleteMulti(array $ids): ?bool
    {
        return $this->execute(function () use ($ids) {
            PropertyAttribute::destroy($ids);

            return true;
        });
    }
}
