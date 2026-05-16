/**
 * Front-office URL for a CMS page slug (localized when Ziggy route exists).
 */
export function cmsPageUrl(slug) {
    const s = String(slug || "").trim();
    if (!s) {
        return "#";
    }
    try {
        if (typeof route === "function" && route().has?.("page.show")) {
            return route("page.show", { slug: s });
        }
    } catch {
        // ignore
    }
    return `/${s}`;
}
