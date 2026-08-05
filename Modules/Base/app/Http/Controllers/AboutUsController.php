<?php

namespace Modules\Base\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Base\Application\Seo\SeoDocumentService;
use Modules\Base\Models\Seo;
use Modules\Property\Models\Property;
use Modules\Property\Support\PropertyCardEagerLoads;
use Modules\Property\Support\PropertyListingCardSerializer;
use Modules\User\Enums\CmsStatus;

class AboutUsController extends Controller
{
    public function __invoke(): Response
    {
        $userId = auth()->id();

        $featuredProperties = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->where('is_featured', true)
            ->with(PropertyCardEagerLoads::relations())
            ->withFavoriteStateForUser($userId)
            ->latest('updated_at')
            ->limit(8)
            ->get()
            ->map(static fn (Property $property) => PropertyListingCardSerializer::toArray($property))
            ->values()
            ->all();

        $seoService = app(SeoDocumentService::class);
        $metaTitle = $this->seoString('about_us_meta_title');
        $pageTitle = $metaTitle !== ''
            ? $metaTitle
            : $seoService->labelFromBaseLang('about_us.title', 'About us');

        return Inertia::render('Base::AboutUs', [
            'aboutUs' => [
                'content' => $this->seoString('about_us_content'),
                'youtube_embed' => $this->seoString('about_us_youtube_embed'),
                'meta_title' => $this->seoString('about_us_meta_title'),
                'meta_description' => $this->seoString('about_us_meta_description'),
                'meta_keywords' => $this->seoString('about_us_meta_keywords'),
            ],
            'featuredProperties' => $featuredProperties,
        ])->withViewData([
            'seo' => $seoService->documentSeo([
                'page_title' => $pageTitle,
                'description_keys' => [
                    'about_us_meta_description',
                    'site_meta_description',
                    'website_desc',
                ],
                'keywords_keys' => [
                    'about_us_meta_keywords',
                    'site_meta_keywords',
                    'website_keywords',
                ],
                'og_image' => $seoService->settingsImageUrl('about_us_banner'),
                'canonical' => route('about-us'),
            ]),
        ]);
    }

    private function seoString(string $key): string
    {
        $value = Seo::get($key, '');

        return is_string($value) ? $value : '';
    }
}
