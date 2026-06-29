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
use Modules\Property\Support\PropertyCardEagerLoads;
use Modules\Property\Support\PropertyListingCardSerializer;
use Modules\User\Enums\CmsStatus;

class HomeController extends Controller
{
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

        $districts = Location::query()
            ->where('type', LocationType::District)
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(static fn (Location $district) => [
                'id' => $district->id,
                'name' => $district->name,
            ])
            ->values()
            ->all();

        $areas = Location::query()
            ->where('type', LocationType::Area)
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(static fn (Location $area) => [
                'id' => $area->id,
                'name' => $area->name,
            ])
            ->values()
            ->all();

        $userId = auth()->id();

        $featuredProperties = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->where('is_featured', true)
            ->with(PropertyCardEagerLoads::relations())
            ->withFavoriteStateForUser($userId)
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->map(fn (Property $property) => PropertyListingCardSerializer::toArray($property))
            ->values()
            ->all();

        $recommendedProperties = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->where('is_recommended', true)
            ->with(PropertyCardEagerLoads::relations())
            ->withFavoriteStateForUser($userId)
            ->latest('updated_at')
            ->limit(20)
            ->get()
            ->map(fn (Property $property) => PropertyListingCardSerializer::toArray($property))
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
            ->with(['category:id,name,slug'])
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
                    'category' => $blog->category
                        ? [
                            'id' => $blog->category->id,
                            'name' => $blog->category->name,
                            'slug' => $blog->category->slug,
                        ]
                        : null,
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Base::Index', [
            'welcomeTitle' => 'Find Your Dream Home',
            'welcomeSubtitle' => 'Browse curated listings and discover properties that match your goals.',
            'slides' => $slides,
            'propertyTypes' => $propertyTypes,
            'districts' => $districts,
            'areas' => $areas,
            'featuredProperties' => $featuredProperties,
            'recommendedProperties' => $recommendedProperties,
            'corporateServices' => $corporateServices,
            'testimonials' => $testimonials,
            'articles' => $articles,
        ]);
    }
}
