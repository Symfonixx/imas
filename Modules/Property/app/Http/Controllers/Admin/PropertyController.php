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
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Core\Support\AdminImageInput;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\ProjectUnitType;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyType;
use Modules\Property\Support\PropertyMetricsFromUnitTypes;
use Modules\User\Enums\CmsStatus;
use Throwable;

class PropertyController extends Controller
{
    public function __construct()
    {
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

        DB::beginTransaction();

        try {
            $property = Property::query()->create(array_merge(
                $this->buildPropertyPayload($request, $validated),
                $this->metricsFromUnitTypeRows($validated['unit_types'] ?? [])
            ));
            $this->syncSlides($property, $request);
            if (array_key_exists('unit_types', $validated)) {
                $this->syncUnitTypes($property, $validated['unit_types']);
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        return redirect()->route('admin.properties.index');
    }

    public function edit(Property $property)
    {
        $this->setActive('projects');
        $property->load(['slides', 'unitTypes', 'location.parent.parent']);

        return view('property::admin.property.edit', array_merge(
            $this->formShared(),
            [
                'property' => $property,
            ]
        ));
    }

    public function update(Request $request, Property $property): RedirectResponse
    {

        $validated = $this->filterEmptyUnitTypes($this->validatePayload($request, $property));

        DB::transaction(function () use ($request, $validated, $property): void {
            $payload = $this->buildPropertyPayload($request, $validated, $property);

            if ($request->filled('unit_types_sync_empty')) {
                $payload = array_merge($payload, $this->metricsFromUnitTypeRows([]));
            } elseif (array_key_exists('unit_types', $validated)) {
                $payload = array_merge($payload, $this->metricsFromUnitTypeRows($validated['unit_types']));
            }

            $property->update($payload);
            $this->syncSlides($property, $request);

            if ($request->filled('unit_types_sync_empty')) {
                $this->syncUnitTypes($property, []);
            } elseif (array_key_exists('unit_types', $validated)) {
                $this->syncUnitTypes($property, $validated['unit_types']);
            }
        });

        return redirect()->route('admin.properties.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $ids = array_map('intval', (array) $request->input('ids', []));

        Property::query()
            ->whereIn('id', $ids)
            ->with('slides')
            ->get()
            ->each(function (Property $property): void {
                if (! empty($property->thumbnail)) {
                    Storage::disk('public')->delete($property->thumbnail);
                }

                foreach ($property->slides as $slide) {
                    Storage::disk('public')->delete($slide->image);
                }
            });

        Property::query()->whereIn('id', $ids)->delete();

        return back();
    }

    public function locationChildren(Request $request): JsonResponse
    {
        $parentId = $request->query('parent_id');
        $type = (string) $request->query('type');

        $items = Location::query()
            ->where('parent_id', $parentId === null || $parentId === '' ? null : (int) $parentId)
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->orderBy('id')
            ->get(['id', 'name', 'type']);

        return response()->json([
            'items' => $items->map(fn (Location $location): array => [
                'id' => $location->id,
                'name' => $this->translatedName($location->name),
                'type' => $location->type instanceof LocationType ? $location->type->value : (string) $location->type,
            ])->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formShared(): array
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

        return [
            'property' => null,
            'propertyTypes' => $propertyTypes,
            'cities' => $cities,
            'projectUnitTypesCatalog' => $projectUnitTypesCatalog,
            'statuses' => CmsStatus::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?Property $property = null): array
    {
        $uniqueProjectCode = Rule::unique('properties', 'project_code');
        if ($property !== null) {
            $uniqueProjectCode->ignore($property->id);
        }

        $unitTypeIdRules = ['nullable', 'integer'];
        if ($property !== null) {
            $unitTypeIdRules[] = Rule::exists('unit_types', 'id')->where('property_id', $property->id);
        }

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'project_code' => ['required', 'string', 'max:128', $uniqueProjectCode],
            'overview' => ['required', 'string'],
            'thumbnail' => [$property === null ? 'required' : 'nullable', 'image', 'max:4096'],
            'thumbnail_media_path' => ['nullable', 'string'],
            'location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('type', LocationType::Area->value)],
            'property_type_id' => ['required', 'integer', 'exists:property_types,id'],
            'is_sold_out' => ['nullable', 'boolean'],
            'is_recommended' => ['nullable', 'boolean'],
            'is_citizenship_eligible' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'why_to_buy' => ['required', 'string'],
            'facilities' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'youtube_video_url' => ['nullable', 'url', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', Rule::in(array_map(static fn (CmsStatus $status) => $status->value, CmsStatus::cases()))],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable'],
            'meta_schema' => ['nullable', 'string'],
            'slides' => ['nullable', 'array', 'max:20'],
            'slides.*' => ['image', 'max:4096'],
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
        ]);
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

        $thumbnailPath = $property?->thumbnail;
        if (AdminImageInput::isRemoved($request, 'thumbnail')) {
            $thumbnailPath = null;
        } elseif ($request->hasFile('thumbnail')) {
            if ($thumbnailPath) {
                Storage::disk('public')->delete($thumbnailPath);
            }
            $thumbnailPath = $request->file('thumbnail')->store('properties/thumbnails', 'public');
        } elseif ($request->filled('thumbnail_media_path')) {
            $path = trim((string) $request->input('thumbnail_media_path'));
            $thumbnailPath = strcasecmp($path, 'null') === 0 ? null : $path;
        }

        return [
            'thumbnail' => $thumbnailPath,
            'project_code' => $validated['project_code'],
            'title' => $this->buildTranslatedValue(
                $validated['title'],
                $property?->getTranslations('title') ?? [],
                $autoTranslate
            ),
            'project_name' => $this->buildTranslatedValue(
                $validated['project_name'] ?? $validated['title'],
                $property?->getTranslations('project_name') ?? [],
                $autoTranslate
            ),
            'overview' => $this->buildTranslatedValue(
                $validated['overview'],
                $property?->getTranslations('overview') ?? [],
                $autoTranslate
            ),
            'location_id' => (int) $validated['location_id'],
            'property_type_id' => (int) $validated['property_type_id'],
            'is_sold_out' => $request->boolean('is_sold_out'),
            'is_recommended' => $request->boolean('is_recommended'),
            'is_citizenship_eligible' => $request->boolean('is_citizenship_eligible'),
            'is_featured' => $request->boolean('is_featured'),
            'why_to_buy' => $this->buildTranslatedValue(
                $validated['why_to_buy'],
                $property?->getTranslations('why_to_buy') ?? [],
                $autoTranslate
            ),
            'facilities' => $this->buildTranslatedValue(
                $validated['facilities'] ?? null,
                $property?->getTranslations('facilities') ?? [],
                $autoTranslate
            ),
            'content' => $this->buildTranslatedValue(
                $validated['content'],
                $property?->getTranslations('content') ?? [],
                $autoTranslate
            ),
            'youtube_video_url' => $this->normalizeYoutubeUrl($validated['youtube_video_url'] ?? null),
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'status' => $validated['status'],
            'metadata' => [
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'meta_keywords' => $this->normalizeKeywords($validated['meta_keywords'] ?? null),
                'schema' => $validated['meta_schema'] ?? null,
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

    private function syncSlides(Property $property, Request $request): void
    {
        if (! $request->hasFile('slides')) {
            return;
        }

        foreach ($property->slides as $slide) {
            Storage::disk('public')->delete($slide->image);
        }

        $property->slides()->delete();

        $slides = $request->file('slides', []);
        foreach (array_slice($slides, 0, 20) as $index => $slideImage) {
            $path = $slideImage->store('properties/slides', 'public');
            $property->slides()->create([
                'image' => $path,
                'position' => $index,
            ]);
        }
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
