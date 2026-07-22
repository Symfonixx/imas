<?php

namespace Modules\User\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\User\Application\Dashboard\DashboardAnalyticsService;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardAnalyticsService $analytics) {}

    public function index()
    {
        $this->setActive('dashboard');

        return view('user::admin.dashboard.index', [
            'analytics' => $this->analytics->get(),
        ]);
    }
}
