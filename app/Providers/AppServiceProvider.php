<?php

namespace App\Providers;

use App\Ssr\ImasHttpGateway;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Inertia\Ssr\Gateway;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Gateway::class, ImasHttpGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        $ssrUrl = rtrim((string) config('inertia.ssr.url', 'http://127.0.0.1:13714'), '/');
        Http::allowStrayRequests([
            $ssrUrl.'/*',
            $ssrUrl.'/render',
            $ssrUrl.'/health',
        ]);
    }
}
