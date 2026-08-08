<?php

namespace Modules\Property\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Base\Support\Media\LibraryImageRule;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Core\Support\AdminImageInput;
use Modules\Property\Application\PropertyAttributeValue\PropertyAttributeFormSchemaService;
use Modules\Property\Application\PropertyAttributeValue\PropertyAttributeValueSyncService;
use Modules\Property\Application\SlideCategory\PropertySlideMediaSyncService;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\ProjectUnitType;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertySlideMedia;
use Modules\Property\Models\PropertyType;
use Modules\Property\Models\SlideCategory;
use Modules\Property\Support\PropertyMetricsFromUnitTypes;
use Modules\User\Enums\CmsStatus;
use Throwable;

class PropertyController extends Controller
{
    public function __construct(
        private readonly PropertyAttributeFormSchemaService $attributeFormSchema,
        private readonly PropertyAttributeValueSyncService $attributeValueSync,
        private readonly PropertySlideMediaSyncService $slideMediaSync,
    ) {
        $this->setActive('properties');
        $this->setActive('property_items');
    }

    public function index()
    {
        $this->setActive('projects');
        $model = Property::query()
            ->with(['location:id,name,type', 'propertyType:id,name'])
            ->latest()
            ->paginate(config('core.page_size', 10));

        return view('property::admin.property.index', compact('model'));
    }

    public function create()
    {
        $this->setActive('projects');

        return view('property::admin.property.create', $this->formShared());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->filterEmptyUnitTypes($this->validatePayload($request));
        $groupIds = array_map('intval', $validated['attribute_group_ids'] ?? []);
        $this->attributeValueSync->validate($request, new Property, true, $groupIds);
        $attributeMediaChanges = null;
        $slideMediaChanges = null;

        DB::beginTransaction();

        try {
            $payload = $this->buildPropertyPayload($request, $validated);
            $property = Property::query()->create(array_merge(
                $payload,
                $this->metricsFromUnitTypeRows($validated['unit_types'] ?? [])
            ));
            $this->syncAttributeGroups($property, $groupIds);
            $this->syncSlideCategories($property, $validated['slide_category_ids'] ?? []);
            $slideMediaChanges = $this->slideMediaSync->synchronize(
                $request,
                $property,
                $validated['slide_category_ids'] ?? []
            );
            if (array_key_exists('unit_types', $validated)) {
                $this->syncUnitTypes($property, $validated['unit_types']);
            }
            $attributeMediaChanges = $this->attributeValueSync->synchronize($request, $property, true, $groupIds);
            $this->syncSimilarProperties($property, $validated['similar_property_ids'] ?? []);
            DB::commit();
            $attributeMediaChanges->finalize();
            $slideMediaChanges->finalize();
        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $attributeMediaChanges?->rollback();
            $slideMediaChanges?->rollback();

            throw $e;
        }

        session()->flushMessage(true);

        return redirect()->route('admin.properties.index');
    }

