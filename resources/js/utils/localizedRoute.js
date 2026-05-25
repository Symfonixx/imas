export const SUPPORTED_LOCALES = ["en", "tr", "ar"];

/**
 * Prefix or swap the locale segment on a front-office path/URL.
 */
export function applyLocalePrefix(url, locale) {
    const loc = SUPPORTED_LOCALES.includes(locale) ? locale : "en";

    if (!url || url === "#") {
        return url;
    }

    try {
        const origin =
            typeof window !== "undefined"
                ? window.location.origin
                : "http://localhost";
        const parsed = new URL(url, origin);
        let segments = parsed.pathname.split("/").filter(Boolean);

        if (segments.length > 0 && SUPPORTED_LOCALES.includes(segments[0])) {
            segments.shift();
        }

        parsed.pathname =
            segments.length > 0 ? `/${loc}/${segments.join("/")}` : `/${loc}`;

        return `${parsed.pathname}${parsed.search}${parsed.hash}`;
    } catch {
        return localizedFallbackPath(url, loc);
    }
}

export function localizedFallbackPath(path, locale = "en") {
    const loc = SUPPORTED_LOCALES.includes(locale) ? locale : "en";
    const normalized =
        !path || path === "/"
            ? "/"
            : path.startsWith("/")
              ? path
              : `/${path}`;
    const segments = normalized.split("/").filter(Boolean);

    if (segments.length > 0 && SUPPORTED_LOCALES.includes(segments[0])) {
        segments[0] = loc;
        return `/${segments.join("/")}`;
    }

    if (normalized === "/") {
        return `/${loc}`;
    }

    return `/${loc}${normalized}`;
}

/**
 * Named Laravel route for the active locale (uses Ziggy when available).
 */
export function localizedRoute(name, params, locale, fallback = "#") {
    const loc = SUPPORTED_LOCALES.includes(locale) ? locale : "en";
    let url = fallback;

    try {
        if (typeof route === "function" && route().has?.(name)) {
            url = route(name, params);
        }
    } catch {
        // ignore
    }

    if (!url || url === "#") {
        return localizedFallbackPath(fallback, loc);
    }

    return applyLocalePrefix(url, loc);
}
