<?php

namespace Modules\Property\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Property\Application\Location\Commands\UpsertLocationCommand;
use Modules\Property\Application\Location\LocationApplicationService;
use Modules\Property\Data\LocationData;
use Modules\Property\Models\Location;

class LocationController extends Controller
{
    public function __construct(private readonly LocationApplicationService $locationService)
    {
        $this->setActive('properties');
        $this->setActive('locations');
    }

    public function index()
    {
        $tree = Location::nestedForest(request()->query('type'));

        return view('property::admin.location.index', compact('tree'));
    }

    public function create()
    {
        $parentOptions = Location::orderedForAdmin();

        return view('property::admin.location.create', compact('parentOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = LocationData::validate($this->preparePayload($request));
        $payload = LocationData::from($validated)->toPayloadArray();

        $this->locationService->store(UpsertLocationCommand::fromValidated($payload));

        return redirect()->route('admin.locations.index');
    }

    public function edit(Location $location)
    {
        $blockedIds = array_merge([$location->id], Location::descendantIdsOf($location->id));
        $parentOptions = Location::query()
            ->whereNotIn('id', $blockedIds)
            ->with('parent:id,name')
            ->orderByRaw('parent_id is null desc')
            ->orderBy('id')
            ->get();

        return view('property::admin.location.edit', compact('location', 'parentOptions'));
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $updateTranslations = $request->boolean('update_translations');

        $validated = LocationData::validate($this->preparePayload($request));
        $payload = LocationData::from($validated)->toPayloadArray();

        $this->locationService->update(
            $location,
            UpsertLocationCommand::fromValidated($payload, $updateTranslations)
        );

        return redirect()->route('admin.locations.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->locationService->deleteMulti($request->input('ids'));

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePayload(Request $request): array
    {
        return [
            'name' => (string) $request->input('name'),
            'parent_id' => $request->filled('parent_id') ? (int) $request->input('parent_id') : null,
            'type' => (string) $request->input('type'),
        ];
    }
}
