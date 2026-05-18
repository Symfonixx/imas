/**
 * @param {string|Record<string, string>|null|undefined} value
 * @param {string} locale
 */
export function localizedField(value, locale = "en") {
    if (typeof value === "string") {
        return value.trim();
    }

    if (value && typeof value === "object") {
        const raw =
            value[locale] ??
            value.en ??
            Object.values(value).find((v) => typeof v === "string");

        return typeof raw === "string" ? raw.trim() : "";
    }

    return "";
}
