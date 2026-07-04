const SCHEMA_CONTEXT = "https://schema.org";

/**
 * Strip HTML and collapse whitespace for schema text fields.
 *
 * @param {unknown} value
 * @returns {string}
 */
export function stripHtml(value) {
    if (typeof value !== "string") {
        return "";
    }

    return value.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
}

/**
 * @param {unknown} value
 * @returns {boolean}
 */
function isBlank(value) {
    if (value === null || value === undefined) {
        return true;
    }

    if (typeof value === "string") {
        return value.trim() === "";
    }

    if (Array.isArray(value)) {
        return value.length === 0;
    }

    return false;
}

/**
 * Recursively drop empty strings, null, undefined, and empty arrays/objects.
 *
 * @param {unknown} value
 * @returns {unknown}
 */
export function omitEmpty(value) {
    if (Array.isArray(value)) {
        const items = value
            .map((item) => omitEmpty(item))
            .filter((item) => !isBlank(item));

        return items.length > 0 ? items : undefined;
    }

    if (value && typeof value === "object") {
        const result = {};

        for (const [key, raw] of Object.entries(value)) {
            const cleaned = omitEmpty(raw);
            if (!isBlank(cleaned)) {
                result[key] = cleaned;
            }
        }

        return Object.keys(result).length > 0 ? result : undefined;
    }

    return isBlank(value) ? undefined : value;
}

/**
 * @param {Record<string, string>|null|undefined} social
 * @returns {string[]}
 */
export function collectSocialUrls(social) {
    if (!social || typeof social !== "object") {
        return [];
    }

    return Object.values(social).filter(
        (url) => typeof url === "string" && /^https?:\/\//i.test(url.trim()),
    );
}

/**
 * @param {string[]} urls
 * @returns {string[]}
 */
export function filterSchemaImages(urls) {
    return urls.filter((url) => {
        if (typeof url !== "string" || url.trim() === "") {
            return false;
        }

        const trimmed = url.trim();

        return (
            !/\/blank\.png(?:\?.*)?$/i.test(trimmed) &&
            !/\/default\.jpg(?:\?.*)?$/i.test(trimmed)
        );
    });
}

/**
 * WebSite schema, optionally with a SearchAction (sitelinks search box).
 *
 * @param {object} params
 * @param {string} [params.name]
 * @param {string} [params.url]
 * @param {string} [params.description]
 * @param {string} [params.searchUrlTemplate] Absolute URL with `{search_term_string}` placeholder.
 * @returns {object|undefined}
 */
export function buildWebsiteSchema({
    name,
    url,
    description,
    searchUrlTemplate,
} = {}) {
    const hasTemplate =
        typeof searchUrlTemplate === "string" &&
        searchUrlTemplate.includes("{search_term_string}");

    const schema = omitEmpty({
        "@context": SCHEMA_CONTEXT,
        "@type": "WebSite",
        name,
        url,
        description,
        potentialAction: hasTemplate
            ? {
                  "@type": "SearchAction",
                  target: {
                      "@type": "EntryPoint",
                      urlTemplate: searchUrlTemplate,
                  },
                  "query-input": "required name=search_term_string",
              }
            : undefined,
    });

    return schema && typeof schema === "object" ? schema : undefined;
}

/**
 * BreadcrumbList schema from an ordered list of `{ name, url }` crumbs.
 *
 * @param {Array<{name?: string, url?: string}>} items
 * @returns {object|undefined}
 */
export function buildBreadcrumbSchema(items = []) {
    const list = (Array.isArray(items) ? items : [])
        .map((item, index) => {
            const name =
                typeof item?.name === "string" ? item.name.trim() : "";
            if (name === "") {
                return null;
            }

            const url =
                typeof item?.url === "string" && item.url.trim() !== ""
                    ? item.url.trim()
                    : undefined;

            return omitEmpty({
                "@type": "ListItem",
                position: index + 1,
                name,
                item: url,
            });
        })
        .filter(Boolean);

    if (list.length === 0) {
        return undefined;
    }

    return {
        "@context": SCHEMA_CONTEXT,
        "@type": "BreadcrumbList",
        itemListElement: list,
    };
}

