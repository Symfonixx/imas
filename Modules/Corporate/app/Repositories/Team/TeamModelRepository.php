<?php

namespace Modules\Corporate\Repositories\Team;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Traits\ExceptionHandlerTrait;
use Modules\Core\Traits\FileTrait;
use Modules\Corporate\Models\Team;

class TeamModelRepository implements TeamRepository
{
    use ExceptionHandlerTrait, FileTrait;

    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return Team::query()
            ->select($columns)
            ->when(
                isset($filters['publish']) && $filters['publish'] !== null && $filters['publish'] !== '',
                fn ($q) => $q->where('status', $filters['publish'])
            )
            ->orderBy('rank')
            ->orderByDesc('id')
            ->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?Team
    {
        return Team::find($id, $columns);
    }

    public function store(array $data): mixed
    {
        return $this->execute(function () use ($data) {
            Team::create($data);
        });
    }

    public function update(array $data, Team $team, bool $updateTranslations = false): mixed
    {
        return $this->execute(function () use ($data, $team) {
            $team->update($data);

            return true;
        });
    }

    public function deleteMulti(array $ids): ?bool
    {
        return $this->execute(function () use ($ids) {
            $rows = Team::whereIn('id', $ids)->get(['avatar']);
            $paths = $rows->pluck('avatar')->filter()->unique()->values()->all();
            Team::destroy($ids);
            $this->deleteFile($paths);

            return true;
        });
    }
}
