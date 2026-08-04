<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;
use Modules\Base\Http\Controllers\RssController;
use Modules\Base\Http\Controllers\SitemapController;
use Modules\Base\Models\Settings;

// Machine-readable endpoints (sitemap/feed/robots). These are stateless, public,
// and NOT Inertia pages, so we strip the session/cookie/CSRF/Inertia middleware.
//
// Why: leaving them in the stateful "web" stack makes every response emit
// Set-Cookie (session + XSRF) and "Cache-Control: no-cache, private". Chrome then
// refuses to apply its built-in XML tree viewer and instead renders the document
// as unstyled/flattened inline text — which looks like broken HTML in the browser
// even though the bytes are valid XML. A cookie-less, cacheable response (matching
// a plain static .xml file) renders as the proper XML tree. Stripping this stack
// also stops Inertia turning X-Inertia requests into a 409 HTML "force reload" and
// avoids running shared-data queries on every crawler hit.
Route::withoutMiddleware([
    HandleInertiaRequests::class,
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
