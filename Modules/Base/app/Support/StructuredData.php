<?php

namespace Modules\Base\Support;

/**
 * JSON-LD builders ported from resources/js/utils/structuredData.js.
 */
final class StructuredData
{
    private const SCHEMA_CONTEXT = 'https://schema.org';

    public static function stripHtml(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');
    }

    public static function omitEmpty(mixed $value): mixed
    {
        if (is_array($value)) {
            if (array_is_list($value)) {
                $items = [];
                foreach ($value as $item) {
                    $cleaned = self::omitEmpty($item);
                    if (! self::isBlank($cleaned)) {
                        $items[] = $cleaned;
                    }
                }

                return $items !== [] ? $items : null;
            }

            $result = [];
            foreach ($value as $key => $raw) {
                $cleaned = self::omitEmpty($raw);
                if (! self::isBlank($cleaned)) {
                    $result[$key] = $cleaned;
                }
            }

            return $result !== [] ? $result : null;
        }

        return self::isBlank($value) ? null : $value;
    }

    /**
     * @param  array<string, mixed>|null  $social
     * @return list<string>
     */
    public static function collectSocialUrls(?array $social): array
    {
        if ($social === null) {
            return [];
        }

        $urls = [];
        foreach ($social as $url) {
            if (is_string($url) && preg_match('#^https?://#i', trim($url))) {
                $urls[] = trim($url);
            }
        }

        return $urls;
    }

    /**
     * @param  list<string>  $urls
     * @return list<string>
     */
    public static function filterSchemaImages(array $urls): array
    {
        return array_values(array_filter($urls, static function ($url): bool {
            if (! is_string($url) || trim($url) === '') {
                return false;
            }
            $trimmed = trim($url);

            return ! preg_match('#/blank\.png(?:\?.*)?$#i', $trimmed)
                && ! preg_match('#/default\.jpg(?:\?.*)?$#i', $trimmed);
        }));
    }

    /**
     * @param  array{name?: string, url?: string, description?: string, searchUrlTemplate?: string}  $params
     * @return array<string, mixed>|null
     */
    public static function buildWebsiteSchema(array $params = []): ?array
    {
        $template = $params['searchUrlTemplate'] ?? null;
        $hasTemplate = is_string($template) && str_contains($template, '{search_term_string}');

        $schema = self::omitEmpty([
            '@context' => self::SCHEMA_CONTEXT,
            '@type' => 'WebSite',
            'name' => $params['name'] ?? null,
            'url' => $params['url'] ?? null,
            'description' => $params['description'] ?? null,
            'potentialAction' => $hasTemplate ? [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $template,
                ],
                'query-input' => 'required name=search_term_string',
            ] : null,
        ]);

