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
        <div
            class="imas-blog-v2 imas-property-listings imas-blog-section-anchor"
            ref="pageRef"
        >
            <InnerPageHeadingHero
                :page-title="title"
                :items="propertyHeadingItems"
                :banner-image-url="listingsBannerUrl"
            />

            <div class="imas-blog-v2__page">
                <section class="imas-blog-v2__main">
                    <PropertyListingToolbar
                        :properties="properties"
                        :filters="filters"
                        :sort="sort"
                    />

                    <div
                        v-if="(properties.data ?? []).length > 0"
                        class="imas-property-listings__grid"
                    >
                        <PropertyGridSection :properties="properties" />
                    </div>
                    <p
                        v-else
                        class="imas-blog-v2__empty text-dim"
                    >
                        {{ trans("listing_page.results_count").replace(":count", "0") }}
                    </p>

                    <PropertyListingPagination
                        :properties="properties"
                        @navigate="scrollToListingsTop"
                    />
                </section>

                <PropertyListingSidebar
                    :search-action="propertyIndexUrl"
                    :filters="filters"
                    :sort="sort"
                    :cities="cities"
                    :districts="districts"
                    :areas="areas"
                    :property-types="propertyTypes"
                    :recent-properties="recentProperties"
                    :featured-properties="featuredProperties"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import InnerPageHeadingHero from "@/components/global/InnerPageHeadingHero.vue";
import { useScrollReveal } from "@/composables/useScrollReveal";
import { useDocumentSeo } from "@/composables/useDocumentSeo.js";
import { localizedRoute } from "@/utils/localizedRoute.js";
import PropertyListingToolbar from "../components/PropertyListingToolbar.vue";
import PropertyGridSection from "../components/PropertyGridSection.vue";
import PropertyListingPagination from "../components/PropertyListingPagination.vue";
import PropertyListingSidebar from "../components/PropertyListingSidebar.vue";

const props = defineProps({
    title: { type: String, required: true },
    properties: { type: Object, required: true },
    filters: { type: Object, required: true },
    sort: { type: String, required: true },
    propertyTypes: { type: Array, default: () => [] },
    cities: { type: Array, default: () => [] },
    districts: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
    recentProperties: { type: Array, default: () => [] },
    featuredProperties: { type: Array, default: () => [] },
    seoDescription: { type: String, default: "" },
    seoOgImage: { type: String, default: "" },
});

const page = usePage();
const activeLocale = computed(() => page.props.locale || "en");
const pageRef = ref(null);

const media = computed(() => page.props.globals?.media ?? {});

function scrollToListingsTop() {
    pageRef.value?.scrollIntoView({ behavior: "smooth", block: "start" });
}

onMounted(() => {
    scrollToListingsTop();
});

const propertyIndexUrl = computed(() =>
    localizedRoute(
        "property.index",
        {},
        activeLocale.value,
        "/property",
    ),
);

const {
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
    pageTitle: () => props.title,
    description: () => props.seoDescription,
    ogImage: () => {
        if (props.seoOgImage) {
            return props.seoOgImage;
        }
        const banner = media.value.property_show_banner;
        return typeof banner === "string" ? banner : "";
    },
    canonical: () => {
        if (typeof route === "function" && route().has?.("property.index")) {
            try {
                return route("property.index");
            } catch {
                /* fall through */
            }
        }
        return propertyIndexUrl.value;
    },
});

function trans(key) {
    return page.props.translations[key] || key;
}

const propertyHeadingItems = computed(() => {
    const rows = [];
    try {
        if (typeof route === "function" && route().has?.("home")) {
            rows.push({
                title: trans("navBar.Home"),
                href: localizedRoute("home", {}, activeLocale.value, "/"),
            });
        }
    } catch {
        /* Ziggy may be unavailable */
    }
    rows.push({
        title: trans("navBar.Buy Real Estate"),
        href: null,
    });
    return rows;
});

const listingsBannerUrl = computed(() => {
    const url = media.value.property_show_banner;
    if (typeof url !== "string" || url.trim() === "") {
        return "";
    }
    const trimmed = url.trim();
    if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) {
        return "";
    }
    return trimmed;
});

useScrollReveal(pageRef, { variant: "propertyListings" });
</script>
