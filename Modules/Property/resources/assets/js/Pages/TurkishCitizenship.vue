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
        <div ref="pageRef" class="inner-pages">
            <InnerPageHeadingHero
                :page-title="pageHeadingTitle"
                :items="headingItems"
                :banner-image-url="heroBannerUrl"
            />

            <!--  content section  -->
            <section class="blog blog-section bg-white pt-3 pb-5 imas-tc-page">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-md-12">
                            <TurkishCitizenshipSplitTitle
                                :primary="titlePrimary"
                                :accent="titleAccent"
                                align="start"
                                reveal
                            />
                            <div class="blog-pots imas-tc-page-content">
                                <div
                                    v-if="contentHtml"
                                    class="imas-tc-content"
                                    v-html="contentHtml"
                                />
                                <div
                                    v-if="youtubeEmbed"
                                    class="imas-tc-video ratio ratio-16x9 mb-4 mt-4 w-100"
                                    v-html="youtubeEmbed"
                                />
                                <p
                                    v-if="!contentHtml && !youtubeEmbed"
                                    class="text-muted"
                                >
                                    {{
                                        trans(
                                            "Turkish citizenship page has no published content yet.",
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                        <aside class="col-lg-4 col-md-12 car">
                            <div class="imas-tc-aside-sticky">
                                <PropertyShowContactSidebar
                                    :contact-store-url="contactStoreUrl"
                                    :default-subject="inquirySubject"
                                />
                            </div>
                        </aside>
                    </div>
                </div>
            </section>

            <PopularPropertiesSection
                v-if="citizenshipProperties.length > 0"
                :properties="citizenshipProperties"
                :hide-title="true"
                :custom-title="
                    trans('suitable_properties_for') +
                    ' ' +
                    trans('Turkish Citizenship')
                "
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
import PropertyShowContactSidebar from "../components/PropertyShowContactSidebar.vue";
import PopularPropertiesSection from "../../../../../Base/resources/assets/js/components/PopularPropertiesSection.vue";
import TurkishCitizenshipSplitTitle from "../../../../../Base/resources/assets/js/components/TurkishCitizenshipSplitTitle.vue";

const props = defineProps({
    turkishCitizenship: {
        type: Object,
        required: true,
    },
    citizenshipProperties: {
        type: Array,
        default: () => [],
    },
    contactStoreUrl: {
        type: String,
        required: true,
    },
});

const page = usePage();
const pageRef = ref(null);

useScrollReveal(pageRef, { variant: "propertyListings" });

const globals = computed(() => page.props.globals ?? {});
const seo = computed(() => globals.value.seo ?? {});
const media = computed(() => globals.value.media ?? {});
const turkishCitizenshipGlobals = computed(
    () => globals.value.turkish_citizenship ?? {},
);

const contentHtml = computed(() => props.turkishCitizenship.content ?? "");
const youtubeEmbed = computed(
    () => props.turkishCitizenship.youtube_embed ?? "",
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

const sectionLabel = computed(() => trans("navBar.Turkish Citizenship"));

function pickTranslation(key, fallback) {
    const value = trans(key);
    if (value && value !== key) {
        return value;
    }
    return fallback;
}

const titlePrimary = computed(() =>
    pickTranslation(
        "turkishCitizenship.overview_title_primary",
        "Turkish Citizenship",
    ),
);

const titleAccent = computed(() =>
    pickTranslation(
        "turkishCitizenship.overview_title_accent",
        "by Investment Programme",
    ),
);

const pageHeadingTitle = computed(() => {
    const t = pickSeoString(
        props.turkishCitizenship.meta_title,
        "turkish_citizenship_meta_title",
    );
    return t !== "" ? t : sectionLabel.value;
});

const inquirySubject = computed(() => pageHeadingTitle.value);

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
    const url = bannerUrl.value;
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
        props.turkishCitizenship.meta_title,
        "turkish_citizenship_meta_title",
    );
    if (t !== "") {
        return `${t} | ${page.props.appName}`;
    }
    return `${sectionLabel.value} | ${page.props.appName}`;
});

const metaDescription = computed(() =>
    pickSeoString(
        props.turkishCitizenship.meta_description,
        "turkish_citizenship_meta_description",
        "site_meta_description",
        "website_desc",
    ),
);

const metaKeywords = computed(() =>
    pickSeoString(
        props.turkishCitizenship.meta_keywords,
        "turkish_citizenship_meta_keywords",
        "site_meta_keywords",
        "website_keywords",
    ),
);

const ogTitle = computed(() => {
    const t = pickSeoString(
        props.turkishCitizenship.meta_title,
        "turkish_citizenship_meta_title",
    );
    return t !== "" ? t : sectionLabel.value;
});

const ogDescription = computed(() => metaDescription.value);

const ogImage = computed(() => {
    const banner =
        props.turkishCitizenship.banner_url ||
        turkishCitizenshipGlobals.value.banner_url ||
        media.value.turkish_citizenship_banner;
    if (typeof banner === "string" && banner.trim() !== "") {
        return banner.trim();
    }
    const fallback = media.value.meta_img;
    return typeof fallback === "string" && fallback.trim() !== ""
        ? fallback.trim()
        : "";
});

const canonicalUrl = computed(() => {
    if (typeof route !== "function" || !route().has?.("turkish-citizenship")) {
        return "";
    }
    try {
        return route("turkish-citizenship");
    } catch {
        return "";
    }
});

const ogUrl = computed(() => canonicalUrl.value);

const twitterCard = computed(() =>
    ogImage.value ? "summary_large_image" : "summary",
);

const bannerUrl = computed(() => {
    const url =
        props.turkishCitizenship.banner_url ||
        turkishCitizenshipGlobals.value.banner_url ||
        media.value.turkish_citizenship_banner;
    if (typeof url !== "string" || url.trim() === "") {
        return "";
    }
    return url.trim();
});

function trans(key) {
    return page.props.translations[key] || key;
}
</script>

<style scoped lang="scss">
.imas-tc-page .row {
    align-items: flex-start;
}

.imas-tc-aside-sticky {
    position: sticky;
    top: 6.5rem;
    z-index: 2;
}

@media (max-width: 991.98px) {
    .imas-tc-aside-sticky {
        position: static;
        margin-top: 2rem;
    }
}
.imas-tc-content{
    text-align: start;
}
.imas-tc-content :deep(img) {
    max-width: 100%;
    height: auto;
}

/* YouTube / embed: full width inside ratio box */
.imas-tc-video {
    width: 100%;
    max-width: 100%;
}

.imas-tc-video :deep(> *) {
    /* position: absolute; */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    margin: 0 !important;
}

.imas-tc-video :deep(iframe),
.imas-tc-video :deep(embed) {
    /* position: absolute !important; */
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 400px !important;
    max-width: 100% !important;
    border: 0 !important;
}

@media (max-width: 768px) {
    .imas-tc-video :deep(iframe),
    .imas-tc-video :deep(embed) {
        height: 200px !important;
    }
}
</style>
