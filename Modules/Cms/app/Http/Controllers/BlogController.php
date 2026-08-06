<?php

namespace Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Base\Application\Seo\SeoDocumentService;
use Modules\Base\Support\Seo\SchemaBuilder;
use Modules\Cms\Models\Blog;
use Modules\Cms\Models\BlogCategory;
use Modules\Cms\Support\BlogCardSerializer;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = Validator::make($request->query(), [
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
            'category' => ['sometimes', 'nullable', 'string', 'max:255', 'exists:blog_categories,slug'],
        ])->validated();

        $keyword = isset($validated['q']) ? trim((string) $validated['q']) : '';
        $categorySlug = isset($validated['category']) ? trim((string) $validated['category']) : '';
        if ($categorySlug === '') {
            $categorySlug = null;
        }

        $categoryId = null;
        if ($categorySlug !== null) {
            $categoryId = BlogCategory::query()
                ->where('slug', $categorySlug)
                ->value('id');
        }

        $query = Blog::query()
            ->published()
            ->with(['category:id,name,slug'])
            ->latest();

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        if ($keyword !== '') {
            $this->applyBlogTextFilter($query, $keyword);
        }

        $blogsPaginator = $query->paginate(8)->withQueryString();

        $blogs = $blogsPaginator->through(
            fn (Blog $blog) => BlogCardSerializer::toArray($blog),
        );

        $recentBlogs = $this->recentPublishedBlogs();
        $categories = $this->categoriesList();

        $filters = [
            'q' => $keyword !== '' ? $keyword : null,
            'category' => $categorySlug,
        ];

        $hubTitle = $this->blogHubTitle();

        return Inertia::render('Cms::Index', [
            'title' => $hubTitle,
            'blogs' => $blogs,
            'recentBlogs' => $recentBlogs,
            'categories' => $categories,
            'filters' => $filters,
        ])->withViewData([
            'seo' => app(SeoDocumentService::class)->documentSeo([
                'page_title' => $hubTitle,
                'canonical' => route('blog.index'),
            ]),
        ]);
    }

    public function show(string $slug): Response
    {
        $blog = Blog::query()
            ->published()
            ->where('slug', $slug)
            ->with(['category:id,name,slug'])
            ->firstOrFail();

        $detail = BlogCardSerializer::toDetailArray($blog);
        $seoService = app(SeoDocumentService::class);
        $siteName = $seoService->siteName();
        $meta = is_array($detail['meta'] ?? null) ? $detail['meta'] : [];

        $metaTitle = is_string($meta['title'] ?? null) ? trim(strip_tags($meta['title'])) : '';
        if ($metaTitle === '') {
            $metaTitle = strip_tags((string) $blog->title);
        }
        $documentTitle = $siteName !== '' && ! str_contains($metaTitle, $siteName)
            ? $metaTitle.' | '.$siteName
            : $metaTitle;

        $metaDescription = is_string($meta['description'] ?? null)
            ? trim(strip_tags($meta['description']))
            : '';
        $metaKeywords = $meta['keywords'] ?? null;
        $ogImage = is_string($meta['image'] ?? null) && trim($meta['image']) !== ''
            ? trim($meta['image'])
            : (is_string($detail['image'] ?? null) ? trim($detail['image']) : '');
        $canonical = is_string($meta['canonical_url'] ?? null) ? trim($meta['canonical_url']) : '';

        $publishedAt = $blog->created_at?->toIso8601String() ?? '';
        $modifiedAt = $blog->updated_at?->toIso8601String() ?? $publishedAt;

        $publisherLogo = $seoService->settingsImageUrl('white_logo')
            ?: $seoService->settingsImageUrl('black_logo')
            ?: $seoService->settingsImageUrl('meta_img');

        $articleHeadline = strip_tags((string) ($metaTitle !== '' ? $metaTitle : $blog->title));

        return Inertia::render('Cms::Show', [
            'title' => (string) $blog->title,
            'blog' => $detail,
            'recentBlogs' => $this->recentPublishedBlogs(exceptId: $blog->id),
            'categories' => $this->categoriesList(),
            'filters' => [
                'q' => null,
                'category' => $blog->category?->slug,
            ],
        ])->withViewData([
            'seo' => $seoService->documentSeo([
                'title' => $documentTitle,
                'description' => $metaDescription,
                'keywords' => $metaKeywords,
                'og_image' => $ogImage,
                'canonical' => $canonical,
                'og_type' => 'article',
                'article_published_time' => $publishedAt,
                'article_modified_time' => $modifiedAt,
                'json_ld' => [
                    'jsonld-article' => SchemaBuilder::article(
                        $articleHeadline,
                        $metaDescription !== '' ? strip_tags($metaDescription) : null,
                        $ogImage !== '' ? $ogImage : null,
                        [],
                        $publishedAt !== '' ? $publishedAt : null,
                        $modifiedAt !== '' ? $modifiedAt : null,
                        $canonical !== '' ? $canonical : null,
                        $siteName !== '' ? $siteName : null,
                        $publisherLogo !== '' ? $publisherLogo : null,
                    ),
                ],
            ]),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function categoriesList(): array
    {
        return BlogCategory::query()
            ->orderBy('slug')
            ->get(['id', 'name', 'slug'])
            ->map(static fn (BlogCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentPublishedBlogs(?int $exceptId = null, int $limit = 4): array
    {
        $query = Blog::query()
            ->published()
            ->with(['category:id,name,slug'])
            ->latest();

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->limit($limit)
            ->get()
            ->map(fn (Blog $b) => BlogCardSerializer::toArray($b))
            ->values()
            ->all();
    }

    /**
     * @param  Builder<Blog>  $query
     */
    private function applyBlogTextFilter(Builder $query, string $keyword): void
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', []));
        if ($locales === []) {
            return;
        }

        $pattern = '%'.addcslashes($keyword, '%_\\').'%';

        $query->where(function (Builder $inner) use ($locales, $pattern) {
            foreach ($locales as $locale) {
                $inner->orWhere("title->{$locale}", 'like', $pattern)
                    ->orWhere("description->{$locale}", 'like', $pattern)
                    ->orWhere("content->{$locale}", 'like', $pattern);
            }
        });
    }

    private function blogHubTitle(): string
    {
        $locale = app()->getLocale();
        $path = module_path('Base', "lang/{$locale}.json");

        if (! is_readable($path)) {
            return 'Blog';
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            return 'Blog';
        }

        $blogs = $decoded['blogs'] ?? null;
        if (! is_array($blogs)) {
            return 'Blog';
        }

        $label = $blogs['hub_title'] ?? null;

        return is_string($label) && $label !== '' ? $label : 'Blog';
    }
}
