<?php

namespace Modules\Property\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Base\Application\Settings\SettingsApplicationService;
use Modules\Base\Models\Seo;
use Modules\Base\Support\FrontSeo;
use Modules\Base\Support\FrontViewData;
use Modules\Property\Models\Property;
use Modules\Property\Support\PropertyCardEagerLoads;
use Modules\Property\Support\PropertyListingCardSerializer;
use Modules\User\Enums\CmsStatus;

class TurkishCitizenshipController extends Controller
{
    public function __construct(
        private readonly SettingsApplicationService $settingsService,
        private readonly FrontViewData $frontViewData,
    ) {}

    public function __invoke(): View
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

        $turkishCitizenship = [
            'banner_url' => $this->storagePublicUrl($bannerPath),
            'content' => $this->seoString('turkish_citizenship_content'),
            'youtube_embed' => $this->seoString('turkish_citizenship_youtube_embed'),
            'meta_title' => $this->seoString('turkish_citizenship_meta_title'),
            'meta_description' => $this->seoString('turkish_citizenship_meta_description'),
            'meta_keywords' => $this->seoString('turkish_citizenship_meta_keywords'),
        ];

        $globals = $this->frontViewData->sharedGlobals();
        $localeSwitcher = $this->frontViewData->getLocaleSwitcher();
        $appName = $this->frontViewData->sharedAppName();
        $translations = $this->frontViewData->getTranslations();
        $sectionTitle = $turkishCitizenship['meta_title'] !== ''
            ? $turkishCitizenship['meta_title']
            : front_trans('navBar.Turkish Citizenship', $translations);

        return view('property::front.turkish-citizenship', [
            'turkishCitizenship' => $turkishCitizenship,
            'citizenshipProperties' => $citizenshipProperties,
            'contactStoreUrl' => route('support.contact-us.store'),
            'seo' => FrontSeo::make([
                'title' => $sectionTitle !== '' && $appName !== ''
                    ? "{$sectionTitle} | {$appName}"
                    : ($sectionTitle ?: $appName),
                'description' => $turkishCitizenship['meta_description'] ?: null,
                'keywords' => $turkishCitizenship['meta_keywords'] ?: null,
                'canonical' => route('turkish-citizenship'),
                'image' => $turkishCitizenship['banner_url'] ?: null,
            ], $globals, $localeSwitcher, $appName),
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
