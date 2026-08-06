<?php

namespace Modules\Base\Support\Seo;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Locale switcher URLs and Open Graph / hreflang alternates for front-office SEO.
 * Shared by HandleInertiaRequests and SeoDocumentService so Blade and Vue agree.
 */
final class LocaleAlternates
{
    /** @var array<string, string> */
    private const OG_LOCALE_MAP = [
        'en' => 'en_US',
        'tr' => 'tr_TR',
        'ar' => 'ar_AR',
    ];

    /** @var list<string> */
    private const ORDER = ['en', 'tr', 'ar'];

    /**
     * @return list<array{code: string, native: string, url: string}>
     */
    public static function switcherItems(): array
    {
        $supported = LaravelLocalization::getSupportedLocales();
        $items = [];

        foreach (self::ORDER as $code) {
            if (! isset($supported[$code])) {
                continue;
            }

            $url = (string) LaravelLocalization::getLocalizedURL($code);
            if (trim($url) === '') {
                continue;
            }

            $items[] = [
                'code' => $code,
                'native' => (string) ($supported[$code]['native'] ?? $code),
                'url' => $url,
            ];
        }

        return $items;
    }

    public static function toOgLocale(string $code): string
    {
        $key = strtolower(trim($code));

        return self::OG_LOCALE_MAP[$key] ?? $key;
    }

    public static function currentOgLocale(?string $locale = null): string
    {
        return self::toOgLocale($locale ?? app()->getLocale());
    }

    /**
     * Other supported locales as og:locale:alternate values (excluding current).
     *
     * @return list<array{key: string, value: string}>
     */
    public static function ogLocaleAlternates(?string $activeLocale = null): array
    {
        $active = $activeLocale ?? app()->getLocale();
        $alternates = [];

        foreach (self::switcherItems() as $item) {
            $code = $item['code'];
            if ($code === $active) {
                continue;
            }
            $alternates[] = [
                'key' => 'og-locale-alt-'.$code,
                'value' => self::toOgLocale($code),
            ];
        }

        return $alternates;
    }

    /**
     * Multilingual SEO: alternate URLs for the current page (en / tr / ar + x-default).
     * Keys match App.vue head-key values (hreflang-{code}, hreflang-x-default).
     *
     * @return list<array{key: string, hreflang: string, href: string}>
     */
    public static function hreflangLinks(): array
    {
        $items = self::switcherItems();
        $links = [];

        foreach ($items as $item) {
            $links[] = [
                'key' => 'hreflang-'.$item['code'],
                'hreflang' => $item['code'],
                'href' => $item['url'],
            ];
        }

        $en = null;
        foreach ($items as $item) {
            if ($item['code'] === 'en') {
                $en = $item;
                break;
            }
        }

        if ($en !== null) {
            $links[] = [
                'key' => 'hreflang-x-default',
                'hreflang' => 'x-default',
                'href' => $en['url'],
            ];
        }

        return $links;
    }
}
