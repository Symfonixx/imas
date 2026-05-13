<?php

namespace App\Http\Middleware;

use App;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Module;
use Modules\Base\Models\Seo;
use Modules\Base\Repositories\Settings\SettingsRepository;

class HandleInertiaRequests extends Middleware
{
    public function __construct(private readonly SettingsRepository $settingsRepository) {}

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {

        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'csrf' => csrf_token(),
            'asset_path' => asset('/'),
            'theme_url' => asset('theme/findhouses'),
            'storage_path' => asset('storage') . '/',
            'locale' => App::currentLocale(),
            'text_direction' => App::getLocale() === 'ar' ? 'rtl' : 'ltr',
            'translations' => $this->getTranslations(),
            'locale_switcher' => fn() => $this->getLocaleSwitcher(),
            'settings' => fn() => $this->sharedSettingsFlat(),
            'globals' => fn() => $this->sharedGlobals(),
            'auth' => fn() => $this->sharedAuthPayload($request),
        ]);
    }

    /**
     * Front-office auth: full {@see $user->name}, compact {@see authNavDisplayName()}
     * for nav, public avatar URLs via {@see \App\Models\User::getAvatarAttribute()}.
     *
     * @return array<string, mixed>|null
     */
    protected function sharedAuthPayload(Request $request): ?array
    {
        $user = $request->user();
        if ($user === null) {
            return null;
        }

        $avatarUrl = $user->avatar;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'nav_display_name' => $this->authNavDisplayName((string) $user->name),
            'email' => $user->email,
            'type' => $user->type,
            'img' => $avatarUrl,
            'avatar' => $avatarUrl,
        ];
    }

    /**
     * Navbar label: one word → full name; two or more words → first character
     * of each word joined (Unicode-safe), e.g. "Ahmed Sanad" → "AS".
     */
    protected function authNavDisplayName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false || $parts === []) {
            return $name;
        }

        $count = count($parts);
        if ($count === 1) {
            return $parts[0];
        }

        $initials = '';
        foreach ($parts as $part) {
            $initials .= mb_substr($part, 0, 1);
        }

        return mb_strtoupper($initials);
    }

    /**
     * Raw settings key => value from storage (same keys as admin Website Configurations),
     * plus contact_* aliases used by front-office Vue layouts.
     *
     * @return array<string, mixed>
     */
    protected function sharedSettingsFlat(): array
    {
        /** @var array<string, mixed> $flat */
        $flat = $this->settingsRepository->allKeyValue()->all();

        $flat['contact_phone'] = $flat['phone'] ?? ($flat['contact_phone'] ?? '');
        $flat['contact_email'] = $flat['email'] ?? ($flat['contact_email'] ?? '');
        $flat['contact_address'] = $flat['address'] ?? ($flat['contact_address'] ?? '');

        return $flat;
    }

    /**
     * Structured site-wide config for Inertia (contact, social, media URLs, SEO, about copy).
     *
     * @return array<string, mixed>
     */
    protected function sharedGlobals(): array
    {
        /** @var array<string, mixed> $settings */
        $settings = $this->settingsRepository->allKeyValue()->all();

        /** @var array<string, mixed> $seo */
        $seo = Seo::allLocalizedKeyValue();

        return [
            'contact' => [
                'phone' => (string) ($settings['phone'] ?? ''),
                'email' => (string) ($settings['email'] ?? ''),
                'address' => (string) ($settings['address'] ?? ''),
                'map' => (string) ($settings['map'] ?? ''),
            ],
            'social' => [
                'whatsapp' => (string) ($settings['whatsapp'] ?? ''),
                'facebook' => (string) ($settings['facebook'] ?? ''),
                'instagram' => (string) ($settings['instagram'] ?? ''),
                'youtube' => (string) ($settings['youtube'] ?? ''),
                'twitter' => (string) ($settings['twitter'] ?? ''),
                'tiktok' => (string) ($settings['tiktok'] ?? ''),
            ],
            'media' => [
                'white_logo' => $this->storagePublicUrl($settings['white_logo'] ?? null),
                'black_logo' => $this->storagePublicUrl($settings['black_logo'] ?? null),
                'meta_img' => $this->storagePublicUrl($settings['meta_img'] ?? null),
                'about_us_banner' => $this->storagePublicUrl($settings['about_us_banner'] ?? null),
            ],
            'seo' => $seo,
            'about' => [
                'summary' => (string) ($seo['about_us'] ?? ''),
                'content' => (string) ($seo['about_us_content'] ?? ''),
                'youtube_embed' => (string) ($seo['about_us_youtube_embed'] ?? ''),
                'meta_title' => (string) ($seo['about_us_meta_title'] ?? ''),
                'meta_description' => (string) ($seo['about_us_meta_description'] ?? ''),
                'meta_keywords' => (string) ($seo['about_us_meta_keywords'] ?? ''),
            ],
            'robots_txt' => (string) ($settings['robots_txt'] ?? ''),
        ];
    }

    protected function storagePublicUrl(mixed $path, string $default = 'default.jpg'): string
    {
        $path = is_string($path) ? trim($path) : '';

        if ($path === '') {
            return asset('storage/' . $default);
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public function getTranslations(): array
    {

        $modules = Module::all();

        $locale = app()->getLocale();
        $translations = [];

        // Iterate through each module to process the language file
        foreach ($modules as $module) {
            $modulePath = $module->getPath(); // Path to the module
            $langFilePath = $modulePath . "/lang/$locale.json";

            if (file_exists($langFilePath)) {
                // Decode the JSON file and merge with translations
                $fileContent = json_decode(file_get_contents($langFilePath), true);

                if (is_array($fileContent)) {
                    $translations = array_merge($translations, $fileContent);
                }
            }
        }

        return $this->flattenTranslationsForInertia($translations);
    }

    /**
     * Nested JSON groups (e.g. {"hero": {"title": "…"}}) become dot keys ("hero.title") so
     * Vue can keep using trans("hero.title") with a flat lookup on page.props.translations.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function flattenTranslationsForInertia(array $input, string $prefix = ''): array
    {
        $result = [];

        foreach ($input as $key => $value) {
            $segment = (string) $key;
            $path = $prefix === '' ? $segment : $prefix . '.' . $segment;

            if (is_array($value) && $this->isAssocTranslationArray($value)) {
                $result = array_merge($result, $this->flattenTranslationsForInertia($value, $path));
            } else {
                $result[$path] = $value;
            }
        }

        return $result;
    }

    /**
     * @param  array<mixed>  $arr
     */
    protected function isAssocTranslationArray(array $arr): bool
    {
        if ($arr === []) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @return array<int, array{code: string, native: string, url: string}>
     */
    protected function getLocaleSwitcher(): array
    {
        $order = ['en', 'tr', 'ar'];
        $supported = LaravelLocalization::getSupportedLocales();
        $items = [];

        foreach ($order as $code) {
            if (! isset($supported[$code])) {
                continue;
            }
            $items[] = [
                'code' => $code,
                'native' => (string) ($supported[$code]['native'] ?? $code),
                'url' => LaravelLocalization::getLocalizedURL($code),
            ];
        }

        return $items;
    }
}
