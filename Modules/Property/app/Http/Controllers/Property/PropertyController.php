<?php

namespace Modules\Property\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyType;
use Modules\Property\Support\PropertyListingCardSerializer;
use Modules\User\Enums\CmsStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

class PropertyController extends Controller
{
    /**
     * @var list<string>
     */
    private array $propertyCardWith = [
        'location:id,name',
        'propertyType:id,name,slug',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $validated = Validator::make($request->query(), [
            'sort' => ['sometimes', 'string', 'in:price_asc,price_desc'],
            'location_id' => ['sometimes', 'nullable', 'integer', 'exists:locations,id'],
            'property_type_id' => ['sometimes', 'nullable', 'integer', 'exists:property_types,id'],
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
        ])->validated();

        $sort = $validated['sort'] ?? 'price_asc';
        $keyword = isset($validated['q']) ? trim((string) $validated['q']) : '';

        $query = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->with($this->propertyCardWith);

        if (! empty($validated['location_id'])) {
            $query->where('location_id', (int) $validated['location_id']);
        }

        if (! empty($validated['property_type_id'])) {
            $query->where('property_type_id', (int) $validated['property_type_id']);
        }

        if ($keyword !== '') {
            $this->applyTitleKeywordFilter($query, $keyword);
        }

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
            ->with($this->propertyCardWith)
            ->latest('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (Property $property) => PropertyListingCardSerializer::toArray($property))
            ->values()
            ->all();

        $featuredProperties = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->where('is_featured', true)
            ->with($this->propertyCardWith)
            ->latest('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (Property $property) => PropertyListingCardSerializer::toArray($property))
            ->values()
            ->all();

        if ($request->is('api/*')) {
            return response()->json($properties);
        }

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
     * Page title from {@see \Modules\Base\lang} JSON (`properties.property_Listings`),
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