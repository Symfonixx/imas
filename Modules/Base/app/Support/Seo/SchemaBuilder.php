<?php

namespace Modules\Base\Support\Seo;

/**
 * Server-side Organization / WebSite / Article JSON-LD builders.
 * Mirrors resources/js/utils/structuredData.js so Blade and Vue heads agree.
 */
final class SchemaBuilder
{
    /**
     * @param  list<string>  $sameAs
     * @return array<string, mixed>|null
     */
    public static function organization(
        ?string $name,
        ?string $url,
        ?string $description = null,
        ?string $logo = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $address = null,
        array $sameAs = [],
    ): ?array {
        $schema = JsonLd::omitEmpty([
            '@context' => JsonLd::CONTEXT,
            '@type' => 'Organization',
            'name' => $name,
            'url' => $url,
            'description' => $description,
            'logo' => $logo,
            'contactPoint' => JsonLd::omitEmpty([
                '@type' => 'ContactPoint',
                'telephone' => $phone,
                'email' => $email,
                'contactType' => 'customer service',
            ]),
            'address' => $address
                ? [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $address,
                ]
                : null,
            'sameAs' => $sameAs !== [] ? array_values(array_filter($sameAs, static fn ($u) => is_string($u) && $u !== '')) : null,
        ]);

        return is_array($schema) ? $schema : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function website(
        ?string $name,
        ?string $url,
        ?string $description = null,
        ?string $searchUrlTemplate = null,
    ): ?array {
        $hasTemplate = is_string($searchUrlTemplate)
            && str_contains($searchUrlTemplate, '{search_term_string}');

        $schema = JsonLd::omitEmpty([
            '@context' => JsonLd::CONTEXT,
            '@type' => 'WebSite',
            'name' => $name,
            'url' => $url,
            'description' => $description,
            'potentialAction' => $hasTemplate
                ? [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => $searchUrlTemplate,
                    ],
                    'query-input' => 'required name=search_term_string',
                ]
                : null,
        ]);

        return is_array($schema) ? $schema : null;
    }

    /**
     * @param  list<string>  $images
     * @return array<string, mixed>|null
     */
    public static function article(
        ?string $headline,
        ?string $description = null,
        ?string $image = null,
        array $images = [],
        ?string $datePublished = null,
        ?string $dateModified = null,
        ?string $url = null,
        ?string $publisherName = null,
        ?string $publisherLogo = null,
    ): ?array {
        $imageList = JsonLd::filterImages(array_merge(
            is_string($image) ? [$image] : [],
            $images,
        ));

        $schema = JsonLd::omitEmpty([
            '@context' => JsonLd::CONTEXT,
            '@type' => 'Article',
            'headline' => $headline,
            'description' => $description,
            'image' => $imageList !== [] ? $imageList : null,
            'datePublished' => $datePublished,
            'dateModified' => $dateModified,
            'author' => $publisherName
                ? [
                    '@type' => 'Organization',
                    'name' => $publisherName,
                ]
                : null,
            'publisher' => $publisherName
                ? JsonLd::omitEmpty([
                    '@type' => 'Organization',
                    'name' => $publisherName,
                    'logo' => $publisherLogo
                        ? [
                            '@type' => 'ImageObject',
                            'url' => $publisherLogo,
                        ]
                        : null,
                ])
                : null,
            'mainEntityOfPage' => $url
                ? [
                    '@type' => 'WebPage',
                    '@id' => $url,
                ]
                : null,
            'url' => $url,
        ]);

        return is_array($schema) ? $schema : null;
    }

    /**
     * Absolute social profile URLs from settings-style key/value map.
     *
     * @param  array<string, mixed>  $social
     * @return list<string>
     */
    public static function collectSocialUrls(array $social): array
    {
        $urls = [];

        foreach ($social as $url) {
            if (! is_string($url)) {
                continue;
            }
            $trimmed = trim($url);
            if ($trimmed !== '' && preg_match('#^https?://#i', $trimmed)) {
                $urls[] = $trimmed;
            }
        }

        return $urls;
    }
}
