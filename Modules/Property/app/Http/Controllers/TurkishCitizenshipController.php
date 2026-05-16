<?php

namespace Modules\Property\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
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

        return Inertia::render('Property::TurkishCitizenship', [
            'turkishCitizenship' => [
                'banner_url' => $this->storagePublicUrl($bannerPath),
                'content' => $this->seoString('turkish_citizenship_content'),
                'youtube_embed' => $this->seoString('turkish_citizenship_youtube_embed'),
                'meta_title' => $this->seoString('turkish_citizenship_meta_title'),
                'meta_description' => $this->seoString('turkish_citizenship_meta_description'),
                'meta_keywords' => $this->seoString('turkish_citizenship_meta_keywords'),
            ],
            'citizenshipProperties' => $citizenshipProperties,
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