    public function edit(Property $property)
    {
        $this->setActive('projects');
        $property->load(['slideCategories', 'slideMedia', 'unitTypes', 'similarProperties', 'location.parent.parent', 'attributeGroups']);

        return view('property::admin.property.edit', $this->formShared($property));
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $validated = $this->filterEmptyUnitTypes($this->validatePayload($request, $property));
        $groupIds = $request->boolean('attribute_group_ids_present') || array_key_exists('attribute_group_ids', $validated)
            ? array_map('intval', $validated['attribute_group_ids'] ?? [])
            : $property->attributeGroups()->pluck('property_attribute_groups.id')->map(fn ($id) => (int) $id)->all();
        $this->attributeValueSync->validate($request, $property, false, $groupIds);
        $attributeMediaChanges = null;
        $slideMediaChanges = null;
        $oldThumbnailPath = $property->thumbnail;
        $finalThumbnailPath = $oldThumbnailPath;

        DB::beginTransaction();

        try {
            $payload = $this->buildPropertyPayload($request, $validated, $property);
            $finalThumbnailPath = $payload['thumbnail'];

            if ($request->filled('unit_types_sync_empty')) {
                $payload = array_merge($payload, $this->metricsFromUnitTypeRows([]));
            } elseif (array_key_exists('unit_types', $validated)) {
                $payload = array_merge($payload, $this->metricsFromUnitTypeRows($validated['unit_types']));
            }

            $property->update($payload);
            $this->syncAttributeGroups($property, $groupIds);
            $this->syncSlideCategories($property, $validated['slide_category_ids'] ?? []);
            $slideMediaChanges = $this->slideMediaSync->synchronize(
                $request,
                $property,
                $validated['slide_category_ids'] ?? []
            );

            if ($request->filled('unit_types_sync_empty')) {
                $this->syncUnitTypes($property, []);
            } elseif (array_key_exists('unit_types', $validated)) {
                $this->syncUnitTypes($property, $validated['unit_types']);
            }

            $attributeMediaChanges = $this->attributeValueSync->synchronize($request, $property, false, $groupIds);
            $this->syncSimilarProperties($property, $validated['similar_property_ids'] ?? []);
            DB::commit();
            $attributeMediaChanges->finalize();
            $slideMediaChanges->finalize();
            if (is_string($oldThumbnailPath)
                && $oldThumbnailPath !== $finalThumbnailPath
                && ! Str::startsWith(ltrim($oldThumbnailPath, '/'), 'media-library/')
            ) {
                Storage::disk('public')->delete($oldThumbnailPath);
            }
        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $attributeMediaChanges?->rollback();
            $slideMediaChanges?->rollback();

            throw $e;
        }

        session()->flushMessage(true);

        return redirect()->route('admin.properties.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $ids = array_map('intval', (array) $request->input('ids', []));
        $properties = Property::query()
            ->with('slideMedia:id,property_id,path')
            ->whereIn('id', $ids)
            ->get();

        DB::transaction(fn () => Property::query()->whereIn('id', $ids)->delete());

        $properties->each(function (Property $property): void {
            $paths = array_filter([
                $property->thumbnail,
            ], fn (mixed $path): bool => is_string($path)
                && $path !== ''
                && ! Str::startsWith(ltrim($path, '/'), 'media-library/'));

            Storage::disk('public')->delete($paths);

            foreach ($property->slideMedia->pluck('path')->filter()->unique() as $slidePath) {
                if (PropertySlideMedia::isOwnedStoragePath($slidePath)
                    && ! PropertySlideMedia::query()->where('path', $slidePath)->exists()
                ) {
                    Storage::disk('public')->delete($slidePath);
                }
            }

            Storage::disk('public')->deleteDirectory("properties/attributes/{$property->id}");
            Storage::disk('public')->deleteDirectory("properties/slides/{$property->id}");
        });

        session()->flushMessage(true);

        return back();
    }

    public function locationChildren(Request $request): JsonResponse
    {
        $parentId = $request->query('parent_id');
        $type = (string) $request->query('type');
        $resolvedParentId = $parentId === null || $parentId === '' ? null : (int) $parentId;

        // Areas for a city: direct children of the city + children of its municipalities.
        if (
            $request->boolean('for_city')
            && $resolvedParentId !== null
            && $type === LocationType::Area->value
        ) {
            $municipalityIds = Location::query()
                ->where('parent_id', $resolvedParentId)
                ->where('type', LocationType::Municipality->value)
                ->pluck('id');

            $items = Location::query()
                ->where('type', LocationType::Area->value)
                ->where(function ($query) use ($resolvedParentId, $municipalityIds): void {
                    $query->where('parent_id', $resolvedParentId)
                        ->orWhereIn('parent_id', $municipalityIds);
                })
                ->orderBy('id')
                ->get(['id', 'name', 'type', 'parent_id']);
        } else {
            $items = Location::query()
                ->where('parent_id', $resolvedParentId)
                ->when($type !== '', fn ($query) => $query->where('type', $type))
                ->orderBy('id')
                ->get(['id', 'name', 'type', 'parent_id']);
        }

        return response()->json([
            'items' => $items->map(fn (Location $location): array => [
                'id' => $location->id,
                'name' => $this->translatedName($location->name),
                'type' => $location->type instanceof LocationType ? $location->type->value : (string) $location->type,
                'parent_id' => $location->parent_id,
            ])->values(),
        ]);
    }

    public function attributeGroupSchema(Request $request): JsonResponse
    {
        $groupIds = collect($request->query('group_ids', []))
            ->when(
                $request->filled('group_id'),
                fn ($ids) => $ids->push($request->integer('group_id'))
            )
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
        $propertyId = $request->integer('property_id') ?: null;
        $property = $propertyId
            ? Property::query()->find($propertyId)
            : null;

        $groups = $this->attributeFormSchema->forGroups($groupIds, $property);

        $html = view('property::admin.property.partials._attributes', [
            'attributeGroups' => $groups,
            'isEdit' => $property !== null,
        ])->render();

        return response()->json([
            'html' => $html,
            'groups' => $groups->map(fn (array $group): array => [
                'id' => $group['id'],
                'name' => $this->translatedName($group['name']),
            ])->values()->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formShared(?Property $property = null): array
    {
        $cities = Location::query()
            ->where('type', LocationType::City->value)
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(fn (Location $city): array => [
                'id' => $city->id,
                'name' => $this->translatedName($city->name),
            ])
            ->values();

        $propertyTypes = PropertyType::query()
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(fn (PropertyType $propertyType): array => [
                'id' => $propertyType->id,
                'name' => $this->translatedName($propertyType->name),
            ])
            ->values();

        $projectUnitTypesCatalog = ProjectUnitType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(fn (ProjectUnitType $row): array => [
                'id' => $row->id,
                'name' => $this->translatedName($row->name),
            ])
            ->values();

        $slideCategories = SlideCategory::query()
            ->orderBy('position')
            ->orderBy('id')
            ->get(['id', 'name', 'status'])
            ->map(fn (SlideCategory $slideCategory): array => [
                'id' => $slideCategory->id,
                'name' => $this->translatedName($slideCategory->name),
                'status' => $slideCategory->status?->value ?? (string) $slideCategory->status,
            ])
            ->values();

        $propertiesForSimilar = Property::query()
            ->orderByDesc('updated_at')
            ->get(['id', 'project_code', 'title', 'project_name'])
            ->map(fn (Property $row): array => [
                'id' => $row->id,
                'label' => $this->propertyOptionLabel($row),
            ])
            ->values();

        $attributeGroupOptions = collect($this->attributeFormSchema->activeGroupOptions())
            ->map(fn (array $group): array => [
                'id' => $group['id'],
                'name' => $this->translatedName($group['name']),
            ])
            ->values();

        return [
            'property' => $property,
            'attributeGroups' => $this->attributeFormSchema->forProperty($property),
            'attributeGroupOptions' => $attributeGroupOptions,
            ...$this->formLocationSelection($property),
            'propertyTypes' => $propertyTypes,
            'cities' => $cities,
            'projectUnitTypesCatalog' => $projectUnitTypesCatalog,
            'slideCategories' => $slideCategories,
            'propertiesForSimilar' => $propertiesForSimilar,
            'statuses' => CmsStatus::cases(),
        ];
    }

    /**
     * @return array{prefillCityId: ?int, prefillDistrictId: mixed, selectedAreaIdValue: mixed}
     */
    private function formLocationSelection(?Property $property): array
    {
        $cityId = null;
        $districtId = old('district_id');
        $areaId = old('area_id');
        $location = null;

        if ($districtId === null && $areaId === null && $property?->location_id) {
            $location = $property->relationLoaded('location')
                ? $property->location
                : Location::query()->with(['parent.parent'])->find((int) $property->location_id);
        } elseif ($districtId !== null || $areaId !== null) {
            $location = Location::query()
                ->with(['parent:id,parent_id,type', 'parent.parent:id,parent_id,type'])
                ->find((int) ($areaId ?: $districtId));
        }

        if ($location?->type === LocationType::Area && $location->parent) {
            $areaId ??= $location->id;
            if ($location->parent->type === LocationType::Municipality) {
                $districtId ??= $location->parent_id;
                $cityId = $location->parent->parent_id;
            } elseif ($location->parent->type === LocationType::City) {
                // Area can belong directly to a city (no municipality).
                $districtId = $districtId !== null && $districtId !== '' ? $districtId : null;
                $cityId = $location->parent->id;
            }
        } elseif ($location?->type === LocationType::Municipality) {
            $districtId ??= $location->id;
            $cityId = $location->parent_id;
        }

        return [
            'prefillCityId' => $cityId === null ? null : (int) $cityId,
            'prefillDistrictId' => $districtId,
            'selectedAreaIdValue' => $areaId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?Property $property = null): array
    {
        $uniqueProjectCode = Rule::unique('properties', 'project_code');
        $uniqueUrlKey = Rule::unique('properties', 'url_key');
        if ($property !== null) {
            $uniqueProjectCode->ignore($property->id);
            $uniqueUrlKey->ignore($property->id);
        }

        $unitTypeIdRules = ['nullable', 'integer'];
        if ($property !== null) {
            $unitTypeIdRules[] = Rule::exists('unit_types', 'id')->where('property_id', $property->id);
        }

        $slideMediaIdRules = ['integer', 'distinct'];
        $slideMediaIdRules[] = $property === null
            ? Rule::exists('property_slide_media', 'id')
            : Rule::exists('property_slide_media', 'id')->where('property_id', $property->id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'project_name' => ['required', 'string', 'max:255'],
            'project_code' => ['required', 'string', 'max:128', $uniqueProjectCode],
            'url_key' => [
                'required',
                'string',
                'max:191',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $uniqueUrlKey,
            ],
            'overview' => ['nullable', 'string'],
            'thumbnail' => ['prohibited'],
            'thumbnail_media_path' => ['nullable', new LibraryImageRule],
            'meta_img' => ['prohibited'],
            'meta_img_media_path' => ['nullable', new LibraryImageRule],
            'district_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->where('type', LocationType::Municipality->value)],
            'area_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->where('type', LocationType::Area->value)],
            'property_type_id' => ['required', 'integer', 'exists:property_types,id'],
            'attribute_group_ids' => ['nullable', 'array', 'max:50'],
            'attribute_group_ids.*' => ['integer', 'distinct', 'exists:property_attribute_groups,id'],
            'attribute_group_ids_present' => ['nullable', 'boolean'],
            'is_sold_out' => ['nullable', 'boolean'],
            'is_recommended' => ['nullable', 'boolean'],
            'is_citizenship_eligible' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'why_to_buy' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'youtube_video_url' => ['nullable', 'url', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['nullable', Rule::in(array_map(static fn (CmsStatus $status) => $status->value, CmsStatus::cases()))],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable'],
            'meta_schema' => ['nullable', 'string'],
            'slide_category_ids' => ['nullable', 'array', 'max:20'],
            'slide_category_ids.*' => ['integer', 'distinct', 'exists:slide_categories,id'],
            'slide_category_ids_present' => ['nullable', 'boolean'],
            'slide_media' => ['nullable', 'array'],
            'slide_media.*.images' => ['prohibited'],
            'slide_media.*.images_media_paths' => ['nullable', 'array', 'max:20'],
            'slide_media.*.images_media_paths.*' => ['string', new LibraryImageRule],
            'slide_media.*.videos' => ['nullable', 'array', 'max:10'],
            'slide_media.*.videos.*' => [
                'file',
                'mimetypes:video/mp4,video/webm,video/quicktime',
                'max:102400',
            ],
            'remove_slide_media_ids' => ['nullable', 'array'],
            'remove_slide_media_ids.*' => $slideMediaIdRules,
            'unit_types' => ['nullable', 'array', 'max:100'],
            'unit_types.*.id' => $unitTypeIdRules,
            'unit_types.*.catalog_id' => [
                'nullable',
                'integer',
                Rule::exists('project_unit_types', 'id'),
            ],
            'unit_types.*.name' => ['nullable', 'string', 'max:255'],
            'unit_types.*.min_area' => ['nullable', 'numeric', 'min:0'],
            'unit_types.*.max_area' => ['nullable', 'numeric', 'min:0'],
            'unit_types.*.price' => ['nullable', 'numeric', 'min:0'],
            'similar_property_ids' => ['nullable', 'array', 'max:12'],
            'similar_property_ids.*' => ['integer', 'distinct', 'exists:properties,id'],
        ]);

        if (! empty($validated['area_id'])) {
            $area = Location::query()
                ->with(['parent:id,parent_id,type'])
                ->find((int) $validated['area_id']);

            if ($area === null || $area->type !== LocationType::Area) {
                throw ValidationException::withMessages([
                    'area_id' => __('Invalid area selection.'),
                ]);
            }

            $areaParentType = $area->parent?->type instanceof LocationType
                ? $area->parent->type->value
                : (string) ($area->parent?->type ?? '');

            if (! empty($validated['district_id'])) {
                if ((int) $area->parent_id !== (int) $validated['district_id']) {
                    throw ValidationException::withMessages([
                        'area_id' => __('Invalid area selection.'),
                    ]);
                }
            } elseif ($areaParentType !== LocationType::City->value) {
                // Area without municipality is only valid when the area is a direct child of a city.
                throw ValidationException::withMessages([
                    'district_id' => __('The municipality field is required when area is selected.'),
                ]);
            }
        }

        $validated['location_id'] = $this->resolveLocationId($validated);
        $validated['status'] = $validated['status'] ?? CmsStatus::PUBLISHED->value;

        if ($property !== null && ! empty($validated['similar_property_ids'])) {
            $validated['similar_property_ids'] = array_values(array_filter(
                array_map('intval', $validated['similar_property_ids']),
                fn (int $id): bool => $id !== $property->id
            ));
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveLocationId(array $validated): ?int
    {
        if (! empty($validated['area_id'])) {
            return (int) $validated['area_id'];
        }

        if (! empty($validated['district_id'])) {
            return (int) $validated['district_id'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function filterEmptyUnitTypes(array $validated): array
    {
        if (! array_key_exists('unit_types', $validated) || ! is_array($validated['unit_types'])) {
            return $validated;
        }

        $validated['unit_types'] = array_values(array_filter(
            $validated['unit_types'],
            static function (mixed $row): bool {
                if (! is_array($row)) {
                    return false;
                }
                if (isset($row['catalog_id']) && (int) $row['catalog_id'] > 0) {
                    return true;
                }

                return trim((string) ($row['name'] ?? '')) !== '';
            }
        ));

        if ($validated['unit_types'] === []) {
            unset($validated['unit_types']);
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildPropertyPayload(Request $request, array $validated, ?Property $property = null): array
    {
        $autoTranslate = $property === null;

        $thumbnailResolved = AdminImageInput::resolveMediaPathOnly($request, 'thumbnail', 'thumbnail_media_path');
        if ($thumbnailResolved === AdminImageInput::REMOVED) {
            $thumbnailPath = null;
        } elseif (is_string($thumbnailResolved)) {
            $thumbnailPath = $thumbnailResolved;
        } else {
            $thumbnailPath = $property?->thumbnail;
        }

        $existingMeta = is_array($property?->metadata) ? $property->metadata : [];
        $metaImgResolved = AdminImageInput::resolveMediaPathOnly($request, 'meta_img', 'meta_img_media_path');
        if ($metaImgResolved === AdminImageInput::REMOVED) {
            $metaImg = null;
        } elseif (is_string($metaImgResolved)) {
            $metaImg = $metaImgResolved;
        } else {
            $metaImg = $existingMeta['meta_img'] ?? null;
        }

        return [
            'thumbnail' => $thumbnailPath,
            'project_code' => $validated['project_code'],
            'url_key' => $validated['url_key'],
            'title' => $this->buildTranslatedValue(
                $validated['title'],
                $property?->getTranslations('title') ?? [],
                $autoTranslate
            ),
            'project_name' => $this->buildTranslatedValue(
                $validated['project_name'],
                $property?->getTranslations('project_name') ?? [],
                $autoTranslate
            ),
            'overview' => $this->buildTranslatedValue(
                $validated['overview'] ?? null,
                $property?->getTranslations('overview') ?? [],
                $autoTranslate
            ),
            'location_id' => $validated['location_id'] ?? null,
            'property_type_id' => (int) $validated['property_type_id'],
            'is_sold_out' => $request->boolean('is_sold_out'),
            'is_recommended' => $request->boolean('is_recommended'),
            'is_citizenship_eligible' => $request->boolean('is_citizenship_eligible'),
            'is_featured' => $request->boolean('is_featured'),
            'why_to_buy' => $this->buildTranslatedValue(
                $validated['why_to_buy'] ?? null,
                $property?->getTranslations('why_to_buy') ?? [],
                $autoTranslate
            ),
            'content' => $this->buildTranslatedValue(
                $validated['content'] ?? null,
                $property?->getTranslations('content') ?? [],
                $autoTranslate
            ),
            'youtube_video_url' => $this->normalizeYoutubeUrl($validated['youtube_video_url'] ?? null),
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'status' => $validated['status'] ?? CmsStatus::PUBLISHED->value,
            'metadata' => [
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'meta_keywords' => $this->normalizeKeywords($validated['meta_keywords'] ?? null),
                'schema' => $validated['meta_schema'] ?? null,
                'meta_img' => $metaImg,
            ],
        ];
    }

    /**
     * Replace or upsert rows from the request: existing ids are updated, new rows are created,
     * and any stored unit types not present in the payload are removed.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncUnitTypes(Property $property, array $rows): void
    {
        $keepIds = [];

        foreach ($rows as $row) {
            $catalogId = isset($row['catalog_id']) ? (int) $row['catalog_id'] : 0;
            $catalog = $catalogId > 0
                ? ProjectUnitType::query()->whereKey($catalogId)->first()
                : null;

            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '' && $catalog !== null) {
                $name = trim($this->translatedName($catalog->name));
            }
            if ($name === '') {
                continue;
            }

            $data = [
                'catalog_id' => $catalog?->id,
                'name' => $name,
                'min_area' => $this->nullableDecimal($row['min_area'] ?? null),
                'max_area' => $this->nullableDecimal($row['max_area'] ?? null),
                'price' => $this->nullableDecimal($row['price'] ?? null),
            ];

            $id = isset($row['id']) ? (int) $row['id'] : 0;
            $unitType = $id > 0
                ? $property->unitTypes()->whereKey($id)->first()
                : null;

            if ($unitType !== null) {
                $unitType->update($data);
                $keepIds[] = $unitType->id;

                continue;
            }

            $created = $property->unitTypes()->create($data);
            $keepIds[] = $created->id;
        }

        if ($keepIds === []) {
            $property->unitTypes()->delete();

            return;
        }

        $property->unitTypes()->whereNotIn('id', $keepIds)->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{price: float, min_area: ?float, max_area: ?float}
     */
    private function metricsFromUnitTypeRows(array $rows): array
    {
        return PropertyMetricsFromUnitTypes::fromRows($rows);
    }

    private function nullableDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    /**
     * @param  list<int>  $similarPropertyIds
     */
    private function syncSimilarProperties(Property $property, array $similarPropertyIds): void
    {
        $syncData = [];

        foreach (array_values(array_unique(array_map('intval', $similarPropertyIds))) as $index => $similarPropertyId) {
            if ($similarPropertyId <= 0 || $similarPropertyId === $property->id) {
                continue;
            }

            $syncData[$similarPropertyId] = ['sort_order' => $index];
        }

        $property->similarProperties()->sync($syncData);
    }

    private function propertyOptionLabel(Property $property): string
    {
        $title = $this->translatedName($property->project_name ?: $property->title);
        $code = trim((string) $property->project_code);

        if ($code !== '' && $title !== '') {
            return "{$title} ({$code})";
        }

        return $title !== '' ? $title : $code;
    }

    /**
     * @param  list<int>  $groupIds
     */
    private function syncAttributeGroups(Property $property, array $groupIds): void
    {
        $syncData = [];
        foreach (array_values(array_unique(array_map('intval', $groupIds))) as $index => $groupId) {
            if ($groupId <= 0) {
                continue;
            }
            $syncData[$groupId] = ['position' => $index];
        }

        $property->attributeGroups()->sync($syncData);
    }

    /**
     * @param  list<int>  $slideCategoryIds
     */
    private function syncSlideCategories(Property $property, array $slideCategoryIds): void
    {
        $property->slideCategories()->sync(
            array_values(array_unique(array_map('intval', $slideCategoryIds)))
        );
    }

    private function normalizeKeywords(mixed $keywords): array
    {
        if (is_array($keywords)) {
            return array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $keywords)));
        }

        if (is_string($keywords)) {
            $decoded = json_decode($keywords, true);
            if (is_array($decoded)) {
                $values = [];
                foreach ($decoded as $item) {
                    if (is_array($item) && isset($item['value'])) {
                        $values[] = trim((string) $item['value']);
                    } elseif (is_string($item)) {
                        $values[] = trim($item);
                    }
                }

                return array_values(array_filter($values));
            }

            return array_values(array_filter(array_map('trim', explode(',', $keywords))));
        }

        return [];
    }

    private function normalizeYoutubeUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);
        $parsed = parse_url($url);
        if (! is_array($parsed)) {
            return $url;
        }

        $host = strtolower((string) ($parsed['host'] ?? ''));
        $path = trim((string) ($parsed['path'] ?? ''), '/');
        $videoId = null;

        if (Str::contains($host, 'youtu.be')) {
            $segments = explode('/', $path);
            $videoId = $segments[0] ?? null;
        } elseif (Str::contains($host, 'youtube.com')) {
            if ($path === 'watch') {
                parse_str((string) ($parsed['query'] ?? ''), $query);
                $videoId = $query['v'] ?? null;
            } elseif (Str::startsWith($path, 'embed/')) {
                $videoId = explode('/', $path)[1] ?? null;
            } elseif (Str::startsWith($path, 'shorts/')) {
                $videoId = explode('/', $path)[1] ?? null;
            }
        }

        if (! is_string($videoId) || $videoId === '') {
            return $url;
        }

        $videoId = preg_replace('/[^a-zA-Z0-9_-]/', '', $videoId);
        if (! is_string($videoId) || $videoId === '') {
            return $url;
        }

        return "https://www.youtube.com/embed/{$videoId}";
    }

    private function translatedName(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $locale = app()->getLocale();

            return (string) (Arr::get($value, $locale)
                ?? Arr::get($value, config('app.fallback_locale'))
                ?? Arr::first($value)
                ?? '');
        }

        return '';
    }

    /**
     * @param  array<string, string>  $existing
     * @return array<string, string>|null
     */
    private function buildTranslatedValue(mixed $value, array $existing = [], bool $autoTranslate = true): ?array
    {
        $locale = app()->getLocale();
        $translations = $existing;

        if (is_array($value)) {
            foreach ($value as $lang => $text) {
                if (! is_string($lang) || ! is_scalar($text)) {
                    continue;
                }

                $clean = trim((string) $text);
                if ($clean !== '') {
                    $translations[$lang] = $clean;
                }
            }
        } elseif ($value !== null) {
            $clean = trim((string) $value);
            if ($clean !== '') {
                $translations[$locale] = $clean;
            }
        }

        $sourceText = trim((string) ($translations[$locale] ?? Arr::first($translations) ?? ''));
        if ($sourceText === '') {
            return null;
        }

        $translations[$locale] = $sourceText;

        if ($autoTranslate) {
            foreach (otherLangs() as $lang) {
                if (! is_string($lang) || $lang === $locale) {
                    continue;
                }

                if (! empty($translations[$lang])) {
                    continue;
                }

                try {
                    $translations[$lang] = autoGoogleTranslator($lang, $sourceText);
                } catch (Throwable $exception) {
                    Log::warning('Property auto translation failed.', [
                        'field_locale' => $lang,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return $translations;
    }
}
