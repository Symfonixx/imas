<?php

namespace Modules\Cms\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Base\Support\Media\LibraryImageRule;
use Modules\Cms\Models\BlogCategory;
use Modules\Cms\Repositories\BlogCategory\BlogCategoryRepository;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Core\Support\AdminImageInput;

class BlogCategoryController extends Controller
{
    protected BlogCategoryRepository $categoryRepository;

    public function __construct(BlogCategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->setActive('cms');
        $this->setActive('blogs_categories');
    }

    public function index()
    {
        $model = $this->categoryRepository->all([
            'id', 'name', 'slug', 'add_to_navbar',
            'meta_title', 'meta_description', 'meta_keywords', 'meta_image',
            'created_at',
        ]);

        return view('cms::admin.blog_category.index', compact('model'));
    }

    public function create()
    {
        return view('cms::admin.blog_category.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->preparePayload($request);
        $this->validateLibraryImages($data);
        $this->categoryRepository->store($data);

        return redirect()->route('admin.blogs_categories.index');
    }

    public function edit(BlogCategory $blogs_category)
    {
        return view('cms::admin.blog_category.edit', compact('blogs_category'));
    }

    public function update(Request $request, BlogCategory $blogs_category): RedirectResponse
    {
        $data = $this->preparePayload($request);
        $data['slug'] = $blogs_category->slug;
        $this->validateLibraryImages($data);
        $this->categoryRepository->update($data, $blogs_category, $request->boolean('update_translations'));

        return redirect()->route('admin.blogs_categories.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->categoryRepository->deleteMulti($request->input('ids'));

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePayload(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'add_to_navbar' => $request->boolean('add_to_navbar'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'meta_keywords' => $request->input('meta_keywords'),
            'meta_image' => AdminImageInput::resolveMediaPathOnly($request, 'meta_img', 'meta_img_media_path'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validateLibraryImages(array $data): void
    {
        $validator = validator(
            ['meta_image' => $data['meta_image'] ?? null],
            ['meta_image' => ['nullable', new LibraryImageRule]]
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }
    }
}
