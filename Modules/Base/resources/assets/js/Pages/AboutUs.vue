<template>
    <Head :title="documentTitle">
        <meta
            v-if="metaDescription"
            head-key="description"
            name="description"
            :content="metaDescription"
        />
        <meta
            v-if="metaKeywords"
            head-key="keywords"
            name="keywords"
            :content="metaKeywords"
        />
        <link
            v-if="canonicalUrl"
            head-key="canonical"
            rel="canonical"
            :href="canonicalUrl"
        />
        <meta
            v-if="ogTitle"
            head-key="og:title"
            property="og:title"
            :content="ogTitle"
        />
        <meta
            v-if="ogDescription"
            head-key="og:description"
            property="og:description"
            :content="ogDescription"
        />
        <meta
            v-if="ogImage"
            head-key="og:image"
            property="og:image"
            :content="ogImage"
        />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta
            v-if="ogUrl"
            head-key="og:url"
            property="og:url"
            :content="ogUrl"
        />
        <meta
            head-key="twitter:card"
            name="twitter:card"
            :content="twitterCard"
        />
        <meta
            v-if="ogTitle"
            head-key="twitter:title"
            name="twitter:title"
            :content="ogTitle"
        />
        <meta
            v-if="ogDescription"
            head-key="twitter:description"
            name="twitter:description"
            :content="ogDescription"
        />
        <meta
            v-if="ogImage"
            head-key="twitter:image"
            name="twitter:image"
            :content="ogImage"
        />
    </Head>

    <AppLayout>
        <div ref="pageRef" class="inner-pages imas-about-page">
            <InnerPageHeadingHero
                :page-title="pageHeadingTitle"
                :items="headingItems"
                :banner-image-url="heroBannerUrl"
            />

            <main class="imas-cms-page-show__page container">
                <div
                    v-if="youtubeEmbedHtml"
                    class="imas-about-page__video imas-tc-video ratio ratio-16x9"
                    v-html="youtubeEmbedHtml"
                />
                <article
                    v-if="contentHtml"
                    class="imas-blog-show imas-cms-page-show"
                >
                    <div class="imas-blog-show__content">
                        <div
                            class="imas-blog-show-body imas-cms-page-show__body text-base text-start"
                            v-html="contentHtml"
                        />
                    </div>
                </article>
                <p
                    v-if="!contentHtml && !youtubeEmbedHtml"
                    class="imas-about-page__empty text-muted text-base"
                >
                    {{ trans("about_us.no_content") }}
                </p>
            </main>

            <PopularPropertiesSection
                v-if="latestProperties.length > 0"
                :properties="latestProperties"
                :hide-title="true"
                :custom-title="trans('about_us.latest_properties')"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import { useScrollReveal } from "@/composables/useScrollReveal";
import InnerPageHeadingHero from "@/components/global/InnerPageHeadingHero.vue";
import PopularPropertiesSection from "../components/PopularPropertiesSection.vue";

