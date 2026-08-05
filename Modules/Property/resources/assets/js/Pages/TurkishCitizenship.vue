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
        <div ref="pageRef" class="inner-pages imas-tc-page-root">
            <InnerPageHeadingHero
                :page-title="pageHeadingTitle"
                :items="headingItems"
                :banner-image-url="heroBannerUrl"
            />

            <!--  content section  -->
            <section class="blog blog-section bg-white pt-3 pb-5 imas-tc-page">
                <div class="container">
                    <div ref="tcContentRowRef" class="row imas-tc-page__content-row">
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
                        <aside
                            ref="tcSidebarColRef"
                            class="col-lg-4 col-md-12 car imas-tc-page__sidebar-col"
                        >
                            <div
                                ref="tcSidebarStickyRef"
                                class="imas-tc-page__contact-sticky"
                            >
                                <PropertyShowContactSidebar
                                    :contact-store-url="contactStoreUrl"
                                    :default-subject="inquirySubject"
                                    :source-page="inquirySubject"
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
                :custom-title="trans('suitable_properties_for_turkish_citizenship_by_citizenship_program')"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import { useScrollReveal } from "@/composables/useScrollReveal";
import { useBoundedSticky } from "@/composables/useBoundedSticky";
import { useDocumentSeo } from "@/composables/useDocumentSeo.js";
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
const tcContentRowRef = ref(null);
const tcSidebarColRef = ref(null);
const tcSidebarStickyRef = ref(null);

useScrollReveal(pageRef, { variant: "propertyListings" });

useBoundedSticky({
    boundaryRef: tcContentRowRef,
    columnRef: tcSidebarColRef,
    targetRef: tcSidebarStickyRef,
});

const globals = computed(() => page.props.globals ?? {});
const media = computed(() => globals.value.media ?? {});
const turkishCitizenshipGlobals = computed(
    () => globals.value.turkish_citizenship ?? {},
);

const contentHtml = computed(() => props.turkishCitizenship.content ?? "");
const youtubeEmbed = computed(
    () => props.turkishCitizenship.youtube_embed ?? "",
);

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

const {
    pickSeoString,
    title: documentTitle,
    description: metaDescription,
    keywords: metaKeywords,
    ogTitle,
    ogDescription,
    ogImage,
    canonical: canonicalUrl,
    ogUrl,
    twitterCard,
} = useDocumentSeo({
    pageTitle: () => {
        const t = props.turkishCitizenship.meta_title;
        if (typeof t === "string" && t.trim() !== "") {
            return t.trim();
        }
        const fromGlobal = pickSeoString("turkish_citizenship_meta_title");
        return fromGlobal !== "" ? fromGlobal : sectionLabel.value;
    },
    description: () => {
        const d = props.turkishCitizenship.meta_description;
        if (typeof d === "string" && d.trim() !== "") {
            return d.trim();
        }
        return pickSeoString(
            "turkish_citizenship_meta_description",
            "site_meta_description",
            "website_desc",
        );
    },
    keywords: () => {
        const k = props.turkishCitizenship.meta_keywords;
        if (typeof k === "string" && k.trim() !== "") {
            return k.trim();
        }
        return pickSeoString(
            "turkish_citizenship_meta_keywords",
            "site_meta_keywords",
            "website_keywords",
        );
    },
    ogImage: () => {
        const banner =
            props.turkishCitizenship.banner_url ||
            turkishCitizenshipGlobals.value.banner_url ||
            media.value.turkish_citizenship_banner;
        if (typeof banner === "string" && banner.trim() !== "") {
            const trimmed = banner.trim();
            if (!/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) {
                return trimmed;
            }
        }
        return "";
    },
    canonical: () => {
        if (
            typeof route !== "function" ||
            !route().has?.("turkish-citizenship")
        ) {
            return "";
        }
        try {
            return route("turkish-citizenship");
        } catch {
            return "";
        }
    },
});

const pageHeadingTitle = computed(() => {
    const t = props.turkishCitizenship.meta_title;
    if (typeof t === "string" && t.trim() !== "") {
        return t.trim();
    }
    const fromGlobal = pickSeoString("turkish_citizenship_meta_title");
    return fromGlobal !== "" ? fromGlobal : sectionLabel.value;
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
.imas-tc-content {
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
