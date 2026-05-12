<?php

namespace Modules\Property\Http\Controllers;

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
use Modules\Property\Presentation\ListingPropertyAttributesPresenter;
use Modules\User\Enums\CmsStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

class PropertyController extends Controller
{
    /**
     * @var list<string>
     */
    private array $propertyCardWith = [
        'location:id,name',
        'propertyType:id,name,slug,attribute_family_id',
        'propertyType.attributeFamily',
        'propertyType.attributeFamily.attributes',
        'attributeValues',
        'attributeValues.attribute',
    ];

    public function __construct(
        private readonly ListingPropertyAttributesPresenter $listingPropertyAttributes,
    ) {}

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
            fn (Property $property) => $this->serializeListingProperty($property),
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
            ->map(fn (Property $property) => $this->serializeListingProperty($property))
            ->values()
            ->all();

        $featuredProperties = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->where('is_featured', true)
            ->with($this->propertyCardWith)
            ->latest('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (Property $property) => $this->serializeListingProperty($property))
            ->values()
            ->all();

        if ($request->is('api/*')) {
            return response()->json($properties);
        }

        $pageTitle = __('property::Listings');

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

    /**
     * @return array<string, mixed>
     */
    private function serializeListingProperty(Property $property): array
    {
        return [
            'id' => $property->id,
            'project_code' => $property->project_code,
            'title' => $property->title,
            'project_name' => $property->project_name,
            'overview' => $property->overview,
            'price' => $property->price,
            'min_area' => $property->min_area,
            'max_area' => $property->max_area,
            'thumbnail_url' => $property->thumbnail
                ? asset('storage/'.$property->thumbnail)
                : asset('images/blank.png'),
            'location' => $property->location
                ? ['id' => $property->location->id, 'name' => $property->location->name]
                : null,
            'property_type' => $property->propertyType
                ? [
                    'id' => $property->propertyType->id,
                    'name' => $property->propertyType->name,
                    'slug' => $property->propertyType->slug,
                ]
                : null,
            'url' => route('property.show', $property->id),
            'is_featured' => (bool) $property->is_featured,
            'is_sold_out' => (bool) $property->is_sold_out,
            'youtube_video_url' => $property->youtube_video_url,
            'updated_at' => $property->updated_at?->toIso8601String(),
            'attributes' => $this->listingPropertyAttributes->present($property),
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('property::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('property::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('property::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
