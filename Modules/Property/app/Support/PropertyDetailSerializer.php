<?php

namespace Modules\Property\Support;

use Modules\Base\Support\Media\MediaAssetResolver;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertySlideMedia;
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
        $resolver = app(MediaAssetResolver::class);
        $thumbnailMedia = $resolver->resolve($property->thumbnail);
        $fallbackAlt = (string) ($property->title ?: $property->project_name ?: '');

        return [
            'id' => $property->id,
            'project_code' => $property->project_code,
            'url_key' => $property->url_key,
            'slug' => $property->url_key,
            'title' => $property->title,
            'project_name' => $property->project_name,
            'overview' => $property->overview,
            'why_to_buy' => $property->why_to_buy,
            'content' => $property->content,
            'price' => $property->price,
            'start_price' => self::startPrice($property),
            'min_area' => $property->min_area,
            'max_area' => $property->max_area,
            'thumbnail_url' => $thumbnailMedia['url'] ?? ($property->thumbnail
                ? asset('storage/'.$property->thumbnail)
                : asset('images/blank.png')),
            'thumbnail_alt' => ($thumbnailMedia['alt_text'] ?? null) ?: $fallbackAlt,
            'thumbnail_title' => $thumbnailMedia['title'] ?? null,
            'slides' => self::slides($property, $resolver, $fallbackAlt),
            'slide_categories' => self::slideCategories($property, $resolver, $fallbackAlt),
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
            'videos' => self::videos($property),
            'is_featured' => (bool) $property->is_featured,
            'is_sold_out' => (bool) $property->is_sold_out,
            'is_recommended' => (bool) $property->is_recommended,
            'is_citizenship_eligible' => (bool) $property->is_citizenship_eligible,
            'metadata' => [
                'meta_title' => $metadata['meta_title'] ?? null,
                'meta_description' => $metadata['meta_description'] ?? null,
                'meta_keywords' => $metadata['meta_keywords'] ?? [],
                'schema' => $metadata['schema'] ?? null,
                'meta_img' => $metadata['meta_img'] ?? null,
            ],
            'highlights' => ListingCardHighlightBuilder::forProperty($property),
            'attributes' => PropertyAttributeDisplaySerializer::forProperty($property),
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
     * @return list<array{id: int, position: int, image_url: string, alt: string, title: ?string, caption: ?string}>
     */
    private static function slides(Property $property, MediaAssetResolver $resolver, string $fallbackAlt): array
    {
        $thumbnailPath = $property->thumbnail;

        if (! $property->relationLoaded('slideMedia')) {
            return [];
        }

        $paths = $property->slideMedia
            ->where('type', PropertySlideMedia::TYPE_IMAGE)
            ->filter(static fn (PropertySlideMedia $media): bool => $thumbnailPath === null
                || $media->path !== $thumbnailPath)
            ->pluck('path')
            ->filter()
            ->values()
            ->all();
        $resolved = $resolver->resolveMany($paths);

        return $property->slideMedia
            ->where('type', PropertySlideMedia::TYPE_IMAGE)
            ->filter(static fn (PropertySlideMedia $media): bool => $thumbnailPath === null
                || $media->path !== $thumbnailPath)
            ->sortBy(self::mediaSortKey(...))
            ->map(static function (PropertySlideMedia $media) use ($resolved, $fallbackAlt): array {
                $meta = $resolved[$media->path] ?? null;

                return [
                    'id' => $media->id,
                    'position' => $media->position,
                    'image_url' => $meta['url'] ?? asset('storage/'.$media->path),
                    'alt' => ($meta['alt_text'] ?? null) ?: $fallbackAlt,
                    'title' => $meta['title'] ?? null,
                    'caption' => $meta['caption'] ?? null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private static function videos(Property $property): array
    {
        if (! $property->relationLoaded('slideMedia')) {
            return [];
        }

        return $property->slideMedia
            ->where('type', PropertySlideMedia::TYPE_VIDEO)
            ->sortBy(self::mediaSortKey(...))
            ->map(static fn (PropertySlideMedia $media): string => asset('storage/'.$media->path))
            ->values()
            ->all();
    }

    /**
     * Grouped slide media by published category for the front-office gallery tabs.
     *
     * @return list<array{
     *     id: int,
     *     name: string,
     *     slug: string,
     *     position: int,
     *     assets_count: int,
     *     assets: list<array{id: int, type: string, url: string, alt: ?string, title: ?string, position: int}>
     * }>
     */
    private static function slideCategories(
        Property $property,
        MediaAssetResolver $resolver,
        string $fallbackAlt,
    ): array {
        if (! $property->relationLoaded('slideMedia')) {
            return [];
        }

        $imagePaths = $property->slideMedia
            ->where('type', PropertySlideMedia::TYPE_IMAGE)
            ->pluck('path')
            ->filter()
            ->values()
            ->all();
        $resolved = $resolver->resolveMany($imagePaths);

        return $property->slideMedia
            ->filter(static fn (PropertySlideMedia $media): bool => $media->slideCategory !== null)
            ->groupBy('slide_category_id')
            ->map(static function ($mediaGroup) use ($resolved, $fallbackAlt): ?array {
                /** @var PropertySlideMedia $first */
                $first = $mediaGroup->first();
                $category = $first->slideCategory;
                if ($category === null) {
                    return null;
                }

                $assets = $mediaGroup
                    ->sortBy(static fn (PropertySlideMedia $media): array => [
                        $media->position,
                        $media->id,
                    ])
                    ->map(static function (PropertySlideMedia $media) use ($resolved, $fallbackAlt): array {
                        if ($media->type === PropertySlideMedia::TYPE_VIDEO) {
                            return [
                                'id' => $media->id,
                                'type' => PropertySlideMedia::TYPE_VIDEO,
                                'url' => asset('storage/'.$media->path),
                                'alt' => null,
                                'title' => null,
                                'position' => $media->position,
                            ];
                        }

                        $meta = $resolved[$media->path] ?? null;

                        return [
                            'id' => $media->id,
                            'type' => PropertySlideMedia::TYPE_IMAGE,
                            'url' => $meta['url'] ?? asset('storage/'.$media->path),
                            'alt' => ($meta['alt_text'] ?? null) ?: $fallbackAlt,
                            'title' => $meta['title'] ?? null,
                            'position' => $media->position,
                        ];
                    })
                    ->values()
                    ->all();

                if ($assets === []) {
                    return null;
                }

                return [
                    'id' => $category->id,
                    'name' => (string) $category->name,
                    'slug' => (string) $category->slug,
                    'position' => (int) $category->position,
                    'assets_count' => count($assets),
                    'assets' => $assets,
                ];
            })
            ->filter()
            ->sortBy(static fn (array $row): array => [
                $row['position'],
                $row['id'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{int, int, int}
     */
    private static function mediaSortKey(PropertySlideMedia $media): array
    {
        return [
            (int) ($media->slideCategory?->position ?? PHP_INT_MAX),
            $media->position,
            $media->id,
        ];
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
