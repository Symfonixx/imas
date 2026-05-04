<?php

namespace Modules\Corporate\Repositories\Testimonial;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Corporate\Models\Testimonial;

interface TestimonialRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?Testimonial;

    public function store(array $data): mixed;

    public function update(array $data, Testimonial $testimonial, bool $updateTranslations = false): mixed;

    public function deleteMulti(array $ids): ?bool;
}
