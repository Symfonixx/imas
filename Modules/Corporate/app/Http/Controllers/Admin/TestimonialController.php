<?php

namespace Modules\Corporate\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Core\Support\AdminImageInput;
use Modules\Corporate\Application\Testimonial\Commands\UpsertTestimonialCommand;
use Modules\Corporate\Application\Testimonial\TestimonialApplicationService;
use Modules\Corporate\Data\TestimonialData;
use Modules\Corporate\Models\Testimonial;
use Modules\User\Enums\CmsStatus;

class TestimonialController extends Controller
{
    public function __construct(private readonly TestimonialApplicationService $testimonialService)
    {
        $this->setActive('corporate');
        $this->setActive('corporate_testimonials');
    }

    public function index()
    {
        $model = $this->testimonialService->paginate(new ContentListQuery(
            publish: request()->query('publish')
        ), [
            'id', 'name', 'client', 'avatar', 'position', 'rank', 'status', 'created_at',
        ]);

        return view('corporate::admin.testimonial.index', compact('model'));
    }

    public function create()
    {
        return view('corporate::admin.testimonial.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = TestimonialData::validate($this->preparePayload($request));

        $this->testimonialService->store(UpsertTestimonialCommand::fromValidated($data));

        return redirect()->route('admin.corporate_testimonials.index');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('corporate::admin.testimonial.edit', ['testimonial' => $testimonial]);
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $updateTranslations = $request->boolean('update_translations');

        $data = TestimonialData::validate($this->preparePayload($request));

        $this->testimonialService->update(
            $testimonial,
            UpsertTestimonialCommand::fromValidated($data, $updateTranslations)
        );

        return redirect()->route('admin.corporate_testimonials.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->testimonialService->deleteMulti($request->input('ids'));

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePayload(Request $request): array
    {
        return [
            'name' => (string) $request->input('name'),
            'client' => (string) $request->input('client'),
            'position' => $request->input('position') !== null && $request->input('position') !== ''
                ? (string) $request->input('position')
                : null,
            'quote' => (string) $request->input('quote'),
            'link' => $request->filled('link') ? (string) $request->input('link') : null,
            'avatar' => AdminImageInput::resolveFileOrMediaPath($request, 'img', 'img_media_path'),
            'rank' => (int) $request->input('rank', 0),
            'status' => $request->has('publish') ? CmsStatus::PUBLISHED : CmsStatus::ARCHIVED,
        ];
    }
}
