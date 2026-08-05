<?php

namespace Tests\Feature\Property;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Property\Application\SlideCategory\PropertySlideMediaSyncService;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertySlideMedia;
use Modules\Property\Models\PropertyType;
use Modules\Property\Models\SlideCategory;
use Modules\Property\Support\PropertyDetailSerializer;
use Modules\User\Enums\CmsStatus;
use Tests\TestCase;

class SlideCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_contains_configuration_only_and_can_be_linked_to_properties(): void
    {
        $category = $this->createCategory('waterfront-gallery');
        $property = $this->createProperty('PRJ-001');

        $property->slideCategories()->attach($category);

        $this->assertArrayNotHasKey('images', $category->getAttributes());
        $this->assertArrayNotHasKey('videos', $category->getAttributes());
        $this->assertTrue($property->fresh()->slideCategories->contains($category));
        $this->assertTrue($category->fresh()->properties->contains($property));
    }

    public function test_detail_serializer_uses_media_owned_by_the_property(): void
    {
        $property = $this->createProperty('PRJ-002');
        $category = $this->createCategory('primary-gallery');
        $property->slideCategories()->attach($category);
        $property->slideMedia()->createMany([
            [
                'slide_category_id' => $category->id,
                'type' => PropertySlideMedia::TYPE_IMAGE,
                'path' => 'properties/slides/property/images/primary.jpg',
                'position' => 0,
            ],
            [
                'slide_category_id' => $category->id,
                'type' => PropertySlideMedia::TYPE_VIDEO,
                'path' => 'properties/slides/property/videos/primary.mp4',
                'position' => 1,
            ],
            [
                'slide_category_id' => $category->id,
                'type' => PropertySlideMedia::TYPE_VIDEO,
                'path' => 'properties/slides/property/videos/secondary.mp4',
                'position' => 2,
            ],
        ]);
        $property->load(['slideMedia.slideCategory', 'unitTypes', 'location', 'propertyType']);

        $payload = PropertyDetailSerializer::toArray($property);

        $this->assertCount(1, $payload['slides']);
        $this->assertStringEndsWith('/storage/properties/slides/property/images/primary.jpg', $payload['slides'][0]['image_url']);
        $this->assertCount(2, $payload['videos']);
        $this->assertStringEndsWith('/storage/properties/slides/property/videos/primary.mp4', $payload['videos'][0]);
        $this->assertNull($payload['youtube_video_url']);

        $this->assertCount(1, $payload['slide_categories']);
        $this->assertSame($category->id, $payload['slide_categories'][0]['id']);
        $this->assertSame('Primary Gallery', $payload['slide_categories'][0]['name']);
        $this->assertSame('primary-gallery', $payload['slide_categories'][0]['slug']);
        $this->assertSame(3, $payload['slide_categories'][0]['assets_count']);
        $this->assertCount(3, $payload['slide_categories'][0]['assets']);
        $this->assertSame(PropertySlideMedia::TYPE_IMAGE, $payload['slide_categories'][0]['assets'][0]['type']);
        $this->assertSame(PropertySlideMedia::TYPE_VIDEO, $payload['slide_categories'][0]['assets'][1]['type']);
        $this->assertStringEndsWith(
            '/storage/properties/slides/property/images/primary.jpg',
            $payload['slide_categories'][0]['assets'][0]['url']
        );
        $this->assertStringEndsWith(
            '/storage/properties/slides/property/videos/primary.mp4',
            $payload['slide_categories'][0]['assets'][1]['url']
        );
    }

    public function test_detail_serializer_groups_slide_categories_with_asset_counts(): void
    {
        $property = $this->createProperty('PRJ-002B');
        $exterior = $this->createCategory('exterior');
        $interior = SlideCategory::query()->create([
            'name' => ['en' => 'Interior'],
            'description' => 'Category configuration only.',
            'slug' => 'interior',
            'status' => CmsStatus::PUBLISHED,
            'position' => 2,
        ]);
        $property->slideCategories()->attach([$exterior->id, $interior->id]);
        $property->slideMedia()->createMany([
            [
                'slide_category_id' => $exterior->id,
                'type' => PropertySlideMedia::TYPE_IMAGE,
                'path' => 'properties/slides/exterior-a.jpg',
                'position' => 0,
            ],
            [
                'slide_category_id' => $exterior->id,
                'type' => PropertySlideMedia::TYPE_VIDEO,
                'path' => 'properties/slides/exterior-v.mp4',
                'position' => 1,
            ],
            [
                'slide_category_id' => $interior->id,
                'type' => PropertySlideMedia::TYPE_IMAGE,
                'path' => 'properties/slides/interior-a.jpg',
                'position' => 0,
            ],
        ]);
        $property->load(['slideMedia.slideCategory', 'unitTypes', 'location', 'propertyType']);

        $payload = PropertyDetailSerializer::toArray($property);

        $this->assertCount(2, $payload['slide_categories']);
        $this->assertSame('Exterior', $payload['slide_categories'][0]['name']);
        $this->assertSame(2, $payload['slide_categories'][0]['assets_count']);
        $this->assertSame('Interior', $payload['slide_categories'][1]['name']);
        $this->assertSame(1, $payload['slide_categories'][1]['assets_count']);
    }

    public function test_media_is_isolated_between_properties_using_the_same_category(): void
    {
        $category = $this->createCategory('shared-category');
        $firstProperty = $this->createProperty('PRJ-003');
        $secondProperty = $this->createProperty('PRJ-004');
        $firstProperty->slideCategories()->attach($category);
        $secondProperty->slideCategories()->attach($category);

        $firstProperty->slideMedia()->create([
            'slide_category_id' => $category->id,
            'type' => PropertySlideMedia::TYPE_IMAGE,
            'path' => 'properties/slides/first.jpg',
            'position' => 0,
        ]);
        $secondProperty->slideMedia()->create([
            'slide_category_id' => $category->id,
            'type' => PropertySlideMedia::TYPE_IMAGE,
            'path' => 'properties/slides/second.jpg',
            'position' => 0,
        ]);

        $this->assertSame(
            ['properties/slides/first.jpg'],
            $firstProperty->fresh()->slideMedia->pluck('path')->all()
        );
        $this->assertSame(
            ['properties/slides/second.jpg'],
            $secondProperty->fresh()->slideMedia->pluck('path')->all()
        );
    }

    public function test_sync_service_stores_and_removes_multiple_property_media(): void
    {
        Storage::fake('public');
        $property = $this->createProperty('PRJ-005');
        $category = $this->createCategory('uploaded-gallery');
        $property->slideCategories()->attach($category);
        $service = app(PropertySlideMediaSyncService::class);

        Storage::disk('public')->put('media-library/gallery-a.jpg', 'image-a');
        Storage::disk('public')->put('media-library/gallery-b.jpg', 'image-b');

        $request = Request::create('/', 'POST', [
            'slide_media' => [
                $category->id => [
                    'images_media_paths' => [
                        'media-library/gallery-a.jpg',
                        'media-library/gallery-b.jpg',
                    ],
                ],
            ],
        ], [], [
            'slide_media' => [
                $category->id => [
                    'videos' => [
                        UploadedFile::fake()->create('tour-a.mp4', 100, 'video/mp4'),
                        UploadedFile::fake()->create('tour-b.mp4', 100, 'video/mp4'),
                    ],
                ],
            ],
        ]);

        $changes = $service->synchronize($request, $property, [$category->id]);
        $changes->finalize();

        $media = $property->fresh()->slideMedia;
        $this->assertCount(4, $media);
        $this->assertCount(2, $media->where('type', PropertySlideMedia::TYPE_IMAGE));
        $this->assertCount(2, $media->where('type', PropertySlideMedia::TYPE_VIDEO));
        Storage::disk('public')->assertExists($media->pluck('path')->all());

        $imagePaths = $media->where('type', PropertySlideMedia::TYPE_IMAGE)->pluck('path')->all();
        $videoPaths = $media->where('type', PropertySlideMedia::TYPE_VIDEO)->pluck('path')->all();

        $removeRequest = Request::create('/', 'POST', [
            'remove_slide_media_ids' => $media->pluck('id')->all(),
        ]);
        $changes = $service->synchronize($removeRequest, $property, [$category->id]);
        $changes->finalize();

        Storage::disk('public')->assertExists($imagePaths);
        Storage::disk('public')->assertMissing($videoPaths);
        $this->assertSame(0, $property->fresh()->slideMedia()->count());
    }

    public function test_migrated_media_path_is_deleted_only_after_its_last_property_reference(): void
    {
        Storage::fake('public');
        $path = 'property/slide-categories/images/shared.jpg';
        Storage::disk('public')->put($path, 'image');
        $category = $this->createCategory('migrated-gallery');
        $firstProperty = $this->createProperty('PRJ-006');
        $secondProperty = $this->createProperty('PRJ-007');
        $firstProperty->slideCategories()->attach($category);
        $secondProperty->slideCategories()->attach($category);

        foreach ([$firstProperty, $secondProperty] as $property) {
            $property->slideMedia()->create([
                'slide_category_id' => $category->id,
                'type' => PropertySlideMedia::TYPE_IMAGE,
                'path' => $path,
                'position' => 0,
            ]);
        }

        $service = app(PropertySlideMediaSyncService::class);
        $changes = $service->synchronize(Request::create('/', 'POST'), $firstProperty, []);
        $changes->finalize();
        Storage::disk('public')->assertExists($path);

        $changes = $service->synchronize(Request::create('/', 'POST'), $secondProperty, []);
        $changes->finalize();
        Storage::disk('public')->assertMissing($path);
    }

    private function createCategory(string $slug): SlideCategory
    {
        return SlideCategory::query()->create([
            'name' => ['en' => str($slug)->replace('-', ' ')->title()->toString()],
            'description' => 'Category configuration only.',
            'slug' => $slug,
            'status' => CmsStatus::PUBLISHED,
            'position' => 1,
        ]);
    }

    private function createProperty(string $code): Property
    {
        $location = Location::query()->create([
            'name' => ['en' => 'Test area'],
            'type' => LocationType::Area,
        ]);
        $propertyType = PropertyType::query()->create([
            'slug' => 'test-type-'.strtolower($code),
            'name' => ['en' => 'Test type'],
            'icon' => 'bi bi-building',
        ]);

        return Property::query()->create([
            'project_code' => $code,
            'url_key' => strtolower($code),
            'title' => ['en' => 'Test property'],
            'project_name' => ['en' => 'Test project'],
            'overview' => ['en' => 'Overview'],
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'why_to_buy' => ['en' => 'Why'],
            'content' => ['en' => 'Content'],
            'status' => CmsStatus::PUBLISHED,
        ]);
    }
}
