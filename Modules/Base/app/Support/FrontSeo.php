<?php

namespace Modules\Base\Support;

/**
 * Builds the $seo array consumed by resources/views/partials/seo-head.blade.php.
 *
 * Tier rules: hub = globals; parent = globals + overrides; detail = entity first.
 */
final class FrontSeo
{
    private const OG_LOCALE_MAP = [
        'en' => 'en_US',
        'tr' => 'tr_TR',
        'ar' => 'ar_AR',
    ];

    /**
     * @param  array{
     *     title?: string|null,
     *     description?: string|null,
     *     keywords?: string|null,
     *     image?: string|null,
     *     canonical?: string|null,
     *     og_type?: string|null,
     *     twitter_card?: string|null,
     *     robots?: string|null,
     *     json_ld?: list<array<string, mixed>|string>|null,
     * }  $overrides
     * @param  array<string, mixed>  $globals
     * @param  list<array{code: string, native?: string, url: string}>  $localeSwitcher
     * @return array<string, mixed>
     */
    public static function make(
        array $overrides = [],
        array $globals = [],
        array $localeSwitcher = [],
        ?string $appName = null,
    ): array {
        $seoGlobals = is_array($globals['seo'] ?? null) ? $globals['seo'] : [];
        $media = is_array($globals['media'] ?? null) ? $globals['media'] : [];
        $siteName = trim((string) ($appName ?: ($seoGlobals['website_name'] ?? config('app.name'))));

        $title = self::firstNonEmpty(
            $overrides['title'] ?? null,
            $seoGlobals['site_meta_title'] ?? null,
            $seoGlobals['main_title'] ?? null,
            $siteName,
        );

        $description = self::firstNonEmpty(
            $overrides['description'] ?? null,
            $seoGlobals['site_meta_description'] ?? null,
            $seoGlobals['website_desc'] ?? null,
        );

        $keywords = self::firstNonEmpty(
            $overrides['keywords'] ?? null,
            $seoGlobals['site_meta_keywords'] ?? null,
            $seoGlobals['website_keywords'] ?? null,
        );

        $image = self::firstNonEmpty(
            $overrides['image'] ?? null,
            $media['meta_img'] ?? null,
        );

        $canonical = self::firstNonEmpty(
            $overrides['canonical'] ?? null,
            url()->current(),
        );

        $locale = app()->getLocale();
        $ogLocale = self::OG_LOCALE_MAP[$locale] ?? $locale;
        $ogAlternates = [];
        foreach ($localeSwitcher as $item) {
            $code = (string) ($item['code'] ?? '');
            if ($code !== '' && $code !== $locale) {
                $ogAlternates[] = self::OG_LOCALE_MAP[$code] ?? $code;
            }
        }

        $hreflang = [];
        foreach ($localeSwitcher as $item) {
            $url = isset($item['url']) && is_string($item['url']) ? trim($item['url']) : '';
            $code = (string) ($item['code'] ?? '');
            if ($url === '' || $code === '') {
                continue;
            }
            $hreflang[] = ['hreflang' => $code, 'url' => $url];
        }
        foreach ($localeSwitcher as $item) {
            if (($item['code'] ?? '') === 'en' && ! empty($item['url'])) {
                $hreflang[] = ['hreflang' => 'x-default', 'url' => trim((string) $item['url'])];
                break;
            }
        }

        $jsonLd = [];
        foreach ((array) ($overrides['json_ld'] ?? []) as $block) {
            if (is_array($block) && $block !== []) {
                $jsonLd[] = $block;
            } elseif (is_string($block) && trim($block) !== '') {
                $decoded = json_decode($block, true);
                if (is_array($decoded)) {
                    $jsonLd[] = $decoded;
                } elseif (json_last_error() === JSON_ERROR_NONE) {
                    // keep raw valid JSON string path via decode fail — skip invalid
                } else {
                    // try as already-encoded object string for script output
                    $trimmed = trim($block);
                    if (str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[')) {
                        $jsonLd[] = $trimmed;
                    }
                }
            }
        }

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'image' => $image,
            'canonical' => $canonical,
            'og_type' => self::firstNonEmpty($overrides['og_type'] ?? null, 'website') ?: 'website',
            'twitter_card' => self::firstNonEmpty($overrides['twitter_card'] ?? null, 'summary_large_image') ?: 'summary_large_image',
            'robots' => $overrides['robots'] ?? null,
            'site_name' => $siteName,
            'og_locale' => $ogLocale,
            'og_locale_alternates' => $ogAlternates,
            'hreflang' => $hreflang,
            'json_ld' => $jsonLd,
        ];
    }

    /**
     * Hub page with section title suffix, e.g. "Blog | Site".
     *
     * @param  array<string, mixed>  $globals
     * @param  list<array{code: string, url: string}>  $localeSwitcher
     * @return array<string, mixed>
     */
    public static function forHub(
        string $sectionTitle,
        array $globals = [],
        array $localeSwitcher = [],
        ?string $appName = null,
        ?string $canonical = null,
        array $extraJsonLd = [],
    ): array {
        $siteName = trim((string) ($appName ?: config('app.name')));
        $title = $sectionTitle !== '' && $siteName !== ''
            ? "{$sectionTitle} | {$siteName}"
            : ($sectionTitle ?: $siteName);

        return self::make([
            'title' => $title,
            'canonical' => $canonical,
            'json_ld' => $extraJsonLd,
        ], $globals, $localeSwitcher, $appName);
    }

    /**
     * @param  array<string, mixed>  $globals
     * @param  list<array{code: string, url: string}>  $localeSwitcher
     * @return array<string, mixed>
     */
    public static function forHome(
        array $globals = [],
        array $localeSwitcher = [],
        ?string $appName = null,
    ): array {
        $seoGlobals = is_array($globals['seo'] ?? null) ? $globals['seo'] : [];
        $contact = is_array($globals['contact'] ?? null) ? $globals['contact'] : [];
        $social = is_array($globals['social'] ?? null) ? $globals['social'] : [];
        $media = is_array($globals['media'] ?? null) ? $globals['media'] : [];
        $siteName = trim((string) ($appName ?: ($seoGlobals['website_name'] ?? config('app.name'))));
        $homeUrl = route('home');
        $description = self::firstNonEmpty(
            $seoGlobals['site_meta_description'] ?? null,
            $seoGlobals['website_desc'] ?? null,
        );

        $org = StructuredData::buildOrganizationSchema([
            'name' => $siteName,
            'url' => $homeUrl,
            'description' => $description,
            'logo' => $media['white_logo'] ?? ($media['black_logo'] ?? null),
            'email' => $contact['email'] ?? null,
            'phone' => $contact['phone'] ?? null,
            'address' => $contact['address'] ?? null,
            'sameAs' => StructuredData::collectSocialUrls($social),
        ]);

        $website = StructuredData::buildWebsiteSchema([
            'name' => $siteName,
            'url' => $homeUrl,
            'description' => $description,
            'searchUrlTemplate' => route('property.index').'?keyword={search_term_string}',
        ]);

        $jsonLd = array_values(array_filter([$org, $website]));

        return self::make([
            'canonical' => $homeUrl,
            'json_ld' => $jsonLd,
        ], $globals, $localeSwitcher, $appName);
    }

    private static function firstNonEmpty(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }
}
