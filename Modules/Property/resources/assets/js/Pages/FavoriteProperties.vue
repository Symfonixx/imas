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
            v-if="robots"
            head-key="robots"
            name="robots"
            :content="robots"
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
                :items="headingItems"
                :banner-image-url="listingsBannerUrl"
            />

            <main class="imas-blog-v2__page">
                <section class="imas-blog-v2__main">
                    <p
                        v-if="(properties.data ?? []).length > 0"
                        class="imas-property-listings__count text-dim"
                    >
                        {{ resultsCountLabel }}
                    </p>

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
                        {{ trans("properties.favorite_properties_empty") }}
                    </p>

                    <PropertyListingPagination
                        :properties="properties"
                        @navigate="scrollToListingsTop"
                    />
                </section>

                <aside class="imas-blog-v2-sidebar">
                    <div class="imas-favorites-aside-sticky">
                        <PropertyShowContactSidebar
                            :contact-store-url="contactStoreUrl"
                            :default-subject="inquirySubject"
                            :source-page="inquirySubject"
                        />
                    </div>
                </aside>
            </main>
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
import PropertyGridSection from "../components/PropertyGridSection.vue";
import PropertyListingPagination from "../components/PropertyListingPagination.vue";
import PropertyShowContactSidebar from "../components/PropertyShowContactSidebar.vue";

const props = defineProps({
    title: { type: String, required: true },
    properties: { type: Object, required: true },
    contactStoreUrl: { type: String, required: true },
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
    robots,
} = useDocumentSeo({
    pageTitle: () => props.title,
    robots: "noindex, nofollow",
    canonical: () => {
        try {
            if (typeof route === "function" && route().has?.("property.favorites")) {
                return route("property.favorites");
            }
        } catch {
            /* Ziggy may be unavailable */
        }
        return localizedRoute(
            "property.favorites",
            {},
            activeLocale.value,
            "/property/favorites",
        );
    },
});

const inquirySubject = computed(() => props.title);

function trans(key) {
    return page.props.translations[key] || key;
}

const resultsCountLabel = computed(() => {
    const total = props.properties?.total ?? props.properties?.data?.length ?? 0;
    return trans("properties.favorite_properties_count").replace(
        ":count",
        String(total),
    );
});

const headingItems = computed(() => {
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
        href: localizedRoute(
            "property.index",
            {},
            activeLocale.value,
            "/property",
        ),
    });
    rows.push({
        title: props.title,
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

<style scoped lang="scss">
.imas-favorites-aside-sticky {
    position: sticky;
    top: 6.5rem;
    z-index: 2;
}

@media (max-width: 991.98px) {
    .imas-favorites-aside-sticky {
        position: static;
        margin-top: 0;
    }
}
</style>
