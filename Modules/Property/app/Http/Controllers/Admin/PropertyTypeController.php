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
use Modules\Property\Application\PropertyType\Commands\UpsertPropertyTypeCommand;
use Modules\Property\Application\PropertyType\PropertyTypeApplicationService;
use Modules\Property\Data\PropertyTypeData;
use Modules\Property\Models\AttributeFamily;
use Modules\Property\Models\PropertyType;

class PropertyTypeController extends Controller
{
    public function __construct(private readonly PropertyTypeApplicationService $propertyTypeService)
    {
        $this->setActive('properties');
        $this->setActive('property_types');
    }

    public function index()
    {
        $model = $this->propertyTypeService->paginate(new ContentListQuery, [
            'id', 'name', 'slug', 'icon', 'attribute_family_id', 'created_at',
        ]);

        return view('property::admin.property_type.index', compact('model'));
    }

    public function create()
    {
        return view('property::admin.property_type.create', $this->formShared());
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->preparePayload($request);
        $validated = $this->validatePayload($payload);

        $data = PropertyTypeData::from($validated);
        $this->propertyTypeService->store(UpsertPropertyTypeCommand::fromValidated($data->toPayloadArray()));

        return redirect()->route('admin.property_types.index');
    }

    public function edit(PropertyType $property_type)
    {
        return view('property::admin.property_type.edit', array_merge(
            $this->formShared(),
            ['propertyType' => $property_type]
        ));
    }

    public function update(Request $request, PropertyType $property_type): RedirectResponse
    {
        $updateTranslations = $request->boolean('update_translations');

        $payload = $this->preparePayload($request);
        $validated = $this->validatePayload($payload, $property_type);

        $data = PropertyTypeData::from($validated);
        $this->propertyTypeService->update(
            $property_type,
            UpsertPropertyTypeCommand::fromValidated($data->toPayloadArray(), $updateTranslations)
        );

        return redirect()->route('admin.property_types.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->propertyTypeService->deleteMulti($request->input('ids'));

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function formShared(): array
    {
        return [
            'iconChoices' => config('property.bootstrap_icons', []),
            'families' => AttributeFamily::query()->orderBy('code')->get(['id', 'name', 'code']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePayload(Request $request): array
    {
        return [
            'name' => (string) $request->input('name'),
            'slug' => Str::slug((string) $request->input('slug')),
            'icon' => (string) $request->input('icon'),
            'attribute_family_id' => $request->filled('attribute_family_id') ? (int) $request->input('attribute_family_id') : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(array $payload, ?PropertyType $propertyType = null): array
    {
        $allowedIcons = collect(config('property.bootstrap_icons', []))->pluck('class')->filter()->values()->all();

        $uniqueSlug = Rule::unique('property_types', 'slug');
        if ($propertyType !== null) {
            $uniqueSlug->ignore($propertyType->id);
        }

        return Validator::make($payload, [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $uniqueSlug],
            'icon' => ['required', 'string', 'max:128', Rule::in($allowedIcons)],
            'attribute_family_id' => ['nullable', 'integer', 'exists:attribute_families,id'],
        ])->validate();
    }
}
