<?php

namespace Modules\Cms\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Slide\Commands\UpsertSlideCommand;
use Modules\Cms\Application\Slide\SlideApplicationService;
use Modules\Cms\Data\SlideData;
use Modules\Cms\Models\Slide;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Core\Support\AdminImageInput;
use Modules\User\Enums\CmsStatus;

class SlideController extends Controller
{
    public function __construct(private readonly SlideApplicationService $slideService)
    {
        $this->setActive('cms');
        $this->setActive('slides');
    }

    public function index()
    {
        $model = $this->slideService->paginate(new ContentListQuery(
            publish: request()->query('publish')
        ), [
            'id', 'image', 'main_title', 'subtitle', 'link', 'rank', 'status', 'created_at',
        ]);

        return view('cms::admin.slide.index', compact('model'));
    }

    public function create()
    {
        return view('cms::admin.slide.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->preparePayload($request);
        AdminImageInput::assertPresent($payload['image'] ?? null);

        $data = SlideData::validate($payload);

        $this->slideService->store(UpsertSlideCommand::fromValidated($data));

        return redirect()->route('admin.slides.index');
    }

    public function edit(Slide $slide)
    {
        return view('cms::admin.slide.edit', compact('slide'));
    }

    public function update(Request $request, Slide $slide): RedirectResponse
    {
        $updateTranslations = $request->boolean('update_translations');
        $payload = $this->preparePayload($request);

        $data = SlideData::validate($payload);

        $this->slideService->update($slide, UpsertSlideCommand::fromValidated($data, $updateTranslations));

        return redirect()->route('admin.slides.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->slideService->deleteMulti($request->input('ids'));

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePayload(Request $request): array
    {
        $link = $request->input('link');

        return [
            'main_title' => (string) $request->input('main_title', ''),
            'subtitle' => (string) $request->input('subtitle', ''),
            'link' => $link !== null && trim((string) $link) !== '' ? trim((string) $link) : null,
            'rank' => (int) $request->input('rank', 0),
            'status' => $request->has('publish') ? CmsStatus::PUBLISHED : CmsStatus::ARCHIVED,
            'image' => AdminImageInput::resolveMediaPathOnly($request, 'img', 'img_media_path'),
        ];
    }
}
