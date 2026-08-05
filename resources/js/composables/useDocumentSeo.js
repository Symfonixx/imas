import { computed, unref } from "vue";
import { usePage } from "@inertiajs/vue3";

/**
 * Document SEO for hub / section pages — mirrors SeoDocumentService fallbacks.
 *
 * @param {object} [options]
 * @param {string|import('vue').Ref|import('vue').ComputedRef|(() => string)} [options.pageTitle] Short label → "{label} | {siteName}"
 * @param {string|import('vue').Ref|import('vue').ComputedRef|(() => string)} [options.title] Full document title (used as-is)
 * @param {string|import('vue').Ref|import('vue').ComputedRef|(() => string)} [options.description]
 * @param {string[]} [options.descriptionKeys]
 * @param {string|string[]|import('vue').Ref|import('vue').ComputedRef|(() => string|string[])} [options.keywords]
 * @param {string[]} [options.keywordsKeys]
 * @param {string|import('vue').Ref|import('vue').ComputedRef|(() => string)} [options.ogImage]
 * @param {string|import('vue').Ref|import('vue').ComputedRef|(() => string)} [options.canonical]
 * @param {string|import('vue').Ref|import('vue').ComputedRef|(() => string)} [options.ogType]
 * @param {boolean} [options.useGlobalTitleTemplate=false] Home-style global title template (no "| site" append)
 */
export function useDocumentSeo(options = {}) {
    const page = usePage();

    const globals = computed(() => page.props.globals ?? {});
    const seoMap = computed(() => globals.value.seo ?? {});
    const media = computed(() => globals.value.media ?? {});
    const siteName = computed(() => {
        const name = page.props.appName;
        return typeof name === "string" && name.trim() !== ""
            ? name.trim()
            : "IMas";
    });

    function resolveOption(value) {
        const v = typeof value === "function" ? value() : unref(value);
        return v;
    }

    function pickSeoString(...keys) {
        const s = seoMap.value;
        for (const key of keys) {
            if (typeof key !== "string" || key.trim() === "") {
                continue;
            }
            const v = s[key];
            if (typeof v === "string" && v.trim() !== "") {
                return v.trim();
            }
        }
        return "";
    }

    function asTrimmedString(value) {
        return typeof value === "string" && value.trim() !== ""
            ? value.trim()
            : "";
    }

    function appendSiteName(title) {
        const site = siteName.value;
        if (!title) {
            return site;
        }
        if (!site || title === site || title.includes(site)) {
            return title;
        }
        return `${title} | ${site}`;
    }

    function isDefaultPlaceholder(url) {
        return /\/default\.jpg(?:\?.*)?$/i.test(url);
    }

    const title = computed(() => {
        const explicit = asTrimmedString(resolveOption(options.title));
        if (explicit) {
            return explicit;
        }

        const pageTitle = asTrimmedString(resolveOption(options.pageTitle));
        if (pageTitle) {
            return appendSiteName(pageTitle);
        }

        if (options.useGlobalTitleTemplate) {
            const fromGlobal = pickSeoString(
                "site_meta_title",
                "main_title",
                "website_name",
            );
            if (fromGlobal) {
                return fromGlobal;
            }
            const fallback = asTrimmedString(
                resolveOption(options.fallbackPageTitle),
            );
            if (fallback) {
                return appendSiteName(fallback);
            }
        }

        return siteName.value;
    });

    const description = computed(() => {
        const explicit = asTrimmedString(resolveOption(options.description));
        if (explicit) {
            return explicit;
        }
        const keys = options.descriptionKeys ?? [
            "site_meta_description",
            "website_desc",
        ];
        return pickSeoString(...keys);
    });

    const keywords = computed(() => {
        const explicit = resolveOption(options.keywords);
        if (Array.isArray(explicit)) {
            const joined = explicit
                .filter((p) => typeof p === "string" && p.trim() !== "")
                .map((p) => p.trim())
                .join(", ");
            if (joined) {
                return joined;
            }
        } else {
            const asString = asTrimmedString(explicit);
            if (asString) {
                return asString;
            }
        }
        const keys = options.keywordsKeys ?? [
            "site_meta_keywords",
            "website_keywords",
        ];
        return pickSeoString(...keys);
    });

    const ogImage = computed(() => {
        const explicit = asTrimmedString(resolveOption(options.ogImage));
        if (explicit && !isDefaultPlaceholder(explicit)) {
            return explicit;
        }
        const fallback = media.value.meta_img;
        if (typeof fallback === "string" && fallback.trim() !== "") {
            const trimmed = fallback.trim();
            return isDefaultPlaceholder(trimmed) ? "" : trimmed;
        }
        return "";
    });

    const canonical = computed(() =>
        asTrimmedString(resolveOption(options.canonical)),
    );

    const ogType = computed(() => {
        const t = asTrimmedString(resolveOption(options.ogType));
        return t || "website";
    });

    const ogTitle = computed(() => title.value);
    const ogDescription = computed(() => description.value);
    const ogUrl = computed(() => canonical.value);
    const twitterCard = computed(() =>
        ogImage.value ? "summary_large_image" : "summary",
    );

    return {
        siteName,
        globals,
        seo: seoMap,
        media,
        title,
        description,
        keywords,
        ogImage,
        canonical,
        ogType,
        ogTitle,
        ogDescription,
        ogUrl,
        twitterCard,
        pickSeoString,
    };
}
