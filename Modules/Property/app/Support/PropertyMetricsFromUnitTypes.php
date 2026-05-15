<?php

namespace Modules\Property\Support;

use Modules\Property\Models\UnitType;

final class PropertyMetricsFromUnitTypes
{
    /**
     * Derive listing price and area range from unit type rows.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{price: float, min_area: ?float, max_area: ?float}
     */
    public static function fromRows(array $rows): array
    {
        $prices = [];
        $areas = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $price = self::numericValue($row['price'] ?? null);
            if ($price !== null) {
                $prices[] = $price;
            }

            foreach (['min_area', 'max_area'] as $field) {
                $area = self::numericValue($row[$field] ?? null);
                if ($area !== null) {
                    $areas[] = $area;
                }
            }
        }

        return [
            'price' => $prices === [] ? 0.0 : min($prices),
            'min_area' => $areas === [] ? null : min($areas),
            'max_area' => $areas === [] ? null : max($areas),
        ];
    }

    /**
     * @param  iterable<UnitType>  $unitTypes
     * @return array{price: float, min_area: ?float, max_area: ?float}
     */
    public static function fromUnitTypes(iterable $unitTypes): array
    {
        $rows = [];

        foreach ($unitTypes as $unitType) {
            $rows[] = [
                'price' => $unitType->price,
                'min_area' => $unitType->min_area,
                'max_area' => $unitType->max_area,
            ];
        }

        return self::fromRows($rows);
    }

    private static function numericValue(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
