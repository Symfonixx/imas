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
use Modules\Property\Application\AttributeFamily\AttributeFamilyApplicationService;
use Modules\Property\Application\AttributeFamily\Commands\UpsertAttributeFamilyCommand;
use Modules\Property\Data\AttributeFamilyData;
use Modules\Property\Models\AttributeFamily;
use Modules\Property\Models\PropertyAttribute;

class AttributeFamilyController extends Controller
{
    public function __construct(private readonly AttributeFamilyApplicationService $familyService)
    {
        $this->setActive('properties');
        $this->setActive('attribute_families');
    }

    public function index()
    {
        $model = $this->familyService->paginate(new ContentListQuery, [
            'id', 'name', 'code', 'created_at',
        ]);

        return view('property::admin.attribute_family.index', compact('model'));
    }

    public function create()
    {
        $allAttributes = PropertyAttribute::query()->orderBy('code')->get();

        return view('property::admin.attribute_family.create', compact('allAttributes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->preparePayload($request);
        $validated = $this->validatePayload($payload);

        $sync = $this->buildFamilySync($request);

        $data = AttributeFamilyData::from($validated);
        $this->familyService->store(
            UpsertAttributeFamilyCommand::fromValidated($data->toPayloadArray()),
            $sync
        );

        return redirect()->route('admin.attribute_families.index');
    }

    public function edit(AttributeFamily $attribute_family)
    {
        $allAttributes = PropertyAttribute::query()->orderBy('code')->get();
        $attribute_family->load('attributes');

        return view('property::admin.attribute_family.edit', [
            'family' => $attribute_family,
            'allAttributes' => $allAttributes,
        ]);
    }

    public function update(Request $request, AttributeFamily $attribute_family): RedirectResponse
    {
        $updateTranslations = $request->boolean('update_translations');

        $payload = $this->preparePayload($request);
        $validated = $this->validatePayload($payload, $attribute_family);

        $sync = $this->buildFamilySync($request);

        $data = AttributeFamilyData::from($validated);
        $this->familyService->update(
            $attribute_family,
            UpsertAttributeFamilyCommand::fromValidated($data->toPayloadArray(), $updateTranslations),
            $sync
        );

        return redirect()->route('admin.attribute_families.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->familyService->deleteMulti($request->input('ids'));

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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(array $payload, ?AttributeFamily $family = null): array
    {
        $uniqueCode = Rule::unique('attribute_families', 'code');
        if ($family !== null) {
            $uniqueCode->ignore($family->id);
        }

        return Validator::make($payload, [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'code' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_]*$/', $uniqueCode],
        ])->validate();
    }

    /**
     * @return array<int, array{position: int}>
     */
    private function buildFamilySync(Request $request): array
    {
        $allIds = PropertyAttribute::query()->orderBy('code')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $pairs = [];
        foreach ($allIds as $id) {
            if ($request->boolean("in_family.{$id}")) {
                $pairs[$id] = (int) $request->input("position.{$id}", 0);
            }
        }

        asort($pairs, SORT_NUMERIC);

        $sync = [];
        $index = 0;
        foreach (array_keys($pairs) as $attrId) {
            $sync[$attrId] = ['position' => $index++];
        }

        return $sync;
    }
}
