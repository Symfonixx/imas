<?php

namespace Modules\Property\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Base\lang;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyType;
use Modules\Property\Support\PropertyCardEagerLoads;
use Modules\Property\Support\PropertyListingCardSerializer;
use Modules\Property\Support\PropertySearchBounds;
use Modules\Property\Transformers\PropertyCardResource;
use Modules\User\Enums\CmsStatus;

class PropertyController extends Controller
{
    /**
     * JSON listing for GET /api/properties (optional auth).
     */
    public function apiIndex(Request $request): AnonymousResourceCollection
    {
        $validated = $this->listingQueryRules($request);
        $perPage = min(max((int) ($validated['per_page'] ?? 8), 1), 50);
        $userId = $request->user()?->id;

        $query = $this->buildFilteredPublishedListingQuery($validated, $userId);

        $propertiesPaginator = $query->paginate($perPage)->withQueryString();

        return PropertyCardResource::collection($propertiesPaginator);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $validated = $this->listingQueryRules($request);
        $sort = $validated['sort'] ?? 'price_asc';
        $keyword = isset($validated['q']) ? trim((string) $validated['q']) : '';
        $userId = $request->user()?->id;

        $query = $this->buildFilteredPublishedListingQuery($validated, $userId);

        if ($sort === 'price_desc') {
            $query->orderByDesc('price');
        } else {
            $query->orderBy('price');
        }

        $propertiesPaginator = $query->paginate(8)->withQueryString();

        $properties = $propertiesPaginator->through(
            fn (Property $property) => PropertyListingCardSerializer::toArray($property),
        );

        $filters = [
            'q' => $keyword !== '' ? $keyword : null,
            'location_id' => $validated['location_id'] ?? null,
            'property_type_id' => $validated['property_type_id'] ?? null,
            'min_price' => isset($validated['min_price']) ? (float) $validated['min_price'] : null,
            'max_price' => isset($validated['max_price']) ? (float) $validated['max_price'] : null,
            'min_area' => isset($validated['min_area']) ? (float) $validated['min_area'] : null,
            'max_area' => isset($validated['max_area']) ? (float) $validated['max_area'] : null,
            'project_unit_type_id' => $validated['project_unit_type_id'] ?? [],
        ];

        $propertyTypes = PropertyType::query()
            ->orderBy('slug')
            ->get(['id', 'name', 'slug'])
            ->map(static fn (PropertyType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
            ])
            ->values()
            ->all();

        $cities = Location::query()
            ->where('type', LocationType::City)
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(static fn (Location $city) => [
                'id' => $city->id,
                'name' => $city->name,
            ])
            ->values()
            ->all();

        $recentProperties = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->with(PropertyCardEagerLoads::relations())
            ->withFavoriteStateForUser($userId)
            ->latest('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (Property $property) => PropertyListingCardSerializer::toArray($property))
            ->values()
            ->all();

        $featuredProperties = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->where('is_featured', true)
            ->with(PropertyCardEagerLoads::relations())
            ->withFavoriteStateForUser($userId)
            ->latest('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (Property $property) => PropertyListingCardSerializer::toArray($property))
            ->values()
            ->all();

        $pageTitle = $this->listingPageTitle();

        return Inertia::render('Property::index', [
            'title' => $pageTitle,
            'properties' => $properties,
            'filters' => $filters,
            'sort' => $sort,
            'propertyTypes' => $propertyTypes,
            'cities' => $cities,
            'recentProperties' => $recentProperties,
            'featuredProperties' => $featuredProperties,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function listingQueryRules(Request $request): array
    {
        return Validator::make($request->query(), [
            'sort' => ['sometimes', 'string', 'in:price_asc,price_desc'],
            'location_id' => ['sometimes', 'nullable', 'integer', 'exists:locations,id'],
            'property_type_id' => ['sometimes', 'nullable', 'integer', 'exists:property_types,id'],
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'min_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'min_area' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'max_area' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'project_unit_type_id' => ['sometimes', 'nullable', 'array'],
            'project_unit_type_id.*' => ['integer', 'exists:project_unit_types,id'],
        ])->validated();
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return Builder<Property>
     */
    private function buildFilteredPublishedListingQuery(array $validated, ?int $userId): Builder
    {
        $keyword = isset($validated['q']) ? trim((string) $validated['q']) : '';

        $query = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->with(PropertyCardEagerLoads::relations())
            ->withFavoriteStateForUser($userId);

        if (! empty($validated['location_id'])) {
            $query->where('location_id', (int) $validated['location_id']);
        }

        if (! empty($validated['property_type_id'])) {
            $query->where('property_type_id', (int) $validated['property_type_id']);
        }

        if ($keyword !== '') {
            $this->applyTitleKeywordFilter($query, $keyword);
        }

        if (isset($validated['min_price']) || isset($validated['max_price'])) {
            $this->applyPriceRangeFilter(
                $query,
                (float) ($validated['min_price'] ?? 0),
                (float) ($validated['max_price'] ?? PropertySearchBounds::cachedRaw()['price']['max']),
            );
        }

        if (isset($validated['min_area']) || isset($validated['max_area'])) {
            $this->applyAreaRangeFilter(
                $query,
                (float) ($validated['min_area'] ?? 0),
                (float) ($validated['max_area'] ?? PropertySearchBounds::cachedRaw()['area']['max']),
            );
        }

        if (! empty($validated['project_unit_type_id'])) {
            $catalogIds = array_map('intval', (array) $validated['project_unit_type_id']);
            $query->whereHas('unitTypes', static fn (Builder $ut) => $ut->whereIn('catalog_id', $catalogIds));
        }

        return $query;
    }

    /**
     * @param  Builder<Property>  $query
     */
    private function applyPriceRangeFilter(Builder $query, float $min, float $max): void
    {
        if ($max < $min) {
            [$min, $max] = [$max, $min];
        }

        $query->where(function (Builder $outer) use ($min, $max) {
            $outer->whereBetween('price', [$min, $max])
                ->orWhereHas('unitTypes', static fn (Builder $ut) => $ut->whereBetween('price', [$min, $max]));
        });
    }

    /**
     * @param  Builder<Property>  $query
     */
    private function applyAreaRangeFilter(Builder $query, float $min, float $max): void
    {
        if ($max < $min) {
            [$min, $max] = [$max, $min];
        }

        $query->where(function (Builder $outer) use ($min, $max) {
            $outer->where(function (Builder $property) use ($min, $max) {
                $property->whereNotNull('min_area')
                    ->whereNotNull('max_area')
                    ->where('min_area', '<=', $max)
                    ->where('max_area', '>=', $min);
            })->orWhereHas('unitTypes', static function (Builder $ut) use ($min, $max) {
                $ut->where(function (Builder $row) use ($min, $max) {
                    $row->where(function (Builder $withMax) use ($min, $max) {
                        $withMax->whereNotNull('min_area')
                            ->whereNotNull('max_area')
                            ->where('min_area', '<=', $max)
                            ->where('max_area', '>=', $min);
                    })->orWhere(function (Builder $minOnly) use ($min, $max) {
                        $minOnly->whereNotNull('min_area')
                            ->whereNull('max_area')
                            ->whereBetween('min_area', [$min, $max]);
                    });
                });
            });
        });
    }

    /**
     * Page title from {@see lang} JSON (`properties.property_Listings`),
     * same strings as Inertia `translations` (e.g. tr: "Mülk listeleri").
     * English JSON may store a Laravel key (`property::Listings`) which is resolved here.
     */
    private function listingPageTitle(): string
    {
        $locale = app()->getLocale();
        $path = module_path('Base', "lang/{$locale}.json");

        if (! is_readable($path)) {
            return (string) __('property::Listings');
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            return (string) __('property::Listings');
        }

        $properties = $decoded['properties'] ?? null;
        if (! is_array($properties)) {
            return (string) __('property::Listings');
        }

        $label = $properties['property_Listings'] ?? null;
        if (! is_string($label) || $label === '') {
            return (string) __('property::Listings');
        }

        if (str_contains($label, '::')) {
            return (string) __($label);
        }

        return $label;
    }

    /**
     * @param  Builder<Property>  $query
     */
    private function applyTitleKeywordFilter(Builder $query, string $keyword): void
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', []));
        if ($locales === []) {
            return;
        }

        $pattern = '%'.addcslashes($keyword, '%_\\').'%';

        $query->where(function (Builder $inner) use ($locales, $pattern) {
            foreach ($locales as $locale) {
                $inner->orWhere("title->{$locale}", 'like', $pattern);
            }
        });
    }
}
