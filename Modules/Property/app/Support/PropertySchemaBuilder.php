<?php

namespace Modules\Property\Support;

use Modules\Base\Support\Seo\JsonLd;

/**
 * Builds the property show page JSON-LD from the serialized detail payload.
 * Mirrors buildRealEstateListingSchema / buildBreadcrumbSchema in
 * resources/js/utils/structuredData.js so the server and Inertia head agree.
 */
final class PropertySchemaBuilder
{
    /**
     * @param  array<string, mixed>  $payload  PropertyDetailSerializer output
     * @return array<string, mixed>|null
     */
    public static function realEstateListing(
        array $payload,
        string $canonical,
        string $description,
        string $locale,
    ): ?array {
        $location = is_array($payload['location'] ?? null) ? $payload['location'] : [];
        $price = self::startPrice($payload);
        $images = JsonLd::filterImages(array_merge(
            [$payload['thumbnail_url'] ?? null],
            array_map(
                static fn ($slide) => is_array($slide) ? ($slide['image_url'] ?? null) : null,
                is_array($payload['slides'] ?? null) ? $payload['slides'] : [],
            ),
        ));

        $schema = JsonLd::omitEmpty([
            '@context' => JsonLd::CONTEXT,
            '@type' => 'RealEstateListing',
            'name' => self::displayTitle($payload, $locale),
            'description' => $description,
            'url' => $canonical,
            'image' => $images,
            'datePosted' => $payload['created_at'] ?? null,
            'dateModified' => $payload['updated_at'] ?? null,
            'offers' => $price !== null && $price > 0
                ? [
                    '@type' => 'Offer',
                    'price' => $price,
                    'priceCurrency' => 'USD',
                    'availability' => JsonLd::CONTEXT.(($payload['is_sold_out'] ?? false) ? '/SoldOut' : '/InStock'),
                    'url' => $canonical,
                ]
                : null,
            'address' => JsonLd::omitEmpty([
                '@type' => 'PostalAddress',
                'addressLocality' => self::localized($location['area']['name'] ?? null, $locale)
                    ?: self::localized($location['district']['name'] ?? null, $locale),
                'addressRegion' => self::localized($location['city']['name'] ?? null, $locale),
                'addressCountry' => 'TR',
            ]),
            'geo' => self::geo($payload),
            'floorSize' => self::floorSize($payload),
            'additionalType' => self::localized($payload['property_type']['name'] ?? null, $locale),
        ]);

        return is_array($schema) ? $schema : null;
    }

    /**
     * @param  list<array{name: string, url?: string|null}>  $items
     * @return array<string, mixed>|null
     */
    public static function breadcrumb(array $items): ?array
    {
        $list = [];

        foreach ($items as $item) {
            $name = is_string($item['name'] ?? null) ? trim($item['name']) : '';
            if ($name === '') {
                continue;
            }

            $entry = JsonLd::omitEmpty([
                '@type' => 'ListItem',
                'position' => count($list) + 1,
                'name' => $name,
                'item' => is_string($item['url'] ?? null) ? trim($item['url']) : null,
            ]);

            if (is_array($entry)) {
                $list[] = $entry;
            }
        }

        if ($list === []) {
            return null;
        }

        return [
            '@context' => JsonLd::CONTEXT,
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function displayTitle(array $payload, string $locale): string
    {
        foreach (['title', 'project_name'] as $key) {
            $value = self::localized($payload[$key] ?? null, $locale);
            if ($value !== '') {
                return $value;
            }
        }

        return is_string($payload['project_code'] ?? null) ? trim($payload['project_code']) : '';
    }

    /**
     * Translatable attributes reach the payload either already localized (string)
     * or as a locale => value map.
     */
    public static function localized(mixed $value, string $locale): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (! is_array($value) || $value === []) {
            return '';
        }

        foreach ([$locale, 'en'] as $key) {
            if (is_string($value[$key] ?? null) && trim($value[$key]) !== '') {
                return trim($value[$key]);
            }
        }

        foreach ($value as $item) {
            if (is_string($item) && trim($item) !== '') {
                return trim($item);
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function startPrice(array $payload): ?float
    {
        foreach (['start_price', 'price'] as $key) {
            $value = $payload[$key] ?? null;
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private static function geo(array $payload): ?array
    {
        if (! is_numeric($payload['lat'] ?? null) || ! is_numeric($payload['lng'] ?? null)) {
            return null;
        }

        return [
            '@type' => 'GeoCoordinates',
            'latitude' => (float) $payload['lat'],
            'longitude' => (float) $payload['lng'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private static function floorSize(array $payload): ?array
    {
        $min = is_numeric($payload['min_area'] ?? null) ? (float) $payload['min_area'] : null;
        $max = is_numeric($payload['max_area'] ?? null) ? (float) $payload['max_area'] : null;
        $min = $min !== null && $min > 0 ? $min : null;
        $max = $max !== null && $max > 0 ? $max : null;

        if ($min === null && $max === null) {
            return null;
        }

        if ($min !== null && $max !== null && $min !== $max) {
            return [
                '@type' => 'QuantitativeValue',
                'minValue' => $min,
                'maxValue' => $max,
                'unitCode' => 'FTK',
            ];
        }

        return [
            '@type' => 'QuantitativeValue',
            'value' => $min ?? $max,
            'unitCode' => 'FTK',
        ];
    }
}
