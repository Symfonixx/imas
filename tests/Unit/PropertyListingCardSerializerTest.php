<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\UnitType;
use Modules\Property\Support\PropertyListingCardSerializer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PropertyListingCardSerializerTest extends TestCase
{
    #[Test]
    public function it_includes_unit_types_and_location_hierarchy(): void
    {
        $city = new Location([
            'name' => ['en' => 'Antalya'],
            'type' => LocationType::City,
        ]);
        $city->id = 10;

        $district = new Location([
            'name' => ['en' => 'Konyaalti'],
            'type' => LocationType::Municipality,
            'parent_id' => 10,
        ]);
        $district->id = 11;
        $district->setRelation('parent', $city);

        $area = new Location([
            'name' => ['en' => 'Lara'],
            'type' => LocationType::Area,
            'parent_id' => 11,
        ]);
        $area->id = 12;
        $area->setRelation('parent', $district);

        $property = new Property([
            'project_code' => 'PRJ-001',
            'title' => ['en' => 'Sea View'],
            'project_name' => ['en' => 'Project'],
            'overview' => ['en' => 'Overview'],
            'price' => 250000,
            'min_area' => 80,
            'max_area' => 120,
            'thumbnail' => null,
            'is_featured' => false,
            'is_sold_out' => false,
            'is_citizenship_eligible' => false,
            'youtube_video_url' => null,
        ]);
        $property->id = 5;
        $property->exists = true;
        $property->setRelation('location', $area);
        $property->setRelation('unitTypes', new Collection([
            tap(new UnitType([
                'property_id' => 5,
                'catalog_id' => null,
                'name' => '2+1',
                'min_area' => 80,
                'max_area' => 95,
                'price' => 250000,
            ]), static function (UnitType $unitType): UnitType {
                $unitType->id = 1;

                return $unitType;
            }),
            tap(new UnitType([
                'property_id' => 5,
                'catalog_id' => null,
                'name' => '3+1',
                'min_area' => 100,
                'max_area' => 120,
                'price' => 180000,
            ]), static function (UnitType $unitType): UnitType {
                $unitType->id = 2;

                return $unitType;
            }),
        ]));

        $payload = PropertyListingCardSerializer::toArray($property);

        $this->assertArrayHasKey('unit_types', $payload);
        $this->assertCount(2, $payload['unit_types']);
        $this->assertSame(180000.0, $payload['start_price']);
        $this->assertSame('2+1', $payload['unit_types'][0]['name']);
        $this->assertSame(10, $payload['location']['city']['id']);
        $this->assertSame(11, $payload['location']['district']['id']);
        $this->assertSame(12, $payload['location']['area']['id']);
        $this->assertStringContainsString('/property/5', $payload['url']);
    }
}
