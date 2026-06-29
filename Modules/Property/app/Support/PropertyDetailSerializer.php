<?php

namespace Modules\Property\Support;

use Modules\Property\Models\Property;
use Modules\Property\Models\PropertySlide;
use Modules\Property\Models\UnitType;

final class PropertyDetailSerializer
{
    /**
     * Full front-office property payload for the show page.
     *
     * @return array<string, mixed>
     */
    public static function toArray(Property $property): array
    {
        $metadata = is_array($property->metadata) ? $property->metadata : [];

        return [
            'id' => $property->id,
            'project_code' => $property->project_code,
            'slug' => $property->project_code,
            'title' => $property->title,
            'project_name' => $property->project_name,
            'overview' => $property->overview,
            'why_to_buy' => $property->why_to_buy,
            'facilities' => $property->facilities,
            'content' => $property->content,
            'price' => $property->price,
            'start_price' => self::startPrice($property),
            'min_area' => $property->min_area,
            'max_area' => $property->max_area,
            'thumbnail_url' => $property->thumbnail
                ? asset('storage/'.$property->thumbnail)
                : asset('images/blank.png'),
            'slides' => self::slides($property),
            'location' => PropertyLocationHierarchySerializer::toArray($property->location),
            'unit_types' => self::unitTypes($property),
            'property_type' => $property->propertyType
                ? [
                    'id' => $property->propertyType->id,
                    'name' => $property->propertyType->name,
                    'slug' => $property->propertyType->slug,
                ]
                : null,
            'lat' => $property->lat,
            'lng' => $property->lng,
            'youtube_video_url' => $property->youtube_video_url,
            'is_featured' => (bool) $property->is_featured,
            'is_sold_out' => (bool) $property->is_sold_out,
            'is_recommended' => (bool) $property->is_recommended,
            'is_citizenship_eligible' => (bool) $property->is_citizenship_eligible,
            'metadata' => [
                'meta_title' => $metadata['meta_title'] ?? null,
                'meta_description' => $metadata['meta_description'] ?? null,
                'meta_keywords' => $metadata['meta_keywords'] ?? [],
                'schema' => $metadata['schema'] ?? null,
            ],
            'highlights' => ListingCardHighlightBuilder::forProperty($property),
            'is_favorited' => (bool) ($property->getAttribute('is_favorited') ?? false),
            'created_at' => $property->created_at?->toIso8601String(),
            'updated_at' => $property->updated_at?->toIso8601String(),
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
     * @return list<array{id: int, position: int, image_url: string}>
     */
    private static function slides(Property $property): array
    {
        if (! $property->relationLoaded('slides')) {
            return [];
        }

        $thumbnailPath = $property->thumbnail;

        return $property->slides
            ->filter(static fn (PropertySlide $slide): bool => $thumbnailPath === null
                || $slide->image !== $thumbnailPath)
            ->map(static fn (PropertySlide $slide): array => [
                'id' => $slide->id,
                'position' => (int) $slide->position,
                'image_url' => asset('storage/'.$slide->image),
            ])
            ->values()
            ->all();
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
