<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Property\Enums\LocationType;
use Modules\Property\Http\Controllers\Property\PropertyController;
use Modules\Property\Models\Location;
use Modules\Property\Models\ProjectUnitType;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyType;
use Modules\Property\Models\UnitType;
use Modules\User\Enums\CmsStatus;
use ReflectionMethod;
use Tests\TestCase;

class PropertyListingFilterQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_query_filters_by_price_area_and_unit_type_catalog(): void
    {
        $city = Location::query()->create([
            'name' => ['en' => 'Ankara'],
            'type' => LocationType::City,
        ]);
        $propertyType = PropertyType::query()->create([
            'name' => ['en' => 'Villa'],
            'slug' => 'villa',
            'icon' => 'fa-home',
        ]);

        $catalogA = ProjectUnitType::query()->create([
            'name' => ['en' => '1+1'],
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $catalogB = ProjectUnitType::query()->create([
            'name' => ['en' => '3+1'],
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $match = Property::factory()->create([
            'status' => CmsStatus::PUBLISHED,
            'location_id' => $city->id,
            'property_type_id' => $propertyType->id,
            'price' => 200000,
            'min_area' => 50,
            'max_area' => 80,
        ]);
        UnitType::query()->create([
            'property_id' => $match->id,
            'catalog_id' => $catalogA->id,
            'name' => '1+1',
            'min_area' => 50,
            'max_area' => 80,
            'price' => 200000,
        ]);

        $other = Property::factory()->create([
            'status' => CmsStatus::PUBLISHED,
            'location_id' => $city->id,
            'property_type_id' => $propertyType->id,
            'price' => 900000,
            'min_area' => 120,
            'max_area' => 200,
        ]);
        UnitType::query()->create([
            'property_id' => $other->id,
            'catalog_id' => $catalogB->id,
            'name' => '3+1',
            'min_area' => 120,
            'max_area' => 200,
            'price' => 900000,
        ]);

        $controller = app(PropertyController::class);
        $method = new ReflectionMethod($controller, 'buildFilteredPublishedListingQuery');
        $method->setAccessible(true);

        $query = $method->invoke($controller, [
            'min_price' => 150000,
            'max_price' => 300000,
            'min_area' => 40,
            'max_area' => 100,
            'project_unit_type_id' => [$catalogA->id],
        ], null);

        $ids = $query->pluck('id')->all();

        $this->assertSame([$match->id], $ids);
    }
}
