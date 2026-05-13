<?php

namespace Modules\Property\Support;

use Modules\Property\Models\Property;

final class ListingCardHighlightBuilder
{
    /**
     * @return list<array{code: string, name: string, type: string, display: string}>
     */
    public static function forProperty(Property $property): array
    {
        $rows = [];
        $area = self::builtInAreaDisplay($property);
        if ($area !== null && $area !== '') {
            $rows[] = [
                'code' => 'built_in_area',
                'name' => (string) __('Area'),
                'type' => 'numeric',
                'display' => $area,
            ];
        }

        return $rows;
    }

    private static function builtInAreaDisplay(Property $property): ?string
    {
        $min = $property->min_area;
        $max = $property->max_area;

        if ($min === null && $max === null) {
            return null;
        }

        $minS = self::formatAreaNumber($min);
        $maxS = self::formatAreaNumber($max);

        if ($minS !== null && $maxS !== null && $minS !== $maxS) {
            return "{$minS} – {$maxS} ".__('sq ft');
        }

        $single = $minS ?? $maxS;

        return $single !== null ? "{$single} ".__('sq ft') : null;
    }

    private static function formatAreaNumber(null|float|int|string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $n = (float) $value;
        $formatter = \NumberFormatter::create(app()->getLocale(), \NumberFormatter::DECIMAL);
        if ($formatter === false) {
            return (string) (int) round($n);
        }
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);

        return $formatter->format($n);
    }
}
