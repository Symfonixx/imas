<template>
    <Head :title="documentTitle">
        <meta
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
        <JsonLdScript
            head-key="jsonld-organization"
            :content="organizationJsonLd"
        />
        <JsonLdScript head-key="jsonld-website" :content="websiteJsonLd" />
    </Head>

    <AppLayout>
        <HomeHero
            :welcome-title="welcomeTitle"
            :welcome-subtitle="welcomeSubtitle"
            :slides="slides"
            :property-types="propertyTypes"
            :cities="cities"
            :districts="districts"
            :areas="areas"
        />

        <FeaturedPropertiesSection
            :properties="featuredProperties"
            :title="trans('properties.featured_properties')"
            :subtitle="
                trans('properties.we_provide_full_service_at_every_step')
            "
        />

        <TurkishCitizenshipOverview />

        <HomeAboutus />

        <HomeTestimonials :testimonials="testimonials" />
        <HomeArticlesSection :articles="articles" />
        <HomeServices :services="corporateServices" />
        <PopularPropertiesSection
            :properties="recommendedProperties"
                :title="trans('properties.title')"
            :subtitle="
                trans('properties.we_provide_full_service_at_every_step')
            "
        />
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import JsonLdScript from "@/components/Seo/JsonLdScript.vue";
import FeaturedPropertiesSection from "../components/FeaturedPropertiesSection.vue";
import TurkishCitizenshipOverview from "../components/TurkishCitizenshipOverview.vue";
import HomeAboutus from "../components/HomeAboutus.vue";
import PopularPropertiesSection from "../components/PopularPropertiesSection.vue";
import HomeServices from "../components/HomeServices.vue";
import HomeTestimonials from "../components/HomeTestimonials.vue";
import HomeArticlesSection from "../components/HomeArticlesSection.vue";
import HomeHero from "../components/HomeHero.vue";
import { useDocumentSeo } from "@/composables/useDocumentSeo.js";
import {
    buildOrganizationSchema,
    buildWebsiteSchema,
    collectSocialUrls,
} from "@/utils/structuredData.js";

const page = usePage();

const {
    globals,
    media,
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
    useGlobalTitleTemplate: true,
    fallbackPageTitle: "Home",
    canonical: () => {
        if (typeof route !== "function" || !route().has?.("home")) {
            return "";
        }
        try {
            return route("home");
        } catch {
            return "";
        }
    },
});

const organizationSchema = computed(() => {
    const contact = globals.value.contact ?? {};
    const logo =
        media.value.white_logo ||
        media.value.black_logo ||
        media.value.meta_img ||
        "";

    return buildOrganizationSchema({
        name: page.props.appName,
        url: canonicalUrl.value,
        description: metaDescription.value,
        logo,
        email: contact.email,
        phone: contact.phone,
        address: contact.address,
        sameAs: collectSocialUrls(globals.value.social),
    });
});

const organizationJsonLd = computed(() => {
    const schema = organizationSchema.value;
    return schema ? JSON.stringify(schema) : "";
});

const propertySearchUrl = computed(() => {
    if (typeof route !== "function" || !route().has?.("property.index")) {
        return "";
    }
    try {
        const base = route("property.index");
        const sep = base.includes("?") ? "&" : "?";
        return `${base}${sep}q={search_term_string}`;
    } catch {
        return "";
    }
});

const websiteSchema = computed(() =>
    buildWebsiteSchema({
        name: page.props.appName,
        url: canonicalUrl.value,
        description: metaDescription.value,
        searchUrlTemplate: propertySearchUrl.value,
    }),
);

const websiteJsonLd = computed(() => {
    const schema = websiteSchema.value;
    return schema ? JSON.stringify(schema) : "";
});

function trans(key) {
    return page.props.translations[key] || key;
}

defineProps({
    welcomeTitle: { type: String, required: true },
    welcomeSubtitle: { type: String, required: true },
    slides: { type: Array, default: () => [] },
    propertyTypes: { type: Array, default: () => [] },
    cities: { type: Array, default: () => [] },
    districts: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
    featuredProperties: { type: Array, default: () => [] },
    recommendedProperties: { type: Array, default: () => [] },
    corporateServices: { type: Array, default: () => [] },
    testimonials: { type: Array, default: () => [] },
    articles: { type: Array, default: () => [] },
});
</script>
