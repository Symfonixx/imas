<?php

namespace Modules\Property\Support;

use Modules\Property\Models\Property;

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
            'min_area' => $property->min_area,
            'max_area' => $property->max_area,
            'thumbnail_url' => $property->thumbnail
                ? asset('storage/'.$property->thumbnail)
                : asset('images/blank.png'),
            'location' => $property->location
                ? ['id' => $property->location->id, 'name' => $property->location->name]
                : null,
            'property_type' => $property->propertyType
                ? [
                    'id' => $property->propertyType->id,
                    'name' => $property->propertyType->name,
                    'slug' => $property->propertyType->slug,
                ]
                : null,
            'url' => route('property.show', $property->id),
            'is_featured' => (bool) $property->is_featured,
            'is_sold_out' => (bool) $property->is_sold_out,
            'is_citizenship_eligible' => (bool) $property->is_citizenship_eligible,
            'youtube_video_url' => $property->youtube_video_url,
            'updated_at' => $property->updated_at?->toIso8601String(),
            'highlights' => ListingCardHighlightBuilder::forProperty($property),
        ];
    }
}
