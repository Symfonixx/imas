<?php

namespace Modules\Property\Support;

use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;

final class PropertyLocationHierarchySerializer
{
    /**
     * Resolve city, district, and area from the property location (area or municipality).
     *
     * @return array{city: ?array{id: int, name: mixed, type: string}, district: ?array{id: int, name: mixed, type: string}, area: ?array{id: int, name: mixed, type: string}}|null
     */
    public static function toArray(?Location $storedLocation): ?array
    {
        if ($storedLocation === null) {
            return null;
        }

        $type = self::typeValue($storedLocation);

        if ($type === LocationType::Area->value) {
            return self::fromArea($storedLocation);
        }

        if ($type === LocationType::Municipality->value) {
            return self::fromMunicipality($storedLocation);
        }

        return [
            'city' => self::node($storedLocation),
            'district' => null,
            'area' => null,
        ];
    }

    /**
     * @return array{city: ?array{id: int, name: mixed, type: string}, district: ?array{id: int, name: mixed, type: string}, area: array{id: int, name: mixed, type: string}}
     */
    private static function fromArea(Location $areaLocation): array
    {
        $area = self::node($areaLocation);
        $district = null;
        $city = null;

        $parent = self::relatedParent($areaLocation);

        if ($parent !== null) {
            if (self::typeValue($parent) === LocationType::Municipality->value) {
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

    /**
     * @return array{city: ?array{id: int, name: mixed, type: string}, district: array{id: int, name: mixed, type: string}, area: null}
     */
    private static function fromMunicipality(Location $municipalityLocation): array
    {
        $district = self::node($municipalityLocation);
        $city = null;
        $parent = self::relatedParent($municipalityLocation);

        if ($parent !== null && self::typeValue($parent) === LocationType::City->value) {
            $city = self::node($parent);
        }

        return [
            'city' => $city,
            'district' => $district,
            'area' => null,
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