/**
 * @param {object} params
 * @returns {object|undefined}
 */
export function buildOrganizationSchema({
    name,
    url,
    description,
    logo,
    email,
    phone,
    address,
    sameAs = [],
}) {
    const schema = omitEmpty({
        "@context": SCHEMA_CONTEXT,
        "@type": "Organization",
        name,
        url,
        description,
        logo,
        contactPoint: omitEmpty({
            "@type": "ContactPoint",
            telephone: phone,
            email,
            contactType: "customer service",
        }),
        address: address
            ? {
                  "@type": "PostalAddress",
                  streetAddress: address,
              }
            : undefined,
        sameAs: sameAs.length > 0 ? sameAs : undefined,
    });

    return schema && typeof schema === "object" ? schema : undefined;
}

/**
 * @param {object} params
 * @returns {object|undefined}
 */
export function buildRealEstateListingSchema({
    name,
    description,
    url,
    images = [],
    datePosted,
    dateModified,
    price,
    priceCurrency = "USD",
    isSoldOut = false,
    addressLocality,
    addressRegion,
    addressCountry = "TR",
    latitude,
    longitude,
    minArea,
    maxArea,
    propertyType,
}) {
    const numericPrice = Number(price);
    const hasPrice = Number.isFinite(numericPrice) && numericPrice > 0;

    const floorSize = (() => {
        const min = Number(minArea);
        const max = Number(maxArea);
        const hasMin = Number.isFinite(min) && min > 0;
        const hasMax = Number.isFinite(max) && max > 0;

        if (!hasMin && !hasMax) {
            return undefined;
        }

        if (hasMin && hasMax && min !== max) {
            return {
                "@type": "QuantitativeValue",
                minValue: min,
                maxValue: max,
                unitCode: "FTK",
            };
        }

        return {
            "@type": "QuantitativeValue",
            value: hasMin ? min : max,
            unitCode: "FTK",
        };
    })();

    const schema = omitEmpty({
        "@context": SCHEMA_CONTEXT,
        "@type": "RealEstateListing",
        name,
        description,
        url,
        image: filterSchemaImages(images),
        datePosted,
        dateModified,
        offers: hasPrice
            ? {
                  "@type": "Offer",
                  price: numericPrice,
                  priceCurrency,
                  availability: isSoldOut
                      ? `${SCHEMA_CONTEXT}/SoldOut`
                      : `${SCHEMA_CONTEXT}/InStock`,
                  url,
              }
            : undefined,
        address: omitEmpty({
            "@type": "PostalAddress",
            addressLocality,
            addressRegion,
            addressCountry,
        }),
        geo:
            Number.isFinite(Number(latitude)) && Number.isFinite(Number(longitude))
                ? {
                      "@type": "GeoCoordinates",
                      latitude: Number(latitude),
                      longitude: Number(longitude),
                  }
                : undefined,
        floorSize,
        additionalType: propertyType || undefined,
    });

    return schema && typeof schema === "object" ? schema : undefined;
}

/**
 * @param {object} params
 * @returns {object|undefined}
 */
export function buildArticleSchema({
    headline,
    description,
    image,
    images = [],
    datePublished,
    dateModified,
    url,
    publisherName,
    publisherLogo,
}) {
    const imageList = filterSchemaImages([
        ...(typeof image === "string" ? [image] : []),
        ...images,
    ]);

    const schema = omitEmpty({
        "@context": SCHEMA_CONTEXT,
        "@type": "Article",
        headline,
        description,
        image: imageList.length > 0 ? imageList : undefined,
        datePublished,
        dateModified,
        author: publisherName
            ? {
                  "@type": "Organization",
                  name: publisherName,
              }
            : undefined,
        publisher: publisherName
            ? omitEmpty({
                  "@type": "Organization",
                  name: publisherName,
                  logo: publisherLogo
                      ? {
                            "@type": "ImageObject",
                            url: publisherLogo,
                        }
                      : undefined,
              })
            : undefined,
        mainEntityOfPage: url
            ? {
                  "@type": "WebPage",
                  "@id": url,
              }
            : undefined,
        url,
    });

    return schema && typeof schema === "object" ? schema : undefined;
}
