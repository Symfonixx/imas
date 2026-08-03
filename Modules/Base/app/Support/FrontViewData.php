<?php

namespace Modules\Base\Support;

use App;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Module;
use Modules\Base\Models\Country;
use Modules\Base\Models\Seo;
use Modules\Base\Repositories\Settings\SettingsRepository;
use Modules\Cms\Models\BlogCategory;
use Modules\Cms\Models\Page;
use Modules\Property\Support\PropertySearchBounds;

/**
 * Shared front-office view data (formerly HandleInertiaRequests shared props).
 */
final class FrontViewData
{
    public const SHARED_BLOG_CATEGORIES_CACHE_KEY = 'front.shared.blog_categories';

    public const SHARED_PAGES_CACHE_KEY = 'front.shared.pages';

    public function __construct(private readonly SettingsRepository $settingsRepository) {}

    /**
     * @return array<string, mixed>
     */
    public function forRequest(Request $request): array
    {
        $appName = $this->sharedAppName();
        $globals = $this->sharedGlobals();
        $localeSwitcher = $this->getLocaleSwitcher();
        $translations = $this->getTranslations();

        return [
            'flash' => [
                'contact_sent' => (bool) $request->session()->get('contact_sent'),
                'status' => $request->session()->get('status'),
            ],
            'appName' => $appName,
            'csrf' => csrf_token(),
            'asset_path' => asset('/'),
            'theme_url' => asset('theme/findhouses'),
            'storage_path' => asset('storage').'/',
            'locale' => App::currentLocale(),
            'text_direction' => App::getLocale() === 'ar' ? 'rtl' : 'ltr',
            'translations' => $translations,
            'locale_switcher' => $localeSwitcher,
            'settings' => $this->sharedSettingsFlat(),
            'globals' => $globals,
            'property_search' => PropertySearchBounds::forLocale(app()->getLocale()),
            'auth' => $this->sharedAuthPayload($request),
            'subscribe_store_url' => route('support.newsletter.subscribe'),
            'nav_links' => $this->buildNavLinks($globals, App::currentLocale(), $translations),
            'seo' => FrontSeo::make([], $globals, $localeSwitcher, $appName),
            'navbar_transparent' => false,
        ];
    }

    public function sharedAppName(): string
    {
        $name = trim((string) (Seo::get('website_name') ?: ''));

        return $name !== '' ? $name : (string) config('app.name');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function sharedAuthPayload(Request $request): ?array
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

    public function authNavDisplayName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false || $parts === []) {
            return $name;
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        $initials = '';
        foreach ($parts as $part) {
            $initials .= mb_substr($part, 0, 1);
        }

        return mb_strtoupper($initials);
    }

    /**
     * @return array<string, mixed>
     */
    public function sharedSettingsFlat(): array
    {
        /** @var array<string, mixed> $flat */
        $flat = $this->settingsRepository->allKeyValue()->all();

        $flat['contact_phone'] = $flat['phone'] ?? ($flat['contact_phone'] ?? '');
        $flat['contact_email'] = $flat['email'] ?? ($flat['contact_email'] ?? '');
        $flat['contact_address'] = $flat['address'] ?? ($flat['contact_address'] ?? '');

        return $flat;
    }

