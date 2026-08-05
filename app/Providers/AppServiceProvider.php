<?php

namespace App\Providers;

use App\Ssr\ImasHttpGateway;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Inertia\Ssr\Gateway;
use Modules\Base\Application\Seo\SeoDocumentService;

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

        // Default document SEO for the Inertia root layout (View Page Source).
        // Controllers may override via Inertia::render(...)->withViewData(['seo' => ...]).
        View::composer('app', function ($view): void {
            if ($view->offsetExists('seo')) {
                return;
            }

            $view->with('seo', app(SeoDocumentService::class)->documentSeo());
        });
    }
}
