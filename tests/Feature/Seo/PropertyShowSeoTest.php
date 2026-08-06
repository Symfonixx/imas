<?php

namespace Tests\Feature\Seo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Modules\Base\Models\Seo;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyType;
use Modules\User\Enums\CmsStatus;
use Tests\TestCase;

class PropertyShowSeoTest extends TestCase
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

        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleCookieRedirect::class,
            LocaleSessionRedirect::class,
        ]);

        Seo::set('website_name', 'IMas Test Site', false);
        Cache::forget('seo_entries');
    }

    public function test_show_page_html_includes_every_admin_seo_field(): void
    {
        $property = $this->property([
            'meta_title' => 'Luxury Bosphorus Residences',
            'meta_description' => 'Sea-view apartments in Kadikoy with flexible payment plans.',
            'meta_keywords' => ['istanbul', 'sea view', 'citizenship'],
            'meta_img' => 'seo/property-meta.jpg',
            'schema' => '{"@context":"https://schema.org","@type":"FAQPage","name":"Payment plans"}',
        ]);

        $response = $this->get(route('property.show', $property->url_key));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertIsString($html);

        $this->assertStringContainsString(
            '<title inertia>Luxury Bosphorus Residences | IMas Test Site</title>',
            $html,
        );
        $this->assertStringContainsString(
            '<meta inertia="description" name="description" content="Sea-view apartments in Kadikoy with flexible payment plans.">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta inertia="keywords" name="keywords" content="istanbul, sea view, citizenship">',
            $html,
        );

        $metaImageUrl = asset('storage/seo/property-meta.jpg');
        $this->assertStringContainsString(
            '<meta inertia="og:image" property="og:image" content="'.e($metaImageUrl).'">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta inertia="twitter:image" name="twitter:image" content="'.e($metaImageUrl).'">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta inertia="twitter:card" name="twitter:card" content="summary_large_image">',
            $html,
        );
        $this->assertStringContainsString(
            '<link inertia="canonical" rel="canonical" href="'.e(route('property.show', $property->url_key)).'">',
            $html,
        );

        $this->assertStringContainsString('<script inertia="jsonld-custom" type="application/ld+json">', $html);
        $this->assertStringContainsString('"@type":"FAQPage"', $html);

        $this->assertStringContainsString(
            '<script inertia="jsonld-real-estate-listing" type="application/ld+json">',
            $html,
        );
        $this->assertStringContainsString('"@type":"RealEstateListing"', $html);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
    }

    public function test_show_page_falls_back_to_thumbnail_and_skips_malformed_schema(): void
    {
        $property = $this->property([
            'meta_title' => 'Fallback Residences',
            'schema' => '{not valid json',
        ]);

        $response = $this->get(route('property.show', $property->url_key));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertIsString($html);

        $this->assertStringNotContainsString('jsonld-custom', $html);
        $this->assertStringContainsString(
            '<meta inertia="og:image" property="og:image" content="'.e(asset('storage/properties/hero.jpg')).'">',
            $html,
        );
        $this->assertStringContainsString('"@type":"RealEstateListing"', $html);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function property(array $metadata): Property
    {
        $city = Location::query()->create([
            'name' => ['en' => 'Istanbul'],
            'type' => LocationType::City,
        ]);
        $district = Location::query()->create([
            'name' => ['en' => 'Kadikoy'],
            'type' => LocationType::Municipality,
            'parent_id' => $city->id,
        ]);

        return Property::query()->create([
            'thumbnail' => 'properties/hero.jpg',
            'project_code' => 'TRK-SEO-0001',
            'url_key' => 'luxury-bosphorus-residences',
            'title' => ['en' => 'Bosphorus Residences'],
            'project_name' => ['en' => 'Bosphorus Residences'],
            'overview' => ['en' => '<p>Sea-view apartments.</p>'],
            'location_id' => $district->id,
            'property_type_id' => PropertyType::factory()->create()->id,
            'price' => 250000,
            'min_area' => 90,
            'max_area' => 180,
            'lat' => 41.0,
            'lng' => 29.0,
            'status' => CmsStatus::PUBLISHED,
            'metadata' => $metadata,
        ]);
    }
}