    /**
     * @return array<string, mixed>
     */
    public function sharedGlobals(): array
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
     * @return list<array{id: int, name: string, slug: string, add_to_navbar: bool}>
     */
    public function sharedBlogCategoriesList(): array
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
     * @return array{navbar: list<array<string, mixed>>, footer: list<array<string, mixed>>, top_bar: list<array<string, mixed>>, bottom_bar: list<array<string, mixed>>}
     */
    public function sharedPagesLists(): array
    {
        $locale = app()->getLocale();

        /** @var list<array<string, mixed>> $cached */
        $cached = Cache::rememberForever(self::SHARED_PAGES_CACHE_KEY, function (): array {
            return Page::query()
                ->published()
                ->orderBy('slug')
                ->get([
                    'id', 'title', 'slug', 'content', 'image', 'meta_image',
                    'meta_title', 'meta_description', 'meta_keywords',
                    'add_to_nav', 'add_to_footer', 'add_to_top_bar', 'add_to_bottom_bar',
                    'featured', 'visits',
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
     * @param  array<string, mixed>  $globals
     * @param  array<string, mixed>  $translations
     * @return list<array{key: string, href?: string, label?: string, children?: list<array{key: string, href: string, label?: string}>}>
     */
    public function buildNavLinks(array $globals, string $locale, array $translations = []): array
    {
        $t = static fn (string $key) => (string) ($translations[$key] ?? $key);

        $blogCategories = $globals['blog_categories'] ?? [];
        $blogChildren = [];
        foreach ($blogCategories as $c) {
            if (! ($c['add_to_navbar'] ?? true)) {
                // still show all categories like Vue (Vue used all categories)
            }
            $blogChildren[] = [
                'key' => 'blog-category-'.$c['id'],
                'label' => $c['name'],
                'href' => route('blog.index', ['category_id' => $c['id']]),
            ];
        }

        $blogsNav = [
            'key' => 'navBar.Blogs',
            'label' => $t('navBar.Blogs'),
            'href' => route('blog.index'),
        ];
        if ($blogChildren !== []) {
            $blogsNav['children'] = $blogChildren;
        }

        $navbarPages = $globals['pages']['navbar'] ?? [];
        $pageChildren = [];
        foreach ($navbarPages as $p) {
            $pageChildren[] = [
                'key' => 'page-'.$p['id'],
                'label' => $p['title'],
                'href' => route('page.show', ['slug' => $p['slug']]),
            ];
        }

        $links = [
            ['key' => 'navBar.Home', 'label' => $t('navBar.Home'), 'href' => route('home')],
            ['key' => 'navBar.Buy Real Estate', 'label' => $t('navBar.Buy Real Estate'), 'href' => route('property.index')],
            ['key' => 'navBar.Turkish Citizenship', 'label' => $t('navBar.Turkish Citizenship'), 'href' => route('turkish-citizenship')],
            $blogsNav,
        ];

        if ($pageChildren !== []) {
            $links[] = [
                'key' => 'navBar.Pages',
                'label' => $t('navBar.Pages'),
                'children' => $pageChildren,
            ];
        }

        $links[] = ['key' => 'about_us.title', 'label' => $t('about_us.title'), 'href' => route('about-us')];
        $links[] = ['key' => 'navBar.Contact us', 'label' => $t('navBar.Contact us'), 'href' => route('support.contact-us')];

        return $links;
    }

    /**
     * @return list<array{id: int, name: string, iso_code_2: string, iso_code_3: string, phone_code: string|null, flag: string}>
     */
    public function sharedCountriesList(): array
    {
        return Cache::rememberForever('front.shared.countries', function (): array {
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

    public function localizedTranslation(mixed $value, string $locale): string
    {
        if (is_array($value)) {
            return (string) ($value[$locale] ?? reset($value) ?: '');
        }

        return (string) ($value ?? '');
    }

    public function pageMediaPublicUrl(mixed $path): string
    {
        $path = is_string($path) ? trim($path) : '';

        if ($path === '') {
            return asset('images/blank.png');
        }

        return $this->storagePublicUrl($path);
    }

    public function storagePublicUrl(mixed $path, string $default = 'default.jpg'): string
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

    /**
     * @return array<string, mixed>
     */
    public function getTranslations(): array
    {
        $modules = Module::all();
        $locale = app()->getLocale();
        $translations = [];

        foreach ($modules as $module) {
            $langFilePath = $module->getPath()."/lang/$locale.json";
            if (file_exists($langFilePath)) {
                $fileContent = json_decode((string) file_get_contents($langFilePath), true);
                if (is_array($fileContent)) {
                    $translations = array_merge($translations, $fileContent);
                }
            }
        }

        return $this->flattenTranslations($translations);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function flattenTranslations(array $input, string $prefix = ''): array
    {
        $result = [];

        foreach ($input as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value) && $this->isAssocArray($value)) {
                $result = array_merge($result, $this->flattenTranslations($value, $path));
            } else {
                $result[$path] = $value;
            }
        }

        return $result;
    }

    /**
     * @param  array<mixed>  $arr
     */
    private function isAssocArray(array $arr): bool
    {
        if ($arr === []) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @return list<array{code: string, native: string, url: string}>
     */
    public function getLocaleSwitcher(): array
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
                'url' => (string) LaravelLocalization::getLocalizedURL($code),
            ];
        }

        return $items;
    }
}
