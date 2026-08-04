<?php

namespace Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Base\Support\FrontSeo;
use Modules\Base\Support\FrontViewData;
use Modules\Cms\Models\Page;

class PageController extends Controller
{
    /** Slugs owned by other front-office routes; must not be CMS pages. */
    private const RESERVED_SLUGS = [
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

    public function __construct(private readonly FrontViewData $frontViewData) {}

    public function show(string $slug): View
    {
        if (in_array($slug, self::RESERVED_SLUGS, true)) {
            abort(404);
        }

        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $page->increment('visits');

        $detail = $this->serializePageDetail($page);
        $globals = $this->frontViewData->sharedGlobals();
        $localeSwitcher = $this->frontViewData->getLocaleSwitcher();
        $appName = $this->frontViewData->sharedAppName();
        $meta = $detail['meta'] ?? [];

        return view('cms::front.page-show', [
            'title' => (string) $page->title,
            'page' => $detail,
            'seo' => FrontSeo::make([
                'title' => trim((string) ($meta['title'] ?? $page->title)).' | '.$appName,
                'description' => $meta['description'] ?? null,
                'keywords' => is_string($meta['keywords'] ?? null) ? $meta['keywords'] : null,
                'image' => $meta['image'] ?? ($detail['image'] ?? null),
                'canonical' => $meta['canonical_url'] ?? null,
            ], $globals, $localeSwitcher, $appName),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePageDetail(Page $page): array
    {
        $content = (string) ($page->content ?? '');
        $metaTitle = $page->meta_title;
        if ($metaTitle === null || trim((string) $metaTitle) === '') {
            $metaTitle = $page->title;
        }
        $metaDescription = $page->meta_description;
        if ($metaDescription === null || trim(strip_tags((string) $metaDescription)) === '') {
            $metaDescription = Str::limit(strip_tags($content), 160);
        }

        $canonicalUrl = LaravelLocalization::localizeUrl('/'.$page->slug);

        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'image' => $page->image_link,
            'meta_image' => $page->meta_image_link,
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
                'keywords' => $page->meta_keywords,
                'image' => $page->meta_image_link,
                'canonical_url' => $canonicalUrl,
            ],
            'featured' => (bool) $page->featured,
            'visits' => (int) $page->visits,
            'url' => $canonicalUrl,
        ];
    }
}