const props = defineProps({
    aboutUs: {
        type: Object,
        required: true,
    },
    latestProperties: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const pageRef = ref(null);

useScrollReveal(pageRef, { variant: "propertyListings" });

const globals = computed(() => page.props.globals ?? {});
const seo = computed(() => globals.value.seo ?? {});
const media = computed(() => globals.value.media ?? {});
const contentHtml = computed(() => props.aboutUs.content ?? "");

const youtubeEmbedHtml = computed(() =>
    withYoutubeAutoplay(props.aboutUs.youtube_embed ?? ""),
);

function pickSeoString(fromProps, ...globalKeys) {
    const p = fromProps;
    if (typeof p === "string" && p.trim() !== "") {
        return p.trim();
    }
    const s = seo.value;
    for (const key of globalKeys) {
        const v = s[key];
        if (typeof v === "string" && v.trim() !== "") {
            return v.trim();
        }
    }
    return "";
}

function trans(key) {
    return page.props.translations[key] || key;
}

const sectionLabel = computed(() => trans("about_us.title"));

const pageHeadingTitle = computed(() => {
    const t = pickSeoString(
        props.aboutUs.meta_title,
        "about_us_meta_title",
    );
    return t !== "" ? t : sectionLabel.value;
});

const headingItems = computed(() => {
    const rows = [];
    try {
        if (typeof route === "function" && route().has?.("home")) {
            rows.push({
                title: trans("navBar.Home"),
                href: route("home"),
            });
        }
    } catch {
        /* Ziggy may be unavailable */
    }
    rows.push({
        title: sectionLabel.value,
        href: null,
    });
    return rows;
});

const heroBannerUrl = computed(() => {
    const url = media.value.about_us_banner;
    if (typeof url !== "string" || url.trim() === "") {
        return "";
    }
    const trimmed = url.trim();
    if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) {
        return "";
    }
    return trimmed;
});

const documentTitle = computed(() => {
    const t = pickSeoString(
        props.aboutUs.meta_title,
        "about_us_meta_title",
    );
    if (t !== "") {
        return `${t} | ${page.props.appName}`;
    }
    return `${sectionLabel.value} | ${page.props.appName}`;
});

const metaDescription = computed(() =>
    pickSeoString(
        props.aboutUs.meta_description,
        "about_us_meta_description",
        "site_meta_description",
        "website_desc",
    ),
);

const metaKeywords = computed(() =>
    pickSeoString(
        props.aboutUs.meta_keywords,
        "about_us_meta_keywords",
        "site_meta_keywords",
        "website_keywords",
    ),
);

const ogTitle = computed(() => {
    const t = pickSeoString(
        props.aboutUs.meta_title,
        "about_us_meta_title",
    );
    return t !== "" ? t : sectionLabel.value;
});

const ogDescription = computed(() => metaDescription.value);

const ogImage = computed(() => {
    const banner = media.value.about_us_banner;
    if (typeof banner === "string" && banner.trim() !== "") {
        const trimmed = banner.trim();
        if (!/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) {
            return trimmed;
        }
    }
    const fallback = media.value.meta_img;
    return typeof fallback === "string" && fallback.trim() !== ""
        ? fallback.trim()
        : "";
});

const canonicalUrl = computed(() => {
    if (typeof route !== "function" || !route().has?.("about-us")) {
        return "";
    }
    try {
        return route("about-us");
    } catch {
        return "";
    }
});

const ogUrl = computed(() => canonicalUrl.value);

const twitterCard = computed(() =>
    ogImage.value ? "summary_large_image" : "summary",
);

/**
 * Ensure admin YouTube iframe autoplays on load (muted per browser policy).
 */
function withYoutubeAutoplay(html) {
    const raw = String(html || "").trim();
    if (!raw) {
        return "";
    }

    if (typeof DOMParser !== "undefined") {
        try {
            const doc = new DOMParser().parseFromString(raw, "text/html");
            const iframe = doc.querySelector("iframe");
            if (iframe) {
                const src = iframe.getAttribute("src") || "";
                if (src) {
                    iframe.setAttribute("src", appendYoutubeAutoplayParams(src));
                }
                const allow = iframe.getAttribute("allow") || "";
                if (!/autoplay/i.test(allow)) {
                    iframe.setAttribute(
                        "allow",
                        allow
                            ? `${allow}; autoplay`
                            : "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share",
                    );
                }
                iframe.setAttribute("allowfullscreen", "");
                return iframe.outerHTML;
            }
        } catch {
            /* regex fallback */
        }
    }

    return raw.replace(
        /(<iframe[^>]*\ssrc=["'])([^"']+)(["'])/i,
        (_, pre, src, post) => `${pre}${appendYoutubeAutoplayParams(src)}${post}`,
    );
}

function appendYoutubeAutoplayParams(src) {
    try {
        const url = new URL(src, "https://www.youtube.com");
        url.searchParams.set("autoplay", "1");
        url.searchParams.set("mute", "1");
        url.searchParams.set("playsinline", "1");
        return url.toString();
    } catch {
        const sep = src.includes("?") ? "&" : "?";
        return `${src}${sep}autoplay=1&mute=1&playsinline=1`;
    }
}
</script>

<style scoped lang="scss">
.imas-about-page__video {
    width: 100%;
    max-width: 100%;
    margin-bottom: 2rem;
}

.imas-about-page__video :deep(iframe),
.imas-about-page__video :deep(embed) {
    width: 100% !important;
    height: 100% !important;
    min-height: 400px;
    border: 0 !important;
}

.imas-about-page__video + .imas-about-page__empty,
.imas-about-page__video + .imas-blog-show {
    margin-top: 0;
}

.imas-about-page__empty {
    text-align: center;
    padding: 2rem 0 3rem;
}

@media (max-width: 768px) {
    .imas-about-page__video :deep(iframe),
    .imas-about-page__video :deep(embed) {
        min-height: 200px;
    }
}
</style>
