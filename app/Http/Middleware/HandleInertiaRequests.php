<?php

namespace App\Http\Middleware;

use App;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Module;
use Modules\Base\Models\Country;
use Modules\Base\Models\Seo;
use Modules\Base\Repositories\Settings\SettingsRepository;
use Modules\Cms\Models\BlogCategory;
use Modules\Cms\Models\Page;
use Modules\Property\Support\PropertySearchBounds;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    public const SHARED_BLOG_CATEGORIES_CACHE_KEY = 'inertia.shared.blog_categories';

    public const SHARED_PAGES_CACHE_KEY = 'inertia.shared.pages';

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
            'flash' => fn () => [
                'contact_sent' => (bool) $request->session()->get('contact_sent'),
            ],
            'appName' => fn () => $this->sharedAppName(),
            'csrf' => csrf_token(),
            'asset_path' => asset('/'),
            'theme_url' => asset('theme/findhouses'),
            'storage_path' => asset('storage').'/',
            'locale' => App::currentLocale(),
            'text_direction' => App::getLocale() === 'ar' ? 'rtl' : 'ltr',
            'translations' => $this->getTranslations(),
            'locale_switcher' => fn () => $this->getLocaleSwitcher(),
            'settings' => fn () => $this->sharedSettingsFlat(),
            'globals' => fn () => $this->sharedGlobals(),
            'property_search' => fn () => $this->sharedPropertySearchBounds(),
            'auth' => fn () => $this->sharedAuthPayload($request),
            'subscribe_store_url' => fn () => route('support.newsletter.subscribe'),
            'ziggy' => fn () => array_merge((new Ziggy)->toArray(), [
                'location' => $request->url(),
            ]),
        ]);
    }

    /**
     * Site display name from admin SEO (Main → Website name), else {@see config()} app.name.
     */
    protected function sharedAppName(): string
    {
        $name = trim((string) (Seo::get('website_name') ?: ''));

        if ($name !== '') {
            return $name;
        }

        return (string) config('app.name');
    }

    /**
     * Front-office auth: full {@see $user->name}, compact {@see authNavDisplayName()}
     * for nav, public avatar URLs via {@see User::getAvatarAttribute()}.
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
                'turkish_citizenship_banner' => $this->storagePublicUrl($settings['turkish_citizenship_banner'] ?? null),
                'contact_us_banner' => $this->storagePublicUrl($settings['contact_us_banner'] ?? null),
                'blog_show_banner' => $this->storagePublicUrl($settings['blog_show_banner'] ?? null),
                'property_show_banner' => $this->storagePublicUrl($settings['property_show_banner'] ?? null),
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
            'turkish_citizenship' => [
                'summary' => (string) ($seo['turkish_citizenship'] ?? ''),
                'banner_url' => $this->storagePublicUrl($settings['turkish_citizenship_banner'] ?? null),
                'content' => (string) ($seo['turkish_citizenship_content'] ?? ''),
                'youtube_embed' => (string) ($seo['turkish_citizenship_youtube_embed'] ?? ''),
                'meta_title' => (string) ($seo['turkish_citizenship_meta_title'] ?? ''),
                'meta_description' => (string) ($seo['turkish_citizenship_meta_description'] ?? ''),
                'meta_keywords' => (string) ($seo['turkish_citizenship_meta_keywords'] ?? ''),
            ],
            'robots_txt' => (string) ($settings['robots_txt'] ?? ''),
            'countries' => $this->sharedCountriesList(),
            'blog_categories' => $this->sharedBlogCategoriesList(),
            'pages' => $this->sharedPagesLists(),
        ];
    }

    /**
     * Price/area slider bounds and project unit type catalog for hero + listing filters.
     *
     * @return array<string, mixed>
     */
    protected function sharedPropertySearchBounds(): array
    {
        return PropertySearchBounds::forLocale(app()->getLocale());
    }

    /**
     * Blog categories for front-office Vue (sidebar, navbar, filters).
     * Cached with all name translations; localized per request.
     *
     * @return list<array{id: int, name: string, slug: string, add_to_navbar: bool}>
     */
    protected function sharedBlogCategoriesList(): array
    {
        $locale = app()->getLocale();

        /** @var list<array{id: int, name: array<string, string>, slug: string, add_to_navbar: bool}> $cached */
        $cached = Cache::rememberForever(self::SHARED_BLOG_CATEGORIES_CACHE_KEY, function (): array {
            return BlogCategory::query()
                ->orderBy('slug')
                ->get(['id', 'name', 'slug', 'add_to_navbar'])
                ->map(static function (BlogCategory $category): array {
                    return [
                        'id' => $category->id,
                        'name' => $category->getTranslations('name'),
                        'slug' => $category->slug,
                        'add_to_navbar' => (bool) $category->add_to_navbar,
                    ];
                })
                ->values()
                ->all();
        });

        return array_map(static function (array $row) use ($locale): array {
            $names = $row['name'];
            $name = is_array($names)
                ? (string) ($names[$locale] ?? reset($names) ?: '')
                : (string) $names;

            return [
                'id' => $row['id'],
                'name' => $name,
                'slug' => $row['slug'],
                'add_to_navbar' => $row['add_to_navbar'],
            ];
        }, $cached);
    }

    /**
     * CMS pages for front-office Vue, split by placement flags (a page may appear in multiple lists).
     * Cached with all translations; localized per request. Only Published pages are included.
     *
     * @return array{
     *     navbar: list<array<string, mixed>>,
     *     footer: list<array<string, mixed>>,
     *     top_bar: list<array<string, mixed>>,
     *     bottom_bar: list<array<string, mixed>>
     * }
     */
    protected function sharedPagesLists(): array
    {
        $locale = app()->getLocale();

        /** @var list<array<string, mixed>> $cached */
        $cached = Cache::rememberForever(self::SHARED_PAGES_CACHE_KEY, function (): array {
            return Page::query()
                ->published()
                ->orderBy('slug')
                ->get([
                    'id',
                    'title',
                    'slug',
                    'content',
                    'image',
                    'meta_image',
                    'meta_title',
                    'meta_description',
                    'meta_keywords',
                    'add_to_nav',
                    'add_to_footer',
                    'add_to_top_bar',
                    'add_to_bottom_bar',
                    'featured',
                    'visits',
                ])
                ->map(static function (Page $page): array {
                    return [
                        'id' => $page->id,
                        'slug' => $page->slug,
                        'title' => $page->getTranslations('title'),
                        'content' => $page->getTranslations('content'),
                        'meta_title' => $page->getTranslations('meta_title'),
                        'meta_description' => $page->getTranslations('meta_description'),
                        'meta_keywords' => $page->getTranslations('meta_keywords'),
                        'image' => (string) ($page->image ?? ''),
                        'meta_image' => (string) ($page->meta_image ?? ''),
                        'add_to_nav' => (bool) $page->add_to_nav,
                        'add_to_footer' => (bool) $page->add_to_footer,
                        'add_to_top_bar' => (bool) $page->add_to_top_bar,
                        'add_to_bottom_bar' => (bool) $page->add_to_bottom_bar,
                        'featured' => (bool) $page->featured,
                        'visits' => (int) $page->visits,
                    ];
                })
                ->values()
                ->all();
        });

        $localized = array_map(function (array $row) use ($locale): array {
            return [
                'id' => $row['id'],
                'slug' => $row['slug'],
                'title' => $this->localizedTranslation($row['title'], $locale),
                'content' => $this->localizedTranslation($row['content'], $locale),
                'meta_title' => $this->localizedTranslation($row['meta_title'], $locale),
                'meta_description' => $this->localizedTranslation($row['meta_description'], $locale),
                'meta_keywords' => $this->localizedTranslation($row['meta_keywords'], $locale),
                'image' => $this->pageMediaPublicUrl($row['image'] ?? null),
                'meta_image' => $this->pageMediaPublicUrl($row['meta_image'] ?? null),
                'add_to_nav' => $row['add_to_nav'],
                'add_to_footer' => $row['add_to_footer'],
                'add_to_top_bar' => $row['add_to_top_bar'],
                'add_to_bottom_bar' => $row['add_to_bottom_bar'],
                'featured' => $row['featured'],
                'visits' => $row['visits'],
            ];
        }, $cached);

        return [
            'navbar' => array_values(array_filter($localized, static fn (array $page): bool => $page['add_to_nav'])),
            'footer' => array_values(array_filter($localized, static fn (array $page): bool => $page['add_to_footer'])),
            'top_bar' => array_values(array_filter($localized, static fn (array $page): bool => $page['add_to_top_bar'])),
            'bottom_bar' => array_values(array_filter($localized, static fn (array $page): bool => $page['add_to_bottom_bar'])),
        ];
    }

    /**
     * @param  array<string, string>|string|null  $value
     */
    protected function localizedTranslation(mixed $value, string $locale): string
    {
        if (is_array($value)) {
            return (string) ($value[$locale] ?? reset($value) ?: '');
        }

        return (string) ($value ?? '');
    }

    /**
     * Countries for front-office Vue (dropdowns, phone prefixes, etc.).
     * Separate cache key from {@see Controller::withCountries()} (Eloquent collection for Blade).
     *
     * @return list<array{id: int, name: string, iso_code_2: string, iso_code_3: string, phone_code: string|null, flag: string}>
     */
    protected function sharedCountriesList(): array
    {
        return Cache::rememberForever('inertia.shared.countries', function (): array {
            return Country::query()
                ->orderBy('name')
                ->get()
                ->map(static function (Country $country): array {
                    return [
                        'id' => $country->id,
                        'name' => $country->name,
                        'iso_code_2' => $country->iso_code_2,
                        'iso_code_3' => $country->iso_code_3,
                        'phone_code' => $country->phone_code,
                        'flag' => $country->flag,
                    ];
                })
                ->values()
                ->all();
        });
    }

    protected function pageMediaPublicUrl(mixed $path): string
    {
        $path = is_string($path) ? trim($path) : '';

        if ($path === '') {
            return asset('images/blank.png');
        }

        return $this->storagePublicUrl($path);
    }

    protected function storagePublicUrl(mixed $path, string $default = 'default.jpg'): string
    {
        $path = is_string($path) ? trim($path) : '';

        if ($path === '') {
            return asset('storage/'.$default);
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    public function getTranslations(): array
    {

        $modules = Module::all();

        $locale = app()->getLocale();
        $translations = [];

        // Iterate through each module to process the language file
        foreach ($modules as $module) {
            $modulePath = $module->getPath(); // Path to the module
            $langFilePath = $modulePath."/lang/$locale.json";

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
            $path = $prefix === '' ? $segment : $prefix.'.'.$segment;

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
