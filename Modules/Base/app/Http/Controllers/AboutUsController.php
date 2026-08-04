<?php

namespace Modules\Base\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Base\Models\Seo;
use Modules\Base\Support\FrontSeo;
use Modules\Base\Support\FrontViewData;
use Modules\Property\Models\Property;
use Modules\Property\Support\PropertyCardEagerLoads;
use Modules\Property\Support\PropertyListingCardSerializer;
use Modules\User\Enums\CmsStatus;

class AboutUsController extends Controller
{
    public function __construct(private readonly FrontViewData $frontViewData) {}

    public function __invoke(): View
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

        $aboutUs = [
            'content' => $this->seoString('about_us_content'),
            'youtube_embed' => $this->seoString('about_us_youtube_embed'),
            'meta_title' => $this->seoString('about_us_meta_title'),
            'meta_description' => $this->seoString('about_us_meta_description'),
            'meta_keywords' => $this->seoString('about_us_meta_keywords'),
        ];

        $globals = $this->frontViewData->sharedGlobals();
        $localeSwitcher = $this->frontViewData->getLocaleSwitcher();
        $appName = $this->frontViewData->sharedAppName();
        $translations = $this->frontViewData->getTranslations();
        $sectionTitle = $aboutUs['meta_title'] !== ''
            ? $aboutUs['meta_title']
            : front_trans('about_us.title', $translations);

        return view('base::front.about-us', [
            'aboutUs' => $aboutUs,
            'featuredProperties' => $featuredProperties,
            'seo' => FrontSeo::make([
                'title' => $sectionTitle !== '' && $appName !== ''
                    ? "{$sectionTitle} | {$appName}"
                    : ($sectionTitle ?: $appName),
                'description' => $aboutUs['meta_description'] ?: null,
                'keywords' => $aboutUs['meta_keywords'] ?: null,
                'canonical' => route('about-us'),
                'image' => $globals['media']['about_us_banner'] ?? null,
            ], $globals, $localeSwitcher, $appName),
        ]);
    }

    private function seoString(string $key): string
    {
        $value = Seo::get($key, '');

        return is_string($value) ? $value : '';
    }
}
