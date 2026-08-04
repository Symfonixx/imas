<?php

namespace Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
<<<<<<< HEAD
use Inertia\Inertia;
use Inertia\Response;
=======
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
>>>>>>> parent of 07ae30b (convert it to blade)
use Modules\Cms\Models\Blog;
use Modules\Cms\Models\BlogCategory;
use Modules\Cms\Support\BlogCardSerializer;

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
            fn (Blog $blog) => BlogCardSerializer::toArray($blog),
        );

        $recentBlogs = $this->recentPublishedBlogs();
        $categories = $this->categoriesList();

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

    public function show(string $slug): Response
    {
        $blog = Blog::query()
            ->published()
            ->where('slug', $slug)
            ->with(['category:id,name,slug'])
            ->firstOrFail();

        return Inertia::render('Cms::Show', [
            'title' => (string) $blog->title,
<<<<<<< HEAD
            'blog' => BlogCardSerializer::toDetailArray($blog),
=======
            'blog' => $this->serializeBlogDetail($blog),
>>>>>>> parent of 07ae30b (convert it to blade)
            'recentBlogs' => $this->recentPublishedBlogs(exceptId: $blog->id),
            'categories' => $this->categoriesList(),
            'filters' => [
                'q' => null,
                'category_id' => $blog->category_id,
            ],
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
