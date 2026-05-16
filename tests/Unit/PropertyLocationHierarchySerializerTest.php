<?php

namespace Tests\Unit;

use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Support\PropertyLocationHierarchySerializer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PropertyLocationHierarchySerializerTest extends TestCase
{
    #[Test]
    public function it_resolves_city_district_and_area_from_area_location(): void
    {
        $city = new Location([
            'name' => ['en' => 'Istanbul'],
            'type' => LocationType::City,
            'parent_id' => null,
        ]);
        $city->id = 1;

        $district = new Location([
            'name' => ['en' => 'Besiktas'],
            'type' => LocationType::District,
            'parent_id' => 1,
        ]);
        $district->id = 2;
        $district->setRelation('parent', $city);

        $area = new Location([
            'name' => ['en' => 'Levent'],
            'type' => LocationType::Area,
            'parent_id' => 2,
        ]);
        $area->id = 3;
        $area->setRelation('parent', $district);

        $result = PropertyLocationHierarchySerializer::toArray($area);

        $this->assertNotNull($result);
        $this->assertSame(1, $result['city']['id']);
        $this->assertSame(2, $result['district']['id']);
        $this->assertSame(3, $result['area']['id']);
        $this->assertSame('city', $result['city']['type']);
        $this->assertSame('district', $result['district']['type']);
        $this->assertSame('area', $result['area']['type']);
    }

    #[Test]
    public function it_returns_null_when_area_location_is_missing(): void
    {
        $this->assertNull(PropertyLocationHierarchySerializer::toArray(null));
    }
}
