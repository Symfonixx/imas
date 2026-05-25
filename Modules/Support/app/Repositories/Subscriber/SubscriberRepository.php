<?php

namespace Modules\Support\Repositories\Subscriber;

use Illuminate\Pagination\LengthAwarePaginator;

interface SubscriberRepository
{
    public function paginate(): LengthAwarePaginator;

    public function subscribe(string $email, string $ipAddress, string $lang): void;

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): bool;
}
