<?php

namespace Modules\Property\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Property\Models\ProjectUnitType;
use Modules\Property\Models\Property;
use Modules\Property\Models\UnitType;
use Modules\User\Enums\CmsStatus;
use Throwable;

final class PropertySearchBounds
{
    public const CACHE_KEY = 'inertia.shared.property_search.bounds.v2';

    private const DEFAULT_PRICE_MAX = 1_000_000;

    private const DEFAULT_AREA_MAX = 10_000;

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('inertia.shared.property_search.bounds');
    }

    /**
     * Localized bounds + catalog unit types for front-office search UI.
     *
     * @return array{
     *     price: array{min: int, max: int, currency: string},
     *     area: array{min: int, max: int, unit: string},
     *     project_unit_types: list<array{id: int, name: string}>
     * }
     */
    public static function forLocale(string $locale): array
    {
        try {
            $raw = self::cachedRaw();

            return [
                'price' => $raw['price'],
                'area' => $raw['area'],
                'project_unit_types' => array_map(
                    static function (array $row) use ($locale): array {
                        $names = $row['name'];
                        $name = is_array($names)
                            ? (string) ($names[$locale] ?? reset($names) ?: '')
                            : (string) $names;

                        return [
                            'id' => $row['id'],
                            'name' => $name,
                        ];
                    },
                    $raw['project_unit_types'],
                ),
            ];
        } catch (Throwable $e) {
            Log::warning('PropertySearchBounds::forLocale failed, using defaults.', [
                'message' => $e->getMessage(),
            ]);

            return self::emptyBounds();
        }
    }

    /**
     * @return array{
     *     price: array{min: int, max: int, currency: string},
     *     area: array{min: int, max: int, unit: string},
     *     project_unit_types: list<array{id: int, name: string}>
     * }
     */
    public static function emptyBounds(): array
    {
        return [
            'price' => [
                'min' => 0,
                'max' => self::DEFAULT_PRICE_MAX,
                'currency' => '$',
            ],
            'area' => [
                'min' => 0,
                'max' => self::DEFAULT_AREA_MAX,
                'unit' => 'm²',
            ],
            'project_unit_types' => [],
        ];
    }

    /**
     * @return array{
     *     price: array{min: int, max: int, currency: string},
     *     area: array{min: int, max: int, unit: string},
     *     project_unit_types: list<array{id: int, name: array<string, string>, sort_order: int}>
     * }
     */
    public static function cachedRaw(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, static fn (): array => self::computeRaw());
    }

    /**
     * @return array{
     *     price: array{min: int, max: int, currency: string},
     *     area: array{min: int, max: int, unit: string},
     *     project_unit_types: list<array{id: int, name: array<string, string>, sort_order: int}>
     * }
     */
    private static function computeRaw(): array
    {
        if (! Schema::hasTable('properties')) {
            return self::emptyBoundsForCache();
        }

        $unitTypeRows = Schema::hasTable('unit_types')
            ? UnitType::query()
                ->whereHas('property', static fn ($q) => $q->where('status', CmsStatus::PUBLISHED))
                ->get(['price', 'min_area', 'max_area'])
            : collect();

        $metrics = PropertyMetricsFromUnitTypes::fromRows(
            $unitTypeRows
                ->map(static fn (UnitType $row): array => [
                    'price' => $row->price,
                    'min_area' => $row->min_area,
                    'max_area' => $row->max_area,
                ])
                ->all(),
        );

        $priceMin = $metrics['price'] > 0 ? $metrics['price'] : null;
        $priceMax = self::maxUnitTypePrice($unitTypeRows);
        $areaMin = $metrics['min_area'];
        $areaMax = $metrics['max_area'];

        if ($priceMax === null) {
            $priceMax = (float) (Property::query()
                ->where('status', CmsStatus::PUBLISHED)
                ->whereNotNull('price')
                ->max('price') ?? 0);
        }

        if ($priceMin === null) {
            $priceMin = (float) (Property::query()
                ->where('status', CmsStatus::PUBLISHED)
                ->whereNotNull('price')
                ->min('price') ?? 0);
        }

        if ($areaMax === null) {
            $areaMax = max(
                (float) (Property::query()
                    ->where('status', CmsStatus::PUBLISHED)
                    ->max('max_area') ?? 0),
                (float) (Property::query()
                    ->where('status', CmsStatus::PUBLISHED)
                    ->max('min_area') ?? 0),
            );
        }

        if ($areaMin === null) {
            $areaMin = (float) (Property::query()
                ->where('status', CmsStatus::PUBLISHED)
                ->whereNotNull('min_area')
                ->min('min_area') ?? 0);
        }

        $projectUnitTypes = Schema::hasTable('project_unit_types')
            ? ProjectUnitType::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'name', 'sort_order'])
                ->map(static fn (ProjectUnitType $type): array => [
                    'id' => $type->id,
                    'name' => $type->getTranslations('name'),
                    'sort_order' => (int) $type->sort_order,
                ])
                ->values()
                ->all()
            : [];

        return [
            'price' => self::priceBounds($priceMin, $priceMax),
            'area' => self::areaBounds($areaMin, $areaMax),
            'project_unit_types' => $projectUnitTypes,
        ];
    }

    /**
     * @param  Collection<int, UnitType>  $unitTypeRows
     */
    private static function maxUnitTypePrice(Collection $unitTypeRows): ?float
    {
        $prices = $unitTypeRows
            ->pluck('price')
            ->filter(static fn ($price) => $price !== null && $price !== '')
            ->map(static fn ($price) => (float) $price);

        if ($prices->isEmpty()) {
            return null;
        }

        return $prices->max();
    }

    /**
     * @return array{min: int, max: int, currency: string}
     */
    private static function priceBounds(?float $min, ?float $max): array
    {
        $min = max(0, (int) floor($min ?? 0));
        $max = max($min, (int) ceil($max ?? 0));

        if ($max === 0) {
            return self::emptyBounds()['price'];
        }

        return [
            'min' => $min,
            'max' => $max,
            'currency' => '$',
        ];
    }

    /**
     * @return array{min: int, max: int, unit: string}
     */
    private static function areaBounds(?float $min, ?float $max): array
    {
        $min = max(0, (int) floor($min ?? 0));
        $max = max($min, (int) ceil($max ?? 0));

        if ($max === 0) {
            return self::emptyBounds()['area'];
        }

        return [
            'min' => $min,
            'max' => $max,
            'unit' => 'm²',
        ];
    }

    /**
     * @return array{
     *     price: array{min: int, max: int, currency: string},
     *     area: array{min: int, max: int, unit: string},
     *     project_unit_types: list<array{id: int, name: array<string, string>, sort_order: int}>
     * }
     */
    private static function emptyBoundsForCache(): array
    {
        $empty = self::emptyBounds();

        return [
            'price' => $empty['price'],
            'area' => $empty['area'],
            'project_unit_types' => [],
        ];
    }
}
