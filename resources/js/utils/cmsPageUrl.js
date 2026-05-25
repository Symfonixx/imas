import {
    applyLocalePrefix,
    localizedFallbackPath,
    SUPPORTED_LOCALES,
} from "./localizedRoute.js";

function resolveLocale(locale) {
    if (locale && SUPPORTED_LOCALES.includes(locale)) {
        return locale;
    }
    if (typeof document !== "undefined") {
        const lang = String(
            document.documentElement.getAttribute("lang") || "",
        ).trim();
        if (SUPPORTED_LOCALES.includes(lang)) {
            return lang;
        }
    }
    return "en";
}

/**
 * Front-office URL for a CMS page slug (localized for the active locale).
 */
export function cmsPageUrl(slug, locale) {
    const s = String(slug || "").trim();
    const loc = resolveLocale(locale);

    if (!s) {
        return "#";
    }

    try {
        if (typeof route === "function" && route().has?.("page.show")) {
            return applyLocalePrefix(route("page.show", { slug: s }), loc);
        }
    } catch {
        // ignore
    }

    return localizedFallbackPath(`/${s}`, loc);
}

export { localizedFallbackPath, applyLocalePrefix } from "./localizedRoute.js";
