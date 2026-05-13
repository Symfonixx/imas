<?php

namespace Modules\Support\Repositories\ContactForm;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Support\Models\ContactForm;

interface ContactFormRepository
{
    public function paginate(): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ContactForm;

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): bool;
}
