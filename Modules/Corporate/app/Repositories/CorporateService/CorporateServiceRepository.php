<?php

namespace Modules\Corporate\Repositories\CorporateService;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Corporate\Models\CorporateService;

interface CorporateServiceRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?CorporateService;

    public function store(array $data): mixed;

    public function update(array $data, CorporateService $corporateService, bool $updateTranslations = false): mixed;

    public function deleteMulti(array $ids): ?bool;
}
