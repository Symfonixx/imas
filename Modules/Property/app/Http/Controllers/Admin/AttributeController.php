<?php

namespace Modules\Property\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Property\Application\Attribute\AttributeApplicationService;
use Modules\Property\Application\Attribute\Commands\UpsertAttributeCommand;
use Modules\Property\Data\AttributeData;
use Modules\Property\Enums\AttributeType;
use Modules\Property\Models\PropertyAttribute;

class AttributeController extends Controller
{
    public function __construct(private readonly AttributeApplicationService $attributeService)
    {
        $this->setActive('properties');
        $this->setActive('attributes');
    }

    public function index()
    {
        $model = $this->attributeService->paginate(new ContentListQuery(
            publish: null,
            type: request()->query('type')
        ), [
            'id', 'name', 'code', 'type', 'is_filterable', 'is_required', 'is_trans', 'created_at',
        ]);

        return view('property::admin.attribute.index', compact('model'));
    }

    public function create()
    {
        return view('property::admin.attribute.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->preparePayload($request);
        $validated = $this->validatePayload($payload);

        $data = AttributeData::from($validated);
        $this->attributeService->store(UpsertAttributeCommand::fromValidated($data->toPayloadArray()));

        return redirect()->route('admin.attributes.index');
    }

    public function edit(PropertyAttribute $attribute)
    {
        return view('property::admin.attribute.edit', compact('attribute'));
    }

    public function update(Request $request, PropertyAttribute $attribute): RedirectResponse
    {
        $updateTranslations = $request->boolean('update_translations');

        $payload = $this->preparePayload($request);
        $validated = $this->validatePayload($payload, $attribute);

        $data = AttributeData::from($validated);
        $this->attributeService->update(
            $attribute,
            UpsertAttributeCommand::fromValidated($data->toPayloadArray(), $updateTranslations)
        );

        return redirect()->route('admin.attributes.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->attributeService->deleteMulti($request->input('ids'));

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePayload(Request $request): array
    {
        return [
            'name' => (string) $request->input('name'),
            'code' => Str::lower(trim((string) $request->input('code'))),
            'type' => (string) $request->input('type'),
            'is_filterable' => $request->boolean('is_filterable'),
            'is_required' => $request->boolean('is_required'),
            'is_trans' => $request->boolean('is_trans'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(array $payload, ?PropertyAttribute $attribute = null): array
    {
        $uniqueCode = Rule::unique('attributes', 'code');
        if ($attribute !== null) {
            $uniqueCode->ignore($attribute->id);
        }

        return Validator::make($payload, [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'code' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_]*$/', $uniqueCode],
            'type' => ['required', Rule::enum(AttributeType::class)],
            'is_filterable' => ['boolean'],
            'is_required' => ['boolean'],
            'is_trans' => ['boolean'],
        ])->validate();
    }
}
