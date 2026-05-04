<?php

namespace Modules\Cms\Repositories\Slide;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Modules\Cms\Models\Slide;
use Modules\Core\Traits\ExceptionHandlerTrait;

class SlideModelRepository implements SlideRepository
{
    use ExceptionHandlerTrait;

    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return Slide::select($columns)
            ->when(isset($filters['publish']) && $filters['publish'] !== null && $filters['publish'] !== '',
                fn ($q) => $q->where('status', $filters['publish']))
            ->orderBy('rank')
            ->latest()
            ->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?Slide
    {
        return Slide::find($id, $columns);
    }

    public function store(array $data): mixed
    {
        return $this->execute(function () use ($data) {
            Slide::create($data);
        });
    }

    public function update(array $data, Slide $slide, bool $updateTranslations = false): mixed
    {
        return $this->execute(function () use ($data, $slide) {
            $slide->update($data);

            return true;
        });
    }

    public function deleteMulti(array $ids): ?bool
    {
        return $this->execute(function () use ($ids) {
            Slide::destroy($ids);

            return true;
        });
    }
}
