<?php

namespace Modules\Property\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Property\Application\PropertyAttributeGroup\Commands\UpsertPropertyAttributeGroupCommand;
use Modules\Property\Application\PropertyAttributeGroup\PropertyAttributeGroupApplicationService;
use Modules\Property\Http\Requests\Admin\ReorderPropertyAttributeGroupsRequest;
use Modules\Property\Http\Requests\Admin\UpsertPropertyAttributeGroupRequest;
use Modules\Property\Models\PropertyAttributeGroup;

class PropertyAttributeGroupController extends Controller
{
    public function __construct(
        private readonly PropertyAttributeGroupApplicationService $groupService
    ) {
        $this->setActive('properties');
        $this->setActive('property_attribute_groups');
    }

    public function index()
    {
        return view('property::admin.property_attribute_group.index', [
            'groups' => $this->groupService->groups(),
            'unassignedAttributes' => $this->groupService->unassignedAttributes(),
        ]);
    }

    public function create()
    {
        return view('property::admin.property_attribute_group.create');
    }

    public function store(UpsertPropertyAttributeGroupRequest $request): RedirectResponse
    {
        $this->groupService->store(
            UpsertPropertyAttributeGroupCommand::fromValidated($request->validated(), true)
        );

        return redirect()->route('admin.property_attribute_groups.index');
    }

    public function edit(PropertyAttributeGroup $property_attribute_group)
    {
        return view('property::admin.property_attribute_group.edit', [
            'group' => $property_attribute_group,
        ]);
    }

    public function update(
        UpsertPropertyAttributeGroupRequest $request,
        PropertyAttributeGroup $property_attribute_group
    ): RedirectResponse {
        $this->groupService->update(
            $property_attribute_group,
            UpsertPropertyAttributeGroupCommand::fromValidated(
                $request->validated(),
                $request->boolean('update_translations')
            )
        );

        return redirect()->route('admin.property_attribute_groups.index');
    }

    public function reorder(ReorderPropertyAttributeGroupsRequest $request): RedirectResponse
    {
        $this->groupService->reorder($request->validated('groups'));

        return redirect()->route('admin.property_attribute_groups.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->groupService->deleteMulti($request->input('ids'));

        return back();
    }
}
