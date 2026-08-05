<?php

namespace Modules\Property\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Base\Application\Seo\SeoDocumentService;
use Modules\Base\Application\Settings\SettingsApplicationService;
use Modules\Base\Models\Seo;
use Modules\Property\Models\Property;
use Modules\Property\Support\PropertyCardEagerLoads;
use Modules\Property\Support\PropertyListingCardSerializer;
use Modules\User\Enums\CmsStatus;

class TurkishCitizenshipController extends Controller
{
    public function __construct(
        private readonly SettingsApplicationService $settingsService,
    ) {}

    public function __invoke(): Response
    {
        $settings = $this->settingsService->allKeyValue();
        $bannerPath = trim((string) ($settings->get('turkish_citizenship_banner') ?? ''));

        $userId = auth()->id();

        $citizenshipProperties = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->where('is_citizenship_eligible', true)
            ->with(PropertyCardEagerLoads::relations())
            ->withFavoriteStateForUser($userId)
            ->latest('updated_at')
            ->limit(16)
            ->get()
            ->map(static fn (Property $property) => PropertyListingCardSerializer::toArray($property))
            ->values()
            ->all();

        $seoService = app(SeoDocumentService::class);
        $metaTitle = $this->seoString('turkish_citizenship_meta_title');
        $pageTitle = $metaTitle !== ''
            ? $metaTitle
            : $seoService->labelFromBaseLang('navBar.Turkish Citizenship', 'Turkish citizenship');

        $bannerUrl = $this->storagePublicUrl($bannerPath);

        return Inertia::render('Property::TurkishCitizenship', [
            'turkishCitizenship' => [
                'banner_url' => $bannerUrl,
                'content' => $this->seoString('turkish_citizenship_content'),
                'youtube_embed' => $this->seoString('turkish_citizenship_youtube_embed'),
                'meta_title' => $this->seoString('turkish_citizenship_meta_title'),
                'meta_description' => $this->seoString('turkish_citizenship_meta_description'),
                'meta_keywords' => $this->seoString('turkish_citizenship_meta_keywords'),
            ],
            'citizenshipProperties' => $citizenshipProperties,
            'contactStoreUrl' => route('support.contact-us.store'),
        ])->withViewData([
            'seo' => $seoService->documentSeo([
                'page_title' => $pageTitle,
                'description_keys' => [
                    'turkish_citizenship_meta_description',
                    'site_meta_description',
                    'website_desc',
                ],
                'keywords_keys' => [
                    'turkish_citizenship_meta_keywords',
                    'site_meta_keywords',
                    'website_keywords',
                ],
                'og_image' => $seoService->settingsImageUrl('turkish_citizenship_banner'),
                'canonical' => route('turkish-citizenship'),
            ]),
        ]);
    }

    private function seoString(string $key): string
    {
        $value = Seo::get($key, '');

        return is_string($value) ? $value : '';
    }

    private function storagePublicUrl(string $path): string
    {
        if ($path === '') {
            return asset('storage/default.jpg');
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
