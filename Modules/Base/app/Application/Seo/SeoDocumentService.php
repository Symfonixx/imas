<?php

namespace Modules\Base\Application\Seo;

use Modules\Base\Models\Seo;
use Modules\Base\Models\Settings;
use Modules\Base\Support\Media\MediaAssetResolver;
use Modules\Base\Support\Seo\JsonLd;

/**
 * Resolves document-level SEO for the root Blade layout (View Page Source)
 * and for controller withViewData overrides. Mirrors front-office Vue fallbacks.
 */
class SeoDocumentService
{
    /**
     * @param  array{
     *     page_title?: string,
     *     title?: string,
     *     description?: string|null,
     *     keywords?: string|array<int, string>|null,
     *     og_image?: string|null,
     *     canonical?: string|null,
     *     og_type?: string|null,
     *     robots?: string|null,
     *     json_ld?: array<string, array<array-key, mixed>|string|null>,
     *     title_keys?: list<string>,
     *     description_keys?: list<string>,
     *     keywords_keys?: list<string>
     * }  $overrides
     * @return array{
     *     title: string,
     *     description: string,
     *     keywords: string,
     *     og_image: string,
     *     canonical: string,
     *     og_type: string,
     *     robots: string,
     *     json_ld: array<string, string>
     * }
     */
    public function documentSeo(array $overrides = []): array
    {
        $siteName = $this->siteName();

        $title = $this->resolveTitle($overrides, $siteName);
        $description = $this->resolveDescription($overrides);
        $keywords = $this->resolveKeywords($overrides);
        $ogImage = $this->resolveOgImage($overrides);
        $canonical = $this->stringOverride($overrides['canonical'] ?? null);
        $ogType = $this->stringOverride($overrides['og_type'] ?? null) ?: 'website';
        $robots = $this->stringOverride($overrides['robots'] ?? null);

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'og_image' => $ogImage,
            'canonical' => $canonical,
            'og_type' => $ogType,
            'robots' => $robots,
            'json_ld' => $this->resolveJsonLd($overrides),
        ];
    }

    public function siteName(): string
    {
        $name = $this->seoString('website_name');

        return $name !== '' ? $name : (string) config('app.name');
    }

    /**
     * Localized label from Modules/Base/lang/{locale}.json (dot key e.g. contact_us.title).
     */
    public function labelFromBaseLang(string $dotKey, string $fallback = ''): string
    {
        $locale = app()->getLocale();
        $path = module_path('Base', "lang/{$locale}.json");

        if (! is_readable($path)) {
            return $fallback;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            return $fallback;
        }

        $cursor = $decoded;
        foreach (explode('.', $dotKey) as $segment) {
            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return $fallback;
            }
            $cursor = $cursor[$segment];
        }

        return is_string($cursor) && trim($cursor) !== '' ? trim($cursor) : $fallback;
    }

    /**
     * Public URL for a settings image path, or empty when missing / default placeholder.
     */
    public function settingsImageUrl(string $settingsKey): string
    {
        $path = Settings::get($settingsKey, '');
        $path = is_string($path) ? trim($path) : '';

        if ($path === '') {
            return '';
        }

        $resolved = app(MediaAssetResolver::class)->resolve($path);
        $url = is_array($resolved) ? (string) ($resolved['url'] ?? '') : '';

        if ($url === '') {
            $url = $this->storagePublicUrl($path);
        }

        return $this->isDefaultPlaceholderImage($url) ? '' : $url;
    }

    public function seoValue(string $key): string
    {
        return $this->seoString($key);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function resolveTitle(array $overrides, string $siteName): string
    {
        $explicit = $this->stringOverride($overrides['title'] ?? null);
        if ($explicit !== '') {
            return $explicit;
        }

        $pageTitle = $this->stringOverride($overrides['page_title'] ?? null);
        if ($pageTitle !== '') {
            return $this->appendSiteName($pageTitle, $siteName);
        }

        $titleKeys = $overrides['title_keys'] ?? [
            'site_meta_title',
            'main_title',
            'website_name',
        ];

        if (! is_array($titleKeys)) {
            $titleKeys = ['site_meta_title', 'main_title', 'website_name'];
        }

        $fromKeys = $this->firstSeoString($titleKeys);
        if ($fromKeys !== '') {
            // Global template titles are used as-is (may already include site name / tagline).
            if (($overrides['title_keys'] ?? null) === null) {
                return $fromKeys;
            }

            return $this->appendSiteName($fromKeys, $siteName);
        }

        return $siteName;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function resolveDescription(array $overrides): string
    {
        $explicit = $this->stringOverride($overrides['description'] ?? null);
        if ($explicit !== '') {
            return $explicit;
        }

        $keys = $overrides['description_keys'] ?? [
            'site_meta_description',
            'website_desc',
        ];

        if (! is_array($keys)) {
            $keys = ['site_meta_description', 'website_desc'];
        }

        return $this->firstSeoString($keys);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function resolveKeywords(array $overrides): string
    {
        if (array_key_exists('keywords', $overrides)) {
            $normalized = $this->normalizeKeywords($overrides['keywords']);
            if ($normalized !== '') {
                return $normalized;
            }
        }

        $keys = $overrides['keywords_keys'] ?? [
            'site_meta_keywords',
            'website_keywords',
        ];

        if (! is_array($keys)) {
            $keys = ['site_meta_keywords', 'website_keywords'];
        }

        return $this->firstSeoString($keys);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function resolveOgImage(array $overrides): string
    {
        $explicit = $this->stringOverride($overrides['og_image'] ?? null);
        if ($explicit !== '') {
            if ($this->isDefaultPlaceholderImage($explicit)) {
                // Fall through to site meta image.
            } else {
                return $explicit;
            }
        }

        $path = Settings::get('meta_img', '');
        $path = is_string($path) ? trim($path) : '';

        if ($path === '') {
            return '';
        }

        $resolved = app(MediaAssetResolver::class)->resolve($path);
        $url = is_array($resolved) ? (string) ($resolved['url'] ?? '') : '';

        if ($url === '') {
            $url = $this->storagePublicUrl($path);
        }

        return $this->isDefaultPlaceholderImage($url) ? '' : $url;
    }

    /**
     * Encoded JSON-LD blocks keyed by the Inertia head-key their Vue counterpart uses,
     * so the client head replaces them instead of duplicating them.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, string>
     */
    private function resolveJsonLd(array $overrides): array
    {
        $blocks = $overrides['json_ld'] ?? null;

        if (! is_array($blocks)) {
            return [];
        }

        $resolved = [];

        foreach ($blocks as $key => $block) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            $json = is_string($block) ? JsonLd::encode(JsonLd::decode($block)) : JsonLd::encode($block);

            if ($json !== '') {
                $resolved[$key] = $json;
            }
        }

        return $resolved;
    }

    /**
     * @param  list<string>  $keys
     */
    private function firstSeoString(array $keys): string
    {
        foreach ($keys as $key) {
            if (! is_string($key) || $key === '') {
                continue;
            }
            $value = $this->seoString($key);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function seoString(string $key): string
    {
        $value = Seo::get($key, '');

        return is_string($value) ? trim($value) : '';
    }

    private function stringOverride(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return trim($value);
    }

    /**
     * @param  string|array<int, mixed>|null  $keywords
     */
    private function normalizeKeywords(mixed $keywords): string
    {
        if (is_array($keywords)) {
            $parts = [];
            foreach ($keywords as $part) {
                if (is_string($part) && trim($part) !== '') {
                    $parts[] = trim($part);
                }
            }

            return implode(', ', $parts);
        }

        return $this->stringOverride($keywords);
    }

    private function appendSiteName(string $title, string $siteName): string
    {
        if ($siteName === '' || $title === $siteName || str_contains($title, $siteName)) {
            return $title;
        }

        return $title.' | '.$siteName;
    }

    private function storagePublicUrl(string $path): string
    {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    private function isDefaultPlaceholderImage(string $url): bool
    {
        return (bool) preg_match('#/default\.jpg(?:\?.*)?$#i', $url);
    }
}
