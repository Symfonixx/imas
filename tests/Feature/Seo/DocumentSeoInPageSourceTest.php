<?php

namespace Tests\Feature\Seo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Modules\Base\Models\Seo;
use Modules\Cms\Models\Blog;
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
            LocaleCookieRedirect::class,
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
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
        $this->assertStringContainsString(
            '<meta inertia="og:site_name" property="og:site_name" content="IMas Test Site">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta inertia="og:locale" property="og:locale" content="en_US">',
            $html,
        );
        $this->assertStringContainsString('rel="alternate" hreflang=', $html);
        $this->assertStringContainsString(
            '<meta name="theme-color" content="#0a1526">',
            $html,
        );
        $this->assertStringContainsString(
            '<script inertia="jsonld-organization" type="application/ld+json">',
            $html,
        );
        $this->assertStringContainsString(
            '<script inertia="jsonld-website" type="application/ld+json">',
            $html,
        );
        $this->assertStringContainsString('"@type":"Organization"', $html);
        $this->assertStringContainsString('"@type":"WebSite"', $html);
    }

    public function test_password_request_html_includes_noindex_robots(): void
    {
        Seo::set('website_name', 'IMas Test Site', false);
        Cache::forget('seo_entries');

        $response = $this->get(route('password.request'));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertIsString($html);
        $this->assertStringContainsString(
            '<meta inertia="robots" name="robots" content="noindex, nofollow">',
            $html,
        );
        $this->assertStringContainsString('Forgot Password | IMas Test Site', $html);
    }

    public function test_blog_show_html_includes_article_meta_and_json_ld(): void
    {
        Seo::set('website_name', 'IMas Test Site', false);
        Cache::forget('seo_entries');

        $blog = Blog::factory()->published()->create([
            'title' => ['en' => 'Istanbul Investment Guide'],
            'slug' => 'istanbul-investment-guide',
            'meta_title' => ['en' => 'Istanbul Investment Guide'],
            'meta_description' => ['en' => 'How to buy property in Istanbul safely.'],
            'meta_keywords' => ['en' => 'istanbul, investment'],
        ]);

        $response = $this->get(route('blog.show', $blog->slug));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertIsString($html);

        $this->assertStringContainsString(
            '<meta inertia="og:type" property="og:type" content="article">',
            $html,
        );
        $this->assertStringContainsString(
            'property="article:published_time"',
            $html,
        );
        $this->assertStringContainsString(
            '<script inertia="jsonld-article" type="application/ld+json">',
            $html,
        );
        $this->assertStringContainsString('"@type":"Article"', $html);
        $this->assertStringContainsString('How to buy property in Istanbul safely.', $html);
    }

    public function test_property_listings_html_includes_description_and_absolute_canonical(): void
    {
        Seo::set('website_name', 'IMas Test Site', false);
        Cache::forget('seo_entries');

        $response = $this->get(route('property.index'));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertIsString($html);

        $this->assertStringContainsString('Property listings | IMas Test Site', $html);
        $this->assertStringContainsString(
            'name="description"',
            $html,
        );
        $this->assertStringContainsString(
            'Browse property listings for sale',
            $html,
        );
        $this->assertStringContainsString(
            'property="og:description"',
            $html,
        );

        $canonical = route('property.index');
        $this->assertStringContainsString(
            'rel="canonical" href="'.$canonical.'"',
            $html,
        );
        $this->assertStringContainsString(
            'property="og:url" content="'.$canonical.'"',
            $html,
        );
        $this->assertStringNotContainsString(
            'rel="canonical" href="/en/property"',
            $html,
        );

        // Blade SEO + SSR head must not both appear (SSR off in this test → single Blade title).
        $this->assertSame(1, substr_count($html, '<title inertia>'));
    }
}
