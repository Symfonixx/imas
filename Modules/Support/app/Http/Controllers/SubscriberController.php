<?php

namespace Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\Support\Http\Requests\StoreSubscriberRequest;
use Modules\Support\Repositories\Subscriber\SubscriberRepository;

class SubscriberController extends Controller
{
    public function __construct(
        private readonly SubscriberRepository $subscriberRepository,
    ) {}

    public function store(StoreSubscriberRequest $request): RedirectResponse
    {
        $this->subscriberRepository->subscribe(
            email: $request->validated('email'),
            ipAddress: $request->ip() ?? '',
            lang: app()->getLocale(),
        );

        return redirect()->back();
    }
}
