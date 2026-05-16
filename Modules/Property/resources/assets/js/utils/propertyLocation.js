/**
 * @param {string|Record<string, string>|null|undefined} name
 * @param {string} locale
 */
export function localizedLocationName(name, locale = "en") {
    if (typeof name === "string") {
        return name.trim();
    }

    if (name && typeof name === "object") {
        const value = name[locale] ?? name.en ?? Object.values(name)[0];

        return typeof value === "string" ? value.trim() : "";
    }

    return "";
}

/**
 * @param {{ city?: { name?: unknown }, district?: { name?: unknown }, area?: { name?: unknown } }|null|undefined} location
 * @param {string} locale
 */
export function propertyLocationLine(location, locale = "en") {
    if (!location) {
        return "";
    }

    const parts = [
        localizedLocationName(location.area?.name, locale),
        localizedLocationName(location.district?.name, locale),
        localizedLocationName(location.city?.name, locale),
    ].filter(Boolean);

    return parts.join(", ");
}
