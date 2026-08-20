<?php

namespace Modules\Base\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Cms\Models\Blog;
use Modules\Cms\Models\Page;
use Modules\Property\Models\Property;
use Modules\User\Enums\CmsStatus;

class SitemapService
{
    /** @var list<string> */
    private const RESERVED_PAGE_SLUGS = [
        'about-us',
        'contact-us',
        'blog',
        'property',
        'turkish-citizenship',
        'login',
        'register',
        'admin',
        'api',
    ];

    /**
     * @return list<array{
     *     loc: string,
     *     lastmod: string|null,
     *     changefreq: string,
     *     priority: string,
     *     alternates: array<string, string>,
     *     xDefault: string
     * }>
     */
    public function entries(): array
    {
        return Cache::remember('sitemap.entries', now()->addHour(), fn (): array => $this->buildEntries());
    }

    public function forgetCache(): void
    {
        Cache::forget('sitemap.entries');
    }

    /**
     * @return list<array{
     *     loc: string,
     *     lastmod: string|null,
     *     changefreq: string,
     *     priority: string,
     *     alternates: array<string, string>,
     *     xDefault: string
     * }>
     */
    private function buildEntries(): array
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', []));
        $defaultLocale = (string) config('app.locale', 'en');

        $pages = collect($this->staticPages())
            ->merge($this->cmsPages())
            ->merge($this->blogs())
            ->merge($this->properties());

        $entries = [];

        foreach ($pages as $page) {
            $alternates = $this->localizedUrls($locales, $page['path']);
            $xDefault = $alternates[$defaultLocale] ?? reset($alternates) ?: '';
            $lastmod = $this->formatLastmod($page['lastmod'] ?? null);

            // Emit a distinct <url><loc> per locale (en / ar / tr) so crawlers
            // index each language version, not only the default.
            foreach ($alternates as $loc) {
                $entries[] = [
                    'loc' => $loc,
                    'lastmod' => $lastmod,
                    'changefreq' => $page['changefreq'],
                    'priority' => $page['priority'],
                    'alternates' => $alternates,
                    'xDefault' => $xDefault,
                ];
            }
        }

        return $entries;
    }

    /**
     * @param  list<string>  $locales
     * @return array<string, string>
     */
    private function localizedUrls(array $locales, string $path): array
    {
        $urls = [];

        foreach ($locales as $locale) {
            $urls[$locale] = LaravelLocalization::getLocalizedURL($locale, $path, [], true);
        }

        return $urls;
    }

    /**
     * @return list<array{path: string, changefreq: string, priority: string, lastmod: Carbon|null}>
     */
    private function staticPages(): array
    {
        return [
            ['path' => '/', 'changefreq' => 'daily', 'priority' => '1.0', 'lastmod' => null],
            ['path' => '/about-us', 'changefreq' => 'monthly', 'priority' => '0.8', 'lastmod' => null],
            ['path' => '/contact-us', 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => null],
            ['path' => '/blog', 'changefreq' => 'daily', 'priority' => '0.9', 'lastmod' => null],
            ['path' => '/property', 'changefreq' => 'daily', 'priority' => '0.9', 'lastmod' => null],
            ['path' => '/turkish-citizenship', 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => null],
        ];
    }

    /**
     * @return list<array{path: string, changefreq: string, priority: string, lastmod: Carbon|null}>
     */
    private function cmsPages(): array
    {
        return Page::query()
            ->published()
            ->whereNotIn('slug', self::RESERVED_PAGE_SLUGS)
            ->orderBy('slug')
            ->get(['slug', 'updated_at'])
            ->map(static fn (Page $page): array => [
                'path' => '/'.$page->slug,
                'changefreq' => 'monthly',
                'priority' => '0.6',
                'lastmod' => $page->updated_at,
            ])
            ->all();
    }

    /**
     * @return list<array{path: string, changefreq: string, priority: string, lastmod: Carbon|null}>
     */
    private function blogs(): array
    {
        return Blog::query()
            ->published()
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->map(static fn (Blog $blog): array => [
                'path' => '/blog/'.$blog->slug,
                'changefreq' => 'weekly',
                'priority' => '0.7',
                'lastmod' => $blog->updated_at,
            ])
            ->all();
    }

    /**
     * @return list<array{path: string, changefreq: string, priority: string, lastmod: Carbon|null}>
     */
    private function properties(): array
    {
        return Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->orderByDesc('updated_at')
            ->get(['url_key', 'updated_at'])
            ->map(static fn (Property $property): array => [
                'path' => '/property/'.$property->url_key,
                'changefreq' => 'weekly',
                'priority' => '0.8',
                'lastmod' => $property->updated_at,
            ])
            ->all();
    }

    private function formatLastmod(?Carbon $lastmod): ?string
    {
        return $lastmod?->toDateString();
    }
}
