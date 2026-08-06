<?php

namespace Modules\Base\Support\Seo;

/**
 * Server-side counterpart of resources/js/utils/structuredData.js so JSON-LD can
 * be emitted in the first HTML response as well as by the Inertia head.
 */
final class JsonLd
{
    public const CONTEXT = 'https://schema.org';

    /**
     * Recursively drop null values, empty strings and empty arrays.
     */
    public static function omitEmpty(mixed $value): mixed
    {
        if (! is_array($value)) {
            return self::isBlank($value) ? null : $value;
        }

        $isList = array_is_list($value);
        $result = [];

        foreach ($value as $key => $item) {
            $cleaned = self::omitEmpty($item);
            if (self::isBlank($cleaned)) {
                continue;
            }

            if ($isList) {
                $result[] = $cleaned;
            } else {
                $result[$key] = $cleaned;
            }
        }

        return $result === [] ? null : $result;
    }

    /**
     * Keep only usable absolute-ish image URLs (placeholders are not valid schema images).
     *
     * @param  array<int, mixed>  $urls
     * @return list<string>
     */
    public static function filterImages(array $urls): array
    {
        $filtered = [];

        foreach ($urls as $url) {
            if (! is_string($url)) {
                continue;
            }

            $trimmed = trim($url);
            if ($trimmed === '' || preg_match('#/(?:blank\.png|default\.jpg)(?:\?.*)?$#i', $trimmed)) {
                continue;
            }

            if (! in_array($trimmed, $filtered, true)) {
                $filtered[] = $trimmed;
            }
        }

        return $filtered;
    }

    /**
     * Decode an admin-authored JSON-LD textarea. Invalid JSON is dropped so a
     * malformed value can never break the document head.
     *
     * @return array<array-key, mixed>|null
     */
    public static function decode(mixed $raw): ?array
    {
        if (is_array($raw)) {
            return $raw === [] ? null : $raw;
        }

        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode(trim($raw), true);

        return is_array($decoded) && $decoded !== [] ? $decoded : null;
    }

    /**
     * Encode for inline `<script type="application/ld+json">`. HEX_TAG/HEX_AMP keep
     * a `</script>` inside the payload from closing the tag while staying valid JSON.
     */
    public static function encode(mixed $data): string
    {
        if ($data === null || $data === [] || $data === '') {
            return '';
        }

        $json = json_encode(
            $data,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );

        return is_string($json) ? $json : '';
    }

    private static function isBlank(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        return is_array($value) && $value === [];
    }
}