        return is_array($schema) ? $schema : null;
    }

    /**
     * @param  list<array{name?: string, url?: string}>  $items
     * @return array<string, mixed>|null
     */
    public static function buildBreadcrumbSchema(array $items = []): ?array
    {
        $list = [];
        foreach (array_values($items) as $index => $item) {
            $name = isset($item['name']) && is_string($item['name']) ? trim($item['name']) : '';
            if ($name === '') {
                continue;
            }
            $url = isset($item['url']) && is_string($item['url']) && trim($item['url']) !== ''
                ? trim($item['url'])
                : null;
            $entry = self::omitEmpty([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $name,
                'item' => $url,
            ]);
            if (is_array($entry)) {
                $list[] = $entry;
            }
        }

        if ($list === []) {
            return null;
        }

        return [
            '@context' => self::SCHEMA_CONTEXT,
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    /**
     * @param  array{name?: string, url?: string, description?: string, logo?: string, email?: string, phone?: string, address?: string, sameAs?: list<string>}  $params
     * @return array<string, mixed>|null
     */
    public static function buildOrganizationSchema(array $params = []): ?array
    {
        $sameAs = $params['sameAs'] ?? [];
        $address = $params['address'] ?? null;

        $schema = self::omitEmpty([
            '@context' => self::SCHEMA_CONTEXT,
            '@type' => 'Organization',
            'name' => $params['name'] ?? null,
            'url' => $params['url'] ?? null,
            'description' => $params['description'] ?? null,
            'logo' => $params['logo'] ?? null,
            'contactPoint' => self::omitEmpty([
                '@type' => 'ContactPoint',
                'telephone' => $params['phone'] ?? null,
                'email' => $params['email'] ?? null,
                'contactType' => 'customer service',
            ]),
            'address' => $address ? [
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
            ] : null,
            'sameAs' => $sameAs !== [] ? $sameAs : null,
        ]);

        return is_array($schema) ? $schema : null;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>|null
     */
    public static function buildRealEstateListingSchema(array $params = []): ?array
    {
        $price = isset($params['price']) ? (float) $params['price'] : null;
        $hasPrice = $price !== null && is_finite($price) && $price > 0;
        $images = self::filterSchemaImages(array_values(array_filter(
            (array) ($params['images'] ?? []),
            static fn ($u) => is_string($u)
        )));

        $minArea = isset($params['minArea']) ? (float) $params['minArea'] : null;
        $maxArea = isset($params['maxArea']) ? (float) $params['maxArea'] : null;
        $hasMin = $minArea !== null && is_finite($minArea) && $minArea > 0;
        $hasMax = $maxArea !== null && is_finite($maxArea) && $maxArea > 0;

        $floorSize = null;
        if ($hasMin || $hasMax) {
            if ($hasMin && $hasMax && $minArea !== $maxArea) {
                $floorSize = [
                    '@type' => 'QuantitativeValue',
                    'minValue' => $minArea,
                    'maxValue' => $maxArea,
                    'unitCode' => 'FTK',
                ];
            } else {
                $floorSize = [
                    '@type' => 'QuantitativeValue',
                    'value' => $hasMin ? $minArea : $maxArea,
                    'unitCode' => 'FTK',
                ];
            }
        }

        $lat = isset($params['latitude']) ? (float) $params['latitude'] : null;
        $lng = isset($params['longitude']) ? (float) $params['longitude'] : null;
        $url = $params['url'] ?? null;
        $isSoldOut = (bool) ($params['isSoldOut'] ?? false);

        $schema = self::omitEmpty([
            '@context' => self::SCHEMA_CONTEXT,
            '@type' => 'RealEstateListing',
            'name' => $params['name'] ?? null,
            'description' => $params['description'] ?? null,
            'url' => $url,
            'image' => $images !== [] ? $images : null,
            'datePosted' => $params['datePosted'] ?? null,
            'dateModified' => $params['dateModified'] ?? null,
            'offers' => $hasPrice ? [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => $params['priceCurrency'] ?? 'USD',
                'availability' => $isSoldOut
                    ? self::SCHEMA_CONTEXT.'/SoldOut'
                    : self::SCHEMA_CONTEXT.'/InStock',
                'url' => $url,
            ] : null,
            'address' => self::omitEmpty([
                '@type' => 'PostalAddress',
                'addressLocality' => $params['addressLocality'] ?? null,
                'addressRegion' => $params['addressRegion'] ?? null,
                'addressCountry' => $params['addressCountry'] ?? 'TR',
            ]),
            'geo' => ($lat !== null && $lng !== null && is_finite($lat) && is_finite($lng)) ? [
                '@type' => 'GeoCoordinates',
                'latitude' => $lat,
                'longitude' => $lng,
            ] : null,
            'floorSize' => $floorSize,
            'additionalType' => $params['propertyType'] ?? null,
        ]);

        return is_array($schema) ? $schema : null;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>|null
     */
    public static function buildArticleSchema(array $params = []): ?array
    {
        $images = self::filterSchemaImages(array_values(array_filter([
            ...(isset($params['image']) && is_string($params['image']) ? [$params['image']] : []),
            ...((array) ($params['images'] ?? [])),
        ], static fn ($u) => is_string($u))));

        $publisherName = $params['publisherName'] ?? null;
        $publisherLogo = $params['publisherLogo'] ?? null;
        $url = $params['url'] ?? null;

        $schema = self::omitEmpty([
            '@context' => self::SCHEMA_CONTEXT,
            '@type' => 'Article',
            'headline' => $params['headline'] ?? null,
            'description' => $params['description'] ?? null,
            'image' => $images !== [] ? $images : null,
            'datePublished' => $params['datePublished'] ?? null,
            'dateModified' => $params['dateModified'] ?? null,
            'author' => $publisherName ? [
                '@type' => 'Organization',
                'name' => $publisherName,
            ] : null,
            'publisher' => $publisherName ? self::omitEmpty([
                '@type' => 'Organization',
                'name' => $publisherName,
                'logo' => $publisherLogo ? [
                    '@type' => 'ImageObject',
                    'url' => $publisherLogo,
                ] : null,
            ]) : null,
            'mainEntityOfPage' => $url ? [
                '@type' => 'WebPage',
                '@id' => $url,
            ] : null,
            'url' => $url,
        ]);

        return is_array($schema) ? $schema : null;
    }

    private static function isBlank(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        if (is_string($value)) {
            return trim($value) === '';
        }
        if (is_array($value)) {
            return $value === [];
        }

        return false;
    }
}
