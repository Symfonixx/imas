<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\ProjectUnitType;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyType;
use Modules\Property\Models\UnitType;
use Modules\Property\Support\PropertySearchBounds;
use Modules\User\Enums\CmsStatus;
use Tests\TestCase;

class PropertySearchBoundsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget(PropertySearchBounds::CACHE_KEY);
    }

    public function test_bounds_reflect_published_unit_types_and_properties(): void
    {
        $city = Location::query()->create([
            'name' => ['en' => 'Istanbul'],
            'type' => LocationType::City,
        ]);
        $propertyType = PropertyType::query()->create([
            'name' => ['en' => 'Apartment'],
            'slug' => 'apartment',
            'icon' => 'fa-home',
        ]);

        $catalog = ProjectUnitType::query()->create([
            'name' => ['en' => '2+1'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $property = Property::factory()->create([
            'status' => CmsStatus::PUBLISHED,
            'location_id' => $city->id,
            'property_type_id' => $propertyType->id,
            'price' => 250000,
            'min_area' => 80,
            'max_area' => 120,
        ]);

        UnitType::query()->create([
            'property_id' => $property->id,
            'catalog_id' => $catalog->id,
            'name' => '2+1',
            'min_area' => 90,
            'max_area' => 110,
            'price' => 500000,
        ]);

        $bounds = PropertySearchBounds::forLocale('en');

        $this->assertSame(500000, $bounds['price']['min']);
        $this->assertSame(500000, $bounds['price']['max']);
        $this->assertSame(90, $bounds['area']['min']);
        $this->assertSame(110, $bounds['area']['max']);
        $this->assertNotEmpty($bounds['project_unit_types']);
        $this->assertSame('2+1', $bounds['project_unit_types'][0]['name']);
    }
}
