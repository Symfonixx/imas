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
                :banner-video-embed="heroYoutubeEmbed"
            />

            <main
                class="imas-about-page__page imas-blog-v2__page container"
                :class="{
                    'imas-about-page__page--with-sidebar': hasSidebar,
                }"
            >
                <section class="imas-about-page__main">
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
                        v-if="!contentHtml"
                        class="imas-about-page__empty text-muted text-base"
                    >
                        {{ trans("about_us.no_content") }}
                    </p>
                </section>

                <AboutUsQuickLinksSidebar
                    v-if="hasSidebar"
                    :featured-properties="featuredProperties"
                />
            </main>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import { useScrollReveal } from "@/composables/useScrollReveal";
import InnerPageHeadingHero from "@/components/global/InnerPageHeadingHero.vue";
import AboutUsQuickLinksSidebar from "../components/AboutUsQuickLinksSidebar.vue";

const props = defineProps({
    aboutUs: {
        type: Object,
        required: true,
    },
    featuredProperties: {
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

const heroYoutubeEmbed = computed(() => {
    const raw = props.aboutUs.youtube_embed ?? "";
    return typeof raw === "string" ? raw.trim() : "";
});

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
    const t = pickSeoString(props.aboutUs.meta_title, "about_us_meta_title");
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
    const t = pickSeoString(props.aboutUs.meta_title, "about_us_meta_title");
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
    const t = pickSeoString(props.aboutUs.meta_title, "about_us_meta_title");
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

const hasQuickLinks = computed(() => {
    try {
        return (
            (typeof route === "function" &&
                route().has?.("turkish-citizenship")) ||
            (typeof route === "function" && route().has?.("blog.index"))
        );
    } catch {
        return true;
    }
});

const hasSidebar = computed(
    () => hasQuickLinks.value || props.featuredProperties.length > 0,
);
</script>

<style scoped lang="scss">
.imas-about-page__page:not(.imas-about-page__page--with-sidebar) {
    display: block;
    max-width: 960px;
}

.imas-about-page__empty {
    text-align: center;
    padding: 2rem 0 3rem;
}
</style>
