<?php

namespace Modules\Property\Support;

use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;

final class PropertyLocationHierarchySerializer
{
    /**
     * Resolve city, district, and area from the property's area location.
     *
     * @return array{city: ?array{id: int, name: mixed, type: string}, district: ?array{id: int, name: mixed, type: string}, area: ?array{id: int, name: mixed, type: string}}|null
     */
    public static function toArray(?Location $areaLocation): ?array
    {
        if ($areaLocation === null) {
            return null;
        }

        $area = self::node($areaLocation);
        $district = null;
        $city = null;

        $parent = self::relatedParent($areaLocation);

        if ($parent !== null) {
            if (self::typeValue($parent) === LocationType::District->value) {
                $district = self::node($parent);
                $cityParent = self::relatedParent($parent);
                if ($cityParent !== null && self::typeValue($cityParent) === LocationType::City->value) {
                    $city = self::node($cityParent);
                }
            } elseif (self::typeValue($parent) === LocationType::City->value) {
                $city = self::node($parent);
            }
        }

        return [
            'city' => $city,
            'district' => $district,
            'area' => $area,
        ];
    }

    private static function relatedParent(Location $location): ?Location
    {
        if (! $location->relationLoaded('parent')) {
            return null;
        }

        $parent = $location->getRelation('parent');

        return $parent instanceof Location ? $parent : null;
    }

    private static function typeValue(Location $location): string
    {
        return $location->type instanceof LocationType
            ? $location->type->value
            : (string) $location->type;
    }

    /**
     * @return array{id: int, name: mixed, type: string}
     */
    private static function node(Location $location): array
    {
        return [
            'id' => $location->id,
            'name' => $location->name,
            'type' => $location->type instanceof LocationType
                ? $location->type->value
                : (string) $location->type,
        ];
    }
}
