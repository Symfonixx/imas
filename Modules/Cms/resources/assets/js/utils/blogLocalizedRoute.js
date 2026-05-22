import { localizedRoute } from "@/utils/localizedRoute.js";

/**
 * Blog index URL with locale prefix (GET forms / Inertia links must use this).
 */
export function blogIndexLocalizedUrl(locale, params = {}) {
    return localizedRoute("blog.index", params, locale, "/blog");
}
