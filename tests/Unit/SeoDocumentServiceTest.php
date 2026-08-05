<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Base\Application\Seo\SeoDocumentService;
use Modules\Base\Models\Seo;
use Tests\TestCase;

class SeoDocumentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('seo_entries');
    }

    public function test_document_seo_uses_global_title_template_as_is(): void
    {
        Seo::set('website_name', 'IMas', false);
        Seo::set('main_title', 'IMas | Luxury Homes', false);
        Seo::set('website_desc', 'Find your next home.', false);
        Cache::forget('seo_entries');

        $seo = app(SeoDocumentService::class)->documentSeo();

        $this->assertSame('IMas | Luxury Homes', $seo['title']);
        $this->assertSame('Find your next home.', $seo['description']);
        $this->assertSame('website', $seo['og_type']);
    }

    public function test_document_seo_appends_site_name_for_page_title(): void
    {
        Seo::set('website_name', 'IMas', false);
        Cache::forget('seo_entries');

        $seo = app(SeoDocumentService::class)->documentSeo([
            'page_title' => 'Catalogue',
            'description' => 'Browse listings.',
            'keywords' => ['a', 'b'],
            'canonical' => 'https://example.test/property',
        ]);

        $this->assertSame('Catalogue | IMas', $seo['title']);
        $this->assertSame('Browse listings.', $seo['description']);
        $this->assertSame('a, b', $seo['keywords']);
        $this->assertSame('https://example.test/property', $seo['canonical']);
    }

    public function test_document_seo_respects_explicit_title_override(): void
    {
        Seo::set('website_name', 'IMas', false);
        Cache::forget('seo_entries');

        $seo = app(SeoDocumentService::class)->documentSeo([
            'title' => 'Penthouse in Istanbul | IMas',
            'og_type' => 'article',
            'robots' => 'noindex, nofollow',
        ]);

        $this->assertSame('Penthouse in Istanbul | IMas', $seo['title']);
        $this->assertSame('article', $seo['og_type']);
        $this->assertSame('noindex, nofollow', $seo['robots']);
    }
}
