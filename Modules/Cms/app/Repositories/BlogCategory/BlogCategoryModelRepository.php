<?php

namespace Modules\Cms\Repositories\BlogCategory;

use App\Http\Middleware\HandleInertiaRequests;
use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Cms\Models\BlogCategory;
use Modules\Core\Traits\ExceptionHandlerTrait;

class BlogCategoryModelRepository implements BlogCategoryRepository
{
    use ExceptionHandlerTrait;

    public function __construct(private readonly ContentPayloadBuilder $payloadBuilder) {}

    public function all(array $columns = ['*']): LengthAwarePaginator
    {
        return BlogCategory::select($columns)->latest()->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?BlogCategory
    {
        return BlogCategory::find($id, $columns);
    }

    public function store(array $data): mixed
    {
        return $this->execute(function () use ($data) {
            $categoryData = $this->prepareCategoryData($data);
            BlogCategory::create($categoryData);
            $this->clearSharedBlogCategoriesCache();
            session()->flushMessage(true);
        });
    }

    private function prepareCategoryData(
        array $data,
        ?BlogCategory $category = null,
        bool $updateTranslations = true
    ): array {
        $payload = $this->payloadBuilder->build(
            data: $data,
            uploadPath: 'cms/blog-categories',
            translatableFields: (new BlogCategory)->translatable,
            imageFields: ['meta_image'],
            existingMedia: $category ? ['meta_image' => $category->meta_image] : [],
            entity: $category,
            updateTranslations: $updateTranslations
        );

        return array_merge($payload, [
            'slug' => $data['slug'] ?? $category?->slug,
            'add_to_navbar' => (bool) ($data['add_to_navbar'] ?? false),
        ]);
    }

    public function update(array $data, BlogCategory $category, bool $updateTranslations = false): mixed
    {
        return $this->execute(function () use ($data, $category, $updateTranslations) {
            $categoryData = $this->prepareCategoryData($data, $category, $updateTranslations);
            $category->update($categoryData);
            $this->clearSharedBlogCategoriesCache();
            session()->flushMessage(true);

            return true;
        });
    }

    public function deleteMulti(array $ids): ?bool
    {
        return $this->execute(function () use ($ids) {
            BlogCategory::destroy($ids);
            $this->clearSharedBlogCategoriesCache();
            session()->flushMessage(true);

            return true;
        });
    }

    private function clearSharedBlogCategoriesCache(): void
    {
        Cache::forget(HandleInertiaRequests::SHARED_BLOG_CATEGORIES_CACHE_KEY);
    }
}
