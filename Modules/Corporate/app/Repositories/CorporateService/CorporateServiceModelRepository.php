<?php

namespace Modules\Corporate\Repositories\CorporateService;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Traits\ExceptionHandlerTrait;
use Modules\Core\Traits\FileTrait;
use Modules\Corporate\Models\CorporateService;

class CorporateServiceModelRepository implements CorporateServiceRepository
{
    use ExceptionHandlerTrait, FileTrait;

    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return CorporateService::select($columns)->latest()
            ->when(isset($filters['publish']) && $filters['publish'] !== null && $filters['publish'] !== '',
                fn ($q) => $q->where('status', $filters['publish']))
            ->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?CorporateService
    {
        return CorporateService::find($id, $columns);
    }

    public function store(array $data): mixed
    {
        return $this->execute(function () use ($data) {
            CorporateService::create($data);
        });
    }

    public function update(array $data, CorporateService $corporateService, bool $updateTranslations = false): mixed
    {
        return $this->execute(function () use ($data, $corporateService) {
            $corporateService->update($data);

            return true;
        });
    }

    public function deleteMulti(array $ids): ?bool
    {
        return $this->execute(function () use ($ids) {
            $rows = CorporateService::whereIn('id', $ids)->get(['image', 'meta_image']);
            $paths = $rows->pluck('image')->merge($rows->pluck('meta_image'))->filter()->unique()->values()->all();
            CorporateService::destroy($ids);
            $this->deleteFile($paths);

            return true;
        });
    }
}
