<?php

namespace Modules\Cms\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Cms\Http\Controllers\PageController;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Cms';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapAdminRoutes();
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')->prefix('api')->name('api.')->group(module_path($this->name, '/routes/api.php'));
    }

    protected function mapWebRoutes(): void
    {
        $name = $this->name;
        Route::group([
            'prefix' => LaravelLocalization::setLocale(),
            'middleware' => ['localeCookieRedirect', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
        ], static function () use ($name) {
            Route::middleware('web')->group(module_path($name, '/routes/web.php'));
        });

        $this->registerPageCatchAllRoute();
    }

    /**
     * Register after all modules so static paths (e.g. contact-us) are not captured.
     */
    private function registerPageCatchAllRoute(): void
    {
        $this->app->booted(function (): void {
            Route::group([
                'prefix' => LaravelLocalization::setLocale(),
                'middleware' => ['localeCookieRedirect', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
            ], function (): void {
                Route::middleware('web')
                    ->get('/{slug}', [PageController::class, 'show'])
                    ->where('slug', '[A-Za-z0-9\-]+')
                    ->name('page.show');
            });
        });
    }

    protected function mapAdminRoutes(): void
    {
        $name = $this->name;
        Route::group([
            'prefix' => LaravelLocalization::setLocale(),
            'middleware' => ['localeCookieRedirect', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
        ], static function () use ($name) {
            Route::prefix('admin')
                ->name('admin.')
                ->middleware(['web', 'auth', 'is_admin'])
                ->group(module_path($name, '/routes/admin.php'));

        });
    }
}
