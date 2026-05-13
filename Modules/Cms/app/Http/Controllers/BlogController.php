<?php

namespace Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Cms\Models\Blog;
use Modules\Cms\Models\BlogCategory;

class BlogController extends Controller
{
    public function index(Request $request): Response
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

        $recentBlogs = Blog::query()
            ->published()
            ->with(['category:id,name,slug'])
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (Blog $blog) => $this->serializeBlog($blog))
            ->values()
            ->all();

        $categories = BlogCategory::query()
            ->orderBy('slug')
            ->get(['id', 'name', 'slug'])
            ->map(static fn (BlogCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->values()
            ->all();

        $filters = [
            'q' => $keyword !== '' ? $keyword : null,
            'category_id' => $categoryId,
        ];

        return Inertia::render('Cms::Index', [
            'title' => $this->blogHubTitle(),
            'blogs' => $blogs,
            'recentBlogs' => $recentBlogs,
            'categories' => $categories,
            'filters' => $filters,
        ]);
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

        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'description' => $blog->description,
            'excerpt' => Str::limit(strip_tags($description), 150),
            'image' => $blog->image_link,
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
