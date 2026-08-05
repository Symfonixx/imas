<?php

namespace Tests\Feature\Seo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Base\Models\Seo;
use Tests\TestCase;

class DocumentSeoInPageSourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('seo_entries');

        config([
            'inertia.ssr.enabled' => false,
            'laravellocalization.useAcceptLanguageHeader' => false,
        ]);

        // Avoid locale cookie/session redirects so `/` returns the Inertia HTML shell.
        $this->withoutMiddleware([
            \Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect::class,
            \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
        ]);
    }

    public function test_home_page_html_includes_blade_seo_meta_tags(): void
    {
        Seo::set('website_name', 'IMas Test Site', false);
        Seo::set('website_desc', 'Best properties in town.', false);
        Seo::set('website_keywords', 'real estate, turkey, imas', false);
        Seo::set('main_title', 'IMas Test Site | Dream Homes', false);

        Cache::forget('seo_entries');

        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();
        $this->assertIsString($html);
        $this->assertStringContainsString('<title inertia>IMas Test Site | Dream Homes</title>', $html);
        $this->assertStringContainsString(
            '<meta inertia="description" name="description" content="Best properties in town.">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta inertia="keywords" name="keywords" content="real estate, turkey, imas">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta inertia="og:title" property="og:title" content="IMas Test Site | Dream Homes">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta inertia="og:description" property="og:description" content="Best properties in town.">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta inertia="og:type" property="og:type" content="website">',
            $html,
        );
    }
}
