<?php

namespace Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Cms\Models\Page;

class PageController extends Controller
{
    /** Slugs owned by other front-office routes; must not be CMS pages. */
    private const RESERVED_SLUGS = [
        'contact-us',
        'blog',
        'property',
        'turkish-citizenship',
        'login',
        'register',
        'admin',
        'api',
    ];

    public function show(string $slug): Response
    {
        if (in_array($slug, self::RESERVED_SLUGS, true)) {
            abort(404);
        }

        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $page->increment('visits');

        return Inertia::render('Cms::PageShow', [
            'title' => (string) $page->title,
            'page' => $this->serializePageDetail($page),
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
