<?php

use Illuminate\Support\Facades\Route;
use Modules\Base\Http\Controllers\RssController;
use Modules\Base\Http\Controllers\SitemapController;
use Modules\Base\Models\Settings;

// Machine-readable endpoints (sitemap/feed/robots). Stateless public routes —
// strip session/cookie/CSRF so responses stay cacheable and Chrome can render XML.
Route::withoutMiddleware([
    'Illuminate\Cookie\Middleware\EncryptCookies',
    'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
    'Illuminate\Session\Middleware\StartSession',
    'Illuminate\View\Middleware\ShareErrorsFromSession',
    'Illuminate\Foundation\Http\Middleware\PreventRequestForgery',
    'Illuminate\Foundation\Http\Middleware\ValidateCsrfToken',
    'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken',
])->group(function () {
    Route::get('/robots.txt', function () {
        $default = "User-agent: *\nDisallow:\n";
        $content = Settings::get('robots_txt', $default) ?: $default;
        $sitemapUrl = url('/sitemap.xml');

        if (! str_contains($content, 'Sitemap:')) {
            $content = rtrim($content)."\n\nSitemap: {$sitemapUrl}\n";
        }

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    })->name('robots');

    Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

    Route::get('/feed.xml', [RssController::class, 'show'])->name('feed');
    Route::get('/feed/{locale}.xml', [RssController::class, 'show'])
        ->where('locale', '[A-Za-z]{2}')
        ->name('feed.locale');
});
