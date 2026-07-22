<?php

namespace Modules\Property\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Property\Application\SlideCategory\SlideCategoryApplicationService;
use Modules\Property\Http\Requests\Admin\StoreSlideCategoryRequest;
use Modules\Property\Http\Requests\Admin\UpdateSlideCategoryRequest;
use Modules\Property\Models\SlideCategory;
use Modules\User\Enums\CmsStatus;

class SlideCategoryController extends Controller
{
    public function __construct(
        private readonly SlideCategoryApplicationService $slideCategoryService
    ) {
        $this->setActive('properties');
        $this->setActive('slide_categories');
    }

    public function index()
    {
        $model = $this->slideCategoryService->paginate(new ContentListQuery(
            publish: request()->query('publish')
        ), [
            'id',
            'name',
            'description',
            'slug',
            'status',
            'position',
            'created_at',
        ]);

        return view('property::admin.slide_category.index', compact('model'));
    }

    public function create()
    {
        return view('property::admin.slide_category.create', [
            'statuses' => CmsStatus::cases(),
        ]);
    }

    public function store(StoreSlideCategoryRequest $request): RedirectResponse
    {
        $this->slideCategoryService->store($request->validated());

        return redirect()->route('admin.slide_categories.index');
    }

    public function edit(SlideCategory $slide_category)
    {
        return view('property::admin.slide_category.edit', [
            'slideCategory' => $slide_category,
            'statuses' => CmsStatus::cases(),
        ]);
    }

    public function update(
        UpdateSlideCategoryRequest $request,
        SlideCategory $slide_category
    ): RedirectResponse {
        $this->slideCategoryService->update(
            $slide_category,
            $request->validated(),
            $request->boolean('update_translations')
        );

        return redirect()->route('admin.slide_categories.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->slideCategoryService->deleteMulti($request->input('ids'));

        return back();
    }
}
