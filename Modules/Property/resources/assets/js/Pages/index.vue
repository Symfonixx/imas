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
       <div class="inner-pages listing homepage-4 agents hd-white">
        <section
            class="properties-right featured portfolio blog pt-5 "
        >
            <div class="container">
                <section
                    class="headings-2 pt-0 pb-55 imas-property-listings-heading"
                >
                    <div class="pro-wrapper">
                        <div class="detail-wrapper-body">
                            <div class="listing-title-bar">
                                <div class="text-heading text-left">
                                    <p class="pb-2">
                                        <Link :href="route('home')">{{
                                            trans("navBar.Home")
                                        }}</Link>
                                        &nbsp;/&nbsp;
                                        <span>{{
                                            trans("navBar.Buy Real Estate")
                                        }}</span>
                                    </p>
                                </div>
                                <h3 class="text-start">{{ title }}</h3>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="row">
                    <div class="col-lg-8 col-md-12 blog-pots">
                        <PropertyListingToolbar
                            :properties="properties"
                            :filters="filters"
                            :sort="sort"
                        />
                        <PropertyGridSection :properties="properties" />
                    </div>
                    <aside class="col-lg-4 col-md-12 car">
                        <PropertyFilterSidebar
                            :filters="filters"
                            :sort="sort"
                            :cities="cities"
                            :property-types="propertyTypes"
                        />
                        <FeaturedPropertiesSidebar
                            :featured-properties="featuredProperties"
                        />
                        <RecentPropertiesSidebar
                            :recent-properties="recentProperties"
                        />
                    </aside>
                </div>
                <PropertyListingPagination :properties="properties" />
            </div>
        </section>
       </div>
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import PropertyListingToolbar from "../components/PropertyListingToolbar.vue";
import PropertyGridSection from "../components/PropertyGridSection.vue";
import PropertyListingPagination from "../components/PropertyListingPagination.vue";
import PropertyFilterSidebar from "../components/PropertyFilterSidebar.vue";
import FeaturedPropertiesSidebar from "../components/FeaturedPropertiesSidebar.vue";
import RecentPropertiesSidebar from "../components/RecentPropertiesSidebar.vue";

const props = defineProps({
    title: { type: String, required: true },
    properties: { type: Object, required: true },
    filters: { type: Object, required: true },
    sort: { type: String, required: true },
    propertyTypes: { type: Array, default: () => [] },
    cities: { type: Array, default: () => [] },
    recentProperties: { type: Array, default: () => [] },
    featuredProperties: { type: Array, default: () => [] },
});

const page = usePage();

const globals = computed(() => page.props.globals ?? {});
const seo = computed(() => globals.value.seo ?? {});
const media = computed(() => globals.value.media ?? {});

function pickSeoString(...keys) {
    const s = seo.value;
    for (const key of keys) {
        const v = s[key];
        if (typeof v === "string" && v.trim() !== "") {
            return v.trim();
        }
    }
    return "";
}

const documentTitle = computed(
    () => `${props.title} | ${page.props.appName}`,
);

const metaDescription = computed(() =>
    pickSeoString("site_meta_description", "website_desc"),
);

const metaKeywords = computed(() =>
    pickSeoString("site_meta_keywords", "website_keywords"),
);

const ogTitle = computed(() => documentTitle.value);
const ogDescription = computed(() => metaDescription.value);

const ogImage = computed(() => {
    const url = media.value.meta_img;
    return typeof url === "string" && url.trim() !== "" ? url.trim() : "";
});

const canonicalUrl = computed(() => {
    if (typeof route !== "function" || !route().has?.("property.index")) {
        return "";
    }
    try {
        return route("property.index");
    } catch {
        return "";
    }
});

const ogUrl = computed(() => canonicalUrl.value);

const twitterCard = computed(() =>
    ogImage.value ? "summary_large_image" : "summary",
);

function trans(key) {
    return page.props.translations[key] || key;
}
</script>


<style scoped lang="scss">
/* Below Bootstrap `sm` (576px): remove bottom padding; keep theme `pb-55` from sm up */
.imas-property-listings-heading {
    @media (max-width: 575.98px) {
        padding-bottom: 0 !important;
    }
}
</style>