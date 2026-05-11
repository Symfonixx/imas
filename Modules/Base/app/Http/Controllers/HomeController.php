<?php

namespace Modules\Base\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Cms\Models\Blog;
use Modules\Cms\Models\Slide;
use Modules\Corporate\Models\CorporateService;
use Modules\Corporate\Models\Testimonial;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyType;
use Modules\Property\Presentation\ListingPropertyAttributesPresenter;
use Modules\User\Enums\CmsStatus;

class HomeController extends Controller
{
    public function __construct(
        private readonly ListingPropertyAttributesPresenter $listingPropertyAttributes,
    ) {}

    public function index(): Response
    {
        $slides = Slide::query()
            ->published()
            ->orderBy('rank')
            ->orderBy('id')
            ->get()
            ->map(static fn (Slide $slide) => [
                'id' => $slide->id,
                'title' => $slide->main_title ?? '',
                'description' => $slide->subtitle ?? '',
                'image' => $slide->image_link,
                'link' => $slide->link,
            ])
            ->values()
            ->all();

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

        $propertyCardWith = [
            'location:id,name',
            'propertyType:id,name,slug,attribute_family_id',
            'propertyType.attributeFamily',
            'propertyType.attributeFamily.attributes',
            'attributeValues',
            'attributeValues.attribute',
        ];

        $featuredProperties = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->where('is_featured', true)
            ->with($propertyCardWith)
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->map(fn (Property $property) => $this->serializeHomeProperty($property))
            ->values()
            ->all();

        $recommendedProperties = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->where('is_recommended', true)
            ->with($propertyCardWith)
            ->latest('updated_at')
            ->limit(20)
            ->get()
            ->map(fn (Property $property) => $this->serializeHomeProperty($property))
            ->values()
            ->all();

        $corporateServices = CorporateService::query()
            ->featured()
            ->latest('updated_at')
            ->limit(3)
            ->get(['id', 'title', 'slug', 'description', 'image'])
            ->map(static function (CorporateService $service) {
                $description = (string) ($service->description ?? '');

                return [
                    'id' => $service->id,
                    'title' => (string) ($service->title ?? ''),
                    'slug' => $service->slug,
                    'description' => Str::limit(strip_tags($description), 280),
                    'image' => $service->image_link,
                ];
            })
            ->values()
            ->all();

        $testimonials = Testimonial::query()
            ->published()
            ->orderBy('rank')
            ->orderBy('id')
            ->get()
            ->map(static function (Testimonial $testimonial) {
                return [
                    'id' => $testimonial->id,
                    'name' => (string) ($testimonial->name ?? ''),
                    'client' => (string) ($testimonial->client ?? ''),
                    'avatar' => $testimonial->avatar_link,
                    'position' => (string) ($testimonial->position ?? ''),
                    'quote' => (string) ($testimonial->quote ?? ''),
                    'link' => $testimonial->link,
                ];
            })
            ->values()
            ->all();

        $articles = Blog::query()
            ->featured()
            ->latest('created_at')
            ->limit(3)
            ->get()
            ->map(static function (Blog $blog) {
                $description = (string) ($blog->description ?? '');

                return [
                    'id' => $blog->id,
                    'title' => (string) ($blog->title ?? ''),
                    'excerpt' => Str::limit(strip_tags($description), 150),
                    'image' => $blog->image_link,
                    'slug' => $blog->slug,
                    'url' => LaravelLocalization::localizeUrl('/blog/'.$blog->slug),
                    'visits' => (int) $blog->visits,
                    'date' => $blog->created_at?->locale(app()->getLocale())->translatedFormat('d M Y') ?? '',
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Base::Index', [
            'welcomeTitle' => 'Find Your Dream Home',
            'welcomeSubtitle' => 'Browse curated listings and discover properties that match your goals.',
            'slides' => $slides,
            'propertyTypes' => $propertyTypes,
            'cities' => $cities,
            'featuredProperties' => $featuredProperties,
            'recommendedProperties' => $recommendedProperties,
            'corporateServices' => $corporateServices,
            'testimonials' => $testimonials,
            'articles' => $articles,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeHomeProperty(Property $property): array
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
}
