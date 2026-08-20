<?php

namespace Modules\Base\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Base\Models\Seo;
use Modules\Cms\Models\Blog;

class RssService
{
    private const ITEM_LIMIT = 50;

    private const CACHE_TTL_MINUTES = 60;

    /**
     * @return array{
     *     title: string,
     *     link: string,
     *     description: string,
     *     language: string,
     *     lastBuildDate: string,
     *     selfUrl: string,
     *     items: list<array{
     *         title: string,
     *         link: string,
     *         guid: string,
     *         pubDate: string,
     *         description: string,
     *         content: string,
     *         category: string|null,
     *         imageUrl: string|null,
     *         imageType: string|null
     *     }>
     * }
     */
    public function feed(string $locale): array
    {
        return Cache::remember(
            $this->cacheKey($locale),
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn (): array => $this->buildFeed($locale)
        );
    }

    public function forgetCache(): void
    {
        foreach (array_keys(config('laravellocalization.supportedLocales', [])) as $locale) {
            Cache::forget($this->cacheKey($locale));
        }
    }

    private function cacheKey(string $locale): string
    {
        return "rss.feed.{$locale}";
    }

    /**
     * @return array{
     *     title: string,
     *     link: string,
     *     description: string,
     *     language: string,
     *     lastBuildDate: string,
     *     selfUrl: string,
     *     items: list<array{
     *         title: string,
     *         link: string,
     *         guid: string,
     *         pubDate: string,
     *         description: string,
     *         content: string,
     *         category: string|null,
     *         imageUrl: string|null,
     *         imageType: string|null
     *     }>
     * }
     */
    private function buildFeed(string $locale): array
    {
        $blogs = Blog::query()
            ->published()
            ->with(['category:id,name'])
            ->orderByDesc('created_at')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $items = $blogs
            ->map(fn (Blog $blog): array => $this->serializeItem($blog, $locale))
            ->all();

        $siteName = $this->siteName();
        $blogHubUrl = LaravelLocalization::getLocalizedURL($locale, '/blog', [], true);
        $selfUrl = $locale === (string) config('app.locale', 'en')
            ? url('/feed.xml')
            : url("/feed/{$locale}.xml");

        $lastBuild = $blogs->first()?->updated_at ?? now();

        return [
            'title' => "{$siteName} — Blog",
            'link' => $blogHubUrl,
            'description' => $this->channelDescription($locale),
            'language' => $this->rssLanguage($locale),
            'lastBuildDate' => $this->formatRssDate($lastBuild),
            'selfUrl' => $selfUrl,
            'items' => $items,
        ];
    }

    /**
     * @return array{
     *     title: string,
     *     link: string,
     *     guid: string,
     *     pubDate: string,
     *     description: string,
     *     content: string,
     *     category: string|null,
     *     imageUrl: string|null,
     *     imageType: string|null
     * }
     */
    private function serializeItem(Blog $blog, string $locale): array
    {
        $title = (string) $blog->getTranslation('title', $locale, true);
        $description = (string) $blog->getTranslation('description', $locale, true);
        $content = (string) $blog->getTranslation('content', $locale, true);
        $metaDescription = (string) $blog->getTranslation('meta_description', $locale, true);

        $excerptSource = trim(strip_tags($metaDescription)) !== ''
            ? $metaDescription
            : $description;

        $excerpt = Str::limit(strip_tags($excerptSource), 300);
        $link = LaravelLocalization::getLocalizedURL($locale, '/blog/'.$blog->slug, [], true);
        $imageUrl = $this->absoluteUrl($blog->image_link);
        $category = $blog->category
            ? (string) $blog->category->getTranslation('name', $locale, true)
            : null;

        return [
            'title' => $title,
            'link' => $link,
            'guid' => $link,
            'pubDate' => $this->formatRssDate($blog->created_at ?? now()),
            'description' => $this->cdataSafe($excerpt),
            'content' => $this->cdataSafe($content !== '' ? $content : $excerpt),
            'category' => $category,
            'imageUrl' => $imageUrl,
            'imageType' => $this->guessImageMimeType($imageUrl),
        ];
    }

    private function siteName(): string
    {
        $name = trim((string) (Seo::get('website_name') ?: ''));

        return $name !== '' ? $name : (string) config('app.name');
    }

    private function channelDescription(string $locale): string
    {
        $previousLocale = app()->getLocale();
        app()->setLocale($locale);

        $description = trim((string) (Seo::get('website_desc') ?: ''));

        app()->setLocale($previousLocale);

        if ($description !== '') {
            return $description;
        }

        return "Latest articles from {$this->siteName()}.";
    }

    private function rssLanguage(string $locale): string
    {
        $regional = config("laravellocalization.supportedLocales.{$locale}.regional");

        return is_string($regional) && $regional !== '' ? $regional : $locale;
    }

    private function formatRssDate(Carbon $date): string
    {
        return $date->copy()->utc()->format('D, d M Y H:i:s').' GMT';
    }

    private function absoluteUrl(string $url): ?string
    {
        if ($url === '' || str_contains($url, 'blank.png')) {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }

    private function guessImageMimeType(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $extension = strtolower((string) pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        return match ($extension) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };
    }

    private function cdataSafe(string $value): string
    {
        return str_replace(']]>', ']]]]><![CDATA[>', $value);
    }
}
