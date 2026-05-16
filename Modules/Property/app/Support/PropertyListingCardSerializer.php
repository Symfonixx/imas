<?php

namespace Modules\Property\Support;

use Modules\Property\Models\Property;
use Modules\Property\Models\UnitType;

final class PropertyListingCardSerializer
{
    /**
     * Shape expected by the front-office property card and horizontal rails.
     *
     * @return array<string, mixed>
     */
    public static function toArray(Property $property): array
    {
        return [
            'id' => $property->id,
            'project_code' => $property->project_code,
            'title' => $property->title,
            'project_name' => $property->project_name,
            'overview' => $property->overview,
            'price' => $property->price,
            'start_price' => self::startPrice($property),
            'min_area' => $property->min_area,
            'max_area' => $property->max_area,
            'thumbnail_url' => $property->thumbnail
                ? asset('storage/'.$property->thumbnail)
                : asset('images/blank.png'),
            'location' => PropertyLocationHierarchySerializer::toArray($property->location),
            'unit_types' => self::unitTypes($property),
            'property_type' => $property->propertyType
                ? [
                    'id' => $property->propertyType->id,
                    'name' => $property->propertyType->name,
                    'slug' => $property->propertyType->slug,
                ]
                : null,
            'url' => route('property.show', $property),
            'is_featured' => (bool) $property->is_featured,
            'is_sold_out' => (bool) $property->is_sold_out,
            'is_citizenship_eligible' => (bool) $property->is_citizenship_eligible,
            'youtube_video_url' => $property->youtube_video_url,
            'updated_at' => $property->updated_at?->toIso8601String(),
            'highlights' => ListingCardHighlightBuilder::forProperty($property),
            'is_favorited' => (bool) ($property->getAttribute('is_favorited') ?? false),
        ];
    }

    private static function startPrice(Property $property): float
    {
        if ($property->relationLoaded('unitTypes') && $property->unitTypes->isNotEmpty()) {
            return PropertyMetricsFromUnitTypes::fromUnitTypes($property->unitTypes)['price'];
        }

        return (float) $property->price;
    }

    /**
     * @return list<array{id: int, catalog_id: ?int, name: string, min_area: mixed, max_area: mixed, price: mixed}>
     */
    private static function unitTypes(Property $property): array
    {
        if (! $property->relationLoaded('unitTypes')) {
            return [];
        }

        return $property->unitTypes
            ->map(static fn (UnitType $unitType): array => [
                'id' => $unitType->id,
                'catalog_id' => $unitType->catalog_id,
                'name' => $unitType->name,
                'min_area' => $unitType->min_area,
                'max_area' => $unitType->max_area,
                'price' => $unitType->price,
            ])
            ->values()
            ->all();
    }
}
