<?php

namespace Modules\Corporate\Repositories\Team;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Corporate\Models\Team;

interface TeamRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?Team;

    public function store(array $data): mixed;

    public function update(array $data, Team $team, bool $updateTranslations = false): mixed;

    public function deleteMulti(array $ids): ?bool;
}
