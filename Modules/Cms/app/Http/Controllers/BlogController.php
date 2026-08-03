<?php

namespace Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Base\Support\FrontSeo;
use Modules\Base\Support\FrontViewData;
use Modules\Cms\Models\Blog;
use Modules\Cms\Models\BlogCategory;

class BlogController extends Controller
{
    public function __construct(private readonly FrontViewData $frontViewData) {}

    public function index(Request $request): View
    {
        $validated = Validator::make($request->query(), [
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:blog_categories,id'],
        ])->validated();

        $keyword = isset($validated['q']) ? trim((string) $validated['q']) : '';
        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;

        $query = Blog::query()
            ->published()
            ->with(['category:id,name,slug'])
            ->latest();

        if ($categoryId !== null && $categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        if ($keyword !== '') {
            $this->applyBlogTextFilter($query, $keyword);
        }

        $blogsPaginator = $query->paginate(8)->withQueryString();

        $blogs = $blogsPaginator->through(
            fn (Blog $blog) => $this->serializeBlog($blog),
        );

        $recentBlogs = $this->recentPublishedBlogs();
        $categories = $this->categoriesList();

        $filters = [
            'q' => $keyword !== '' ? $keyword : null,
            'category_id' => $categoryId,
        ];

        $hubTitle = $this->blogHubTitle();
        $globals = $this->frontViewData->sharedGlobals();
        $localeSwitcher = $this->frontViewData->getLocaleSwitcher();
        $appName = $this->frontViewData->sharedAppName();

        return view('cms::front.blog-index', [
            'title' => $hubTitle,
            'blogs' => $blogs,
            'recentBlogs' => $recentBlogs,
            'categories' => $categories,
            'filters' => $filters,
            'seo' => FrontSeo::forHub(
                $hubTitle,
                $globals,
                $localeSwitcher,
                $appName,
                route('blog.index'),
            ),
        ]);
    }

    public function show(string $slug): View
    {
        $blog = Blog::query()
            ->published()
            ->where('slug', $slug)
            ->with(['category:id,name,slug'])
            ->firstOrFail();

        $detail = $this->serializeBlogDetail($blog);
        $globals = $this->frontViewData->sharedGlobals();
        $localeSwitcher = $this->frontViewData->getLocaleSwitcher();
        $appName = $this->frontViewData->sharedAppName();
        $meta = $detail['meta'] ?? [];
        $jsonLd = [];
        $articleSchema = \Modules\Base\Support\StructuredData::buildArticleSchema([
            'headline' => (string) ($detail['title'] ?? ''),
            'description' => (string) ($meta['description'] ?? $detail['excerpt'] ?? ''),
            'url' => (string) ($meta['canonical_url'] ?? $detail['url'] ?? ''),
            'image' => (string) ($meta['image'] ?? $detail['image'] ?? ''),
            'datePublished' => $detail['created_at'] ?? null,
            'dateModified' => $detail['created_at'] ?? null,
        ]);
        if ($articleSchema) {
            $jsonLd[] = $articleSchema;
        }

        return view('cms::front.blog-show', [
            'title' => (string) $blog->title,
            'blog' => $detail,
            'recentBlogs' => $this->recentPublishedBlogs(exceptId: $blog->id),
            'categories' => $this->categoriesList(),
            'filters' => [
                'q' => null,
                'category_id' => $blog->category_id,
            ],
            'seo' => FrontSeo::make([
                'title' => trim((string) ($meta['title'] ?? $blog->title)).' | '.$appName,
                'description' => $meta['description'] ?? null,
                'keywords' => is_string($meta['keywords'] ?? null) ? $meta['keywords'] : null,
                'image' => $meta['image'] ?? ($detail['image'] ?? null),
                'canonical' => $meta['canonical_url'] ?? null,
                'og_type' => 'article',
                'json_ld' => $jsonLd,
            ], $globals, $localeSwitcher, $appName),
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
            ->map(fn (Blog $b) => $this->serializeBlog($b))
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

    /**
     * @return array<string, mixed>
     */
    private function serializeBlog(Blog $blog): array
    {
        $description = (string) ($blog->description ?? '');
        $media = app(\Modules\Base\Support\Media\MediaAssetResolver::class)->resolve($blog->image);

        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'description' => $blog->description,
            'excerpt' => Str::limit(strip_tags($description), 150),
            'image' => $media['url'] ?? $blog->image_link,
            'image_alt' => $media['alt_text'] ?: (string) $blog->title,
            'image_title' => $media['title'] ?? null,
            'featured' => (bool) $blog->featured,
            'visits' => (int) $blog->visits,
            'created_at' => $blog->created_at?->toIso8601String(),
            'date' => $blog->created_at?->locale(app()->getLocale())->translatedFormat('d M Y') ?? '',
            'url' => LaravelLocalization::localizeUrl('/blog/'.$blog->slug),
            'category' => $blog->category
                ? [
                    'id' => $blog->category->id,
                    'name' => $blog->category->name,
                    'slug' => $blog->category->slug,
                ]
                : null,
        ];
    }

    /**
     * Full blog payload for the detail page (content + SEO meta).
     *
     * @return array<string, mixed>
     */
    private function serializeBlogDetail(Blog $blog): array
    {
        $description = (string) ($blog->description ?? '');
        $metaTitle = $blog->meta_title;
        if ($metaTitle === null || trim((string) $metaTitle) === '') {
            $metaTitle = $blog->title;
        }
        $metaDescription = $blog->meta_description;
        if ($metaDescription === null || trim(strip_tags((string) $metaDescription)) === '') {
            $metaDescription = Str::limit(strip_tags($description), 160);
        }
        $resolver = app(\Modules\Base\Support\Media\MediaAssetResolver::class);
        $imageMedia = $resolver->resolve($blog->image);
        $metaMedia = $resolver->resolve($blog->meta_image);

        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'description' => $blog->description,
            'content' => $blog->content,
            'excerpt' => Str::limit(strip_tags($description), 150),
            'image' => $imageMedia['url'] ?? $blog->image_link,
            'image_alt' => $imageMedia['alt_text'] ?: (string) $blog->title,
            'image_title' => $imageMedia['title'] ?? null,
            'meta_image' => $metaMedia['url'] ?? $blog->meta_image_link,
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
                'keywords' => $blog->meta_keywords,
                'image' => $metaMedia['url'] ?? $blog->meta_image_link,
                'canonical_url' => LaravelLocalization::localizeUrl('/blog/'.$blog->slug),
            ],
            'featured' => (bool) $blog->featured,
            'visits' => (int) $blog->visits,
            'created_at' => $blog->created_at?->toIso8601String(),
            'date' => $blog->created_at?->locale(app()->getLocale())->translatedFormat('d M Y') ?? '',
            'url' => LaravelLocalization::localizeUrl('/blog/'.$blog->slug),
            'category' => $blog->category
                ? [
                    'id' => $blog->category->id,
                    'name' => $blog->category->name,
                    'slug' => $blog->category->slug,
                ]
                : null,
        ];
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
