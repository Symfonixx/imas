<?php

namespace Modules\Cms\Repositories\Slide;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\Slide;

interface SlideRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?Slide;

    public function store(array $data): mixed;

    public function update(array $data, Slide $slide, bool $updateTranslations = false): mixed;

    public function deleteMulti(array $ids): ?bool;
}
