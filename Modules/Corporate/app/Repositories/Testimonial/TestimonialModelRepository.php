<?php

namespace Modules\Corporate\Repositories\Testimonial;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Traits\ExceptionHandlerTrait;
use Modules\Core\Traits\FileTrait;
use Modules\Corporate\Models\Testimonial;

class TestimonialModelRepository implements TestimonialRepository
{
    use ExceptionHandlerTrait, FileTrait;

    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return Testimonial::query()
            ->select($columns)
            ->when(
                isset($filters['publish']) && $filters['publish'] !== null && $filters['publish'] !== '',
                fn ($q) => $q->where('status', $filters['publish'])
            )
            ->orderBy('rank')
            ->orderByDesc('id')
            ->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?Testimonial
    {
        return Testimonial::find($id, $columns);
    }

    public function store(array $data): mixed
    {
        return $this->execute(function () use ($data) {
            Testimonial::create($data);
        });
    }

    public function update(array $data, Testimonial $testimonial, bool $updateTranslations = false): mixed
    {
        return $this->execute(function () use ($data, $testimonial) {
            $testimonial->update($data);

            return true;
        });
    }

    public function deleteMulti(array $ids): ?bool
    {
        return $this->execute(function () use ($ids) {
            $rows = Testimonial::whereIn('id', $ids)->get(['avatar']);
            $paths = $rows->pluck('avatar')->filter()->unique()->values()->all();
            Testimonial::destroy($ids);
            $this->deleteFile($paths);

            return true;
        });
    }
}
