//#region resources/js/utils/structuredData.js
var SCHEMA_CONTEXT = "https://schema.org";
/**
* Strip HTML and collapse whitespace for schema text fields.
*
* @param {unknown} value
* @returns {string}
*/
function stripHtml(value) {
	if (typeof value !== "string") return "";
	return value.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
}
/**
* @param {unknown} value
* @returns {boolean}
*/
function isBlank(value) {
	if (value === null || value === void 0) return true;
	if (typeof value === "string") return value.trim() === "";
	if (Array.isArray(value)) return value.length === 0;
	return false;
}
/**
* Recursively drop empty strings, null, undefined, and empty arrays/objects.
*
* @param {unknown} value
* @returns {unknown}
*/
function omitEmpty(value) {
	if (Array.isArray(value)) {
		const items = value.map((item) => omitEmpty(item)).filter((item) => !isBlank(item));
		return items.length > 0 ? items : void 0;
	}
	if (value && typeof value === "object") {
		const result = {};
		for (const [key, raw] of Object.entries(value)) {
			const cleaned = omitEmpty(raw);
			if (!isBlank(cleaned)) result[key] = cleaned;
		}
		return Object.keys(result).length > 0 ? result : void 0;
	}
	return isBlank(value) ? void 0 : value;
}
/**
* @param {Record<string, string>|null|undefined} social
* @returns {string[]}
*/
function collectSocialUrls(social) {
	if (!social || typeof social !== "object") return [];
	return Object.values(social).filter((url) => typeof url === "string" && /^https?:\/\//i.test(url.trim()));
}
/**
* @param {string[]} urls
* @returns {string[]}
*/
function filterSchemaImages(urls) {
	return urls.filter((url) => {
		if (typeof url !== "string" || url.trim() === "") return false;
		const trimmed = url.trim();
		return !/\/blank\.png(?:\?.*)?$/i.test(trimmed) && !/\/default\.jpg(?:\?.*)?$/i.test(trimmed);
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
function buildWebsiteSchema({ name, url, description, searchUrlTemplate } = {}) {
	const schema = omitEmpty({
		"@context": SCHEMA_CONTEXT,
		"@type": "WebSite",
		name,
		url,
		description,
		potentialAction: typeof searchUrlTemplate === "string" && searchUrlTemplate.includes("{search_term_string}") ? {
			"@type": "SearchAction",
			target: {
				"@type": "EntryPoint",
				urlTemplate: searchUrlTemplate
			},
			"query-input": "required name=search_term_string"
		} : void 0
	});
	return schema && typeof schema === "object" ? schema : void 0;
}
/**
* BreadcrumbList schema from an ordered list of `{ name, url }` crumbs.
*
* @param {Array<{name?: string, url?: string}>} items
* @returns {object|undefined}
*/
function buildBreadcrumbSchema(items = []) {
	const list = (Array.isArray(items) ? items : []).map((item, index) => {
		const name = typeof item?.name === "string" ? item.name.trim() : "";
		if (name === "") return null;
		const url = typeof item?.url === "string" && item.url.trim() !== "" ? item.url.trim() : void 0;
		return omitEmpty({
			"@type": "ListItem",
			position: index + 1,
			name,
			item: url
		});
	}).filter(Boolean);
	if (list.length === 0) return;
	return {
		"@context": SCHEMA_CONTEXT,
		"@type": "BreadcrumbList",
		itemListElement: list
	};
}
/**
* @param {object} params
* @returns {object|undefined}
*/
function buildOrganizationSchema({ name, url, description, logo, email, phone, address, sameAs = [] }) {
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
			contactType: "customer service"
		}),
		address: address ? {
			"@type": "PostalAddress",
			streetAddress: address
		} : void 0,
		sameAs: sameAs.length > 0 ? sameAs : void 0
	});
	return schema && typeof schema === "object" ? schema : void 0;
}
/**
* @param {object} params
* @returns {object|undefined}
*/
function buildRealEstateListingSchema({ name, description, url, images = [], datePosted, dateModified, price, priceCurrency = "USD", isSoldOut = false, addressLocality, addressRegion, addressCountry = "TR", latitude, longitude, minArea, maxArea, propertyType }) {
	const numericPrice = Number(price);
	const hasPrice = Number.isFinite(numericPrice) && numericPrice > 0;
	const floorSize = (() => {
		const min = Number(minArea);
		const max = Number(maxArea);
		const hasMin = Number.isFinite(min) && min > 0;
		const hasMax = Number.isFinite(max) && max > 0;
		if (!hasMin && !hasMax) return;
		if (hasMin && hasMax && min !== max) return {
			"@type": "QuantitativeValue",
			minValue: min,
			maxValue: max,
			unitCode: "FTK"
		};
		return {
			"@type": "QuantitativeValue",
			value: hasMin ? min : max,
			unitCode: "FTK"
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
		offers: hasPrice ? {
			"@type": "Offer",
			price: numericPrice,
			priceCurrency,
			availability: isSoldOut ? `${SCHEMA_CONTEXT}/SoldOut` : `${SCHEMA_CONTEXT}/InStock`,
			url
		} : void 0,
		address: omitEmpty({
			"@type": "PostalAddress",
			addressLocality,
			addressRegion,
			addressCountry
		}),
		geo: Number.isFinite(Number(latitude)) && Number.isFinite(Number(longitude)) ? {
			"@type": "GeoCoordinates",
			latitude: Number(latitude),
			longitude: Number(longitude)
		} : void 0,
		floorSize,
		additionalType: propertyType || void 0
	});
	return schema && typeof schema === "object" ? schema : void 0;
}
/**
* @param {object} params
* @returns {object|undefined}
*/
function buildArticleSchema({ headline, description, image, images = [], datePublished, dateModified, url, publisherName, publisherLogo }) {
	const imageList = filterSchemaImages([...typeof image === "string" ? [image] : [], ...images]);
	const schema = omitEmpty({
		"@context": SCHEMA_CONTEXT,
		"@type": "Article",
		headline,
		description,
		image: imageList.length > 0 ? imageList : void 0,
		datePublished,
		dateModified,
		author: publisherName ? {
			"@type": "Organization",
			name: publisherName
		} : void 0,
		publisher: publisherName ? omitEmpty({
			"@type": "Organization",
			name: publisherName,
			logo: publisherLogo ? {
				"@type": "ImageObject",
				url: publisherLogo
			} : void 0
		}) : void 0,
		mainEntityOfPage: url ? {
			"@type": "WebPage",
			"@id": url
		} : void 0,
		url
	});
	return schema && typeof schema === "object" ? schema : void 0;
}
//#endregion
export { buildWebsiteSchema as a, stripHtml as c, buildRealEstateListingSchema as i, buildBreadcrumbSchema as n, collectSocialUrls as o, buildOrganizationSchema as r, filterSchemaImages as s, buildArticleSchema as t };
