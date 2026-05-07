<?php

namespace Modules\Property\Repositories\Location;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Traits\ExceptionHandlerTrait;
use Modules\Property\Models\Location;

class LocationModelRepository implements LocationRepository
{
    use ExceptionHandlerTrait;

    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return Location::query()
            ->select($columns)
            ->with('parent:id,name')
            ->when(
                isset($filters['type']) && $filters['type'] !== null && $filters['type'] !== '',
                fn ($q) => $q->where('type', $filters['type'])
            )
            ->orderByRaw('parent_id is null desc')
            ->orderBy('id')
            ->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?Location
    {
        return Location::query()->find($id, $columns);
    }

    public function store(array $data): mixed
    {
        return $this->execute(function () use ($data) {
            Location::query()->create($data);
        });
    }

    public function update(array $data, Location $location, bool $updateTranslations = false): mixed
    {
        return $this->execute(function () use ($data, $location) {
            $location->update($data);

            return true;
        });
    }

    public function deleteMulti(array $ids): ?bool
    {
        return $this->execute(function () use ($ids) {
            Location::destroy($ids);

            return true;
        });
    }
}
