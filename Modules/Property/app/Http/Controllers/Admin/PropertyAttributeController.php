<?php

namespace Modules\Property\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Property\Application\PropertyAttribute\Commands\UpsertPropertyAttributeCommand;
use Modules\Property\Application\PropertyAttribute\PropertyAttributeApplicationService;
use Modules\Property\Enums\AttributeType;
use Modules\Property\Http\Requests\Admin\UpsertPropertyAttributeRequest;
use Modules\Property\Models\PropertyAttribute;

class PropertyAttributeController extends Controller
{
    public function __construct(
        private readonly PropertyAttributeApplicationService $attributeService
    ) {
        $this->setActive('properties');
        $this->setActive('property_attributes');
    }

    public function index()
    {
        $model = $this->attributeService->paginate([
            'id',
            'code',
            'name',
            'type',
            'is_required',
            'is_unique',
            'is_active',
            'created_at',
        ]);

        return view('property::admin.property_attribute.index', compact('model'));
    }

    public function create()
    {
        return view('property::admin.property_attribute.create', $this->formData());
    }

    public function store(UpsertPropertyAttributeRequest $request): RedirectResponse
    {
        $this->attributeService->store(
            UpsertPropertyAttributeCommand::fromValidated($request->validated(), true, true)
        );

        return redirect()->route('admin.property_attributes.index');
    }

    public function edit(PropertyAttribute $property_attribute)
    {
        $property_attribute->load('options');

        return view('property::admin.property_attribute.edit', array_merge(
            $this->formData(),
            ['attribute' => $property_attribute]
        ));
    }

    public function update(
        UpsertPropertyAttributeRequest $request,
        PropertyAttribute $property_attribute
    ): RedirectResponse {
        $this->attributeService->update(
            $property_attribute,
            UpsertPropertyAttributeCommand::fromValidated(
                $request->validated(),
                false,
                $request->boolean('update_translations')
            )
        );

        return redirect()->route('admin.property_attributes.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->attributeService->deleteMulti($request->input('ids'));

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'types' => AttributeType::cases(),
            'validationChoices' => UpsertPropertyAttributeRequest::VALIDATION_CHOICES,
        ];
    }
}
