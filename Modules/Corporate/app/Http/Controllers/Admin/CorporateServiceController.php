<?php

namespace Modules\Corporate\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Core\Support\AdminImageInput;
use Modules\Corporate\Application\CorporateService\Commands\UpsertCorporateServiceCommand;
use Modules\Corporate\Application\CorporateService\CorporateServiceApplicationService;
use Modules\Corporate\Data\CorporateServiceData;
use Modules\Corporate\Models\CorporateService;
use Modules\User\Enums\CmsStatus;

class CorporateServiceController extends Controller
{
    public function __construct(private readonly CorporateServiceApplicationService $corporateServiceService)
    {
        $this->setActive('cms');
        $this->setActive('corporate_services');
    }

    public function index()
    {
        $model = $this->corporateServiceService->paginate(new ContentListQuery(
            publish: request()->query('publish')
        ), [
            'id', 'title', 'slug', 'image', 'status', 'featured', 'visits', 'created_at',
        ]);

        return view('corporate::admin.service.index', compact('model'));
    }

    public function create()
    {
        return view('corporate::admin.service.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->preparePayload($request);
        AdminImageInput::assertPresent($payload['image'] ?? null);

        $data = CorporateServiceData::validate($payload);

        $this->corporateServiceService->store(UpsertCorporateServiceCommand::fromValidated($data));

        return redirect()->route('admin.corporate_services.index');
    }

    public function edit(CorporateService $corporate_service)
    {
        return view('corporate::admin.service.edit', ['corporateService' => $corporate_service]);
    }

    public function update(Request $request, CorporateService $corporate_service): RedirectResponse
    {
        $updateTranslations = $request->boolean('update_translations');

        $payload = $this->preparePayload($request);
        $payload['slug'] = $corporate_service->slug;

        $data = CorporateServiceData::validate($payload);

        $this->corporateServiceService->update(
            $corporate_service,
            UpsertCorporateServiceCommand::fromValidated($data, $updateTranslations)
        );

        return redirect()->route('admin.corporate_services.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->corporateServiceService->deleteMulti($request->input('ids'));

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePayload(Request $request): array
    {
        return [
            'title' => (string) $request->input('title'),
            'slug' => (string) $request->input('slug'),
            'description' => (string) $request->input('description'),
            'content' => (string) $request->input('content'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'meta_keywords' => $request->input('meta_keywords'),
            'image' => AdminImageInput::resolveMediaPathOnly($request, 'img', 'img_media_path'),
            'meta_image' => AdminImageInput::resolveMediaPathOnly($request, 'meta_img', 'meta_img_media_path'),
            'status' => $request->has('publish') ? CmsStatus::PUBLISHED : CmsStatus::ARCHIVED,
            'featured' => $request->boolean('featured'),
        ];
    }
}
