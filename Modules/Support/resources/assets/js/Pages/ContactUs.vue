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
        <div ref="pageRef" class="inner-pages imas-contact-page">
            <InnerPageHeadingHero
                :page-title="trans('contact_us.title')"
                :items="blogHeadingItems"
                :banner-image-url="contactUsBannerUrl"
            />
            <section class="contact-us imas-contact-page__section">
                <div class="container">
                    <div class="row g-4">
                        <div class="col-lg-8 col-md-12">
                            <div class="imas-contact-page__panel imas-contact-page__panel--form">
                                <ContactForm
                                    :contact-store-url="contactStoreUrl"
                                    :source-page="trans('contact_us.title')"
                                />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <div
                                class="imas-contact-page__panel imas-contact-page__panel--details"
                            >
                                <ContactDetails />
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <ContactFaq />
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import InnerPageHeadingHero from "@/components/global/InnerPageHeadingHero.vue";
import { useScrollReveal } from "@/composables/useScrollReveal";
import ContactForm from "../Components/ContactForm.vue";
import ContactDetails from "../Components/ContactDetails.vue";
import ContactFaq from "../Components/ContactFaq.vue";

import { localizedRoute } from "@/utils/localizedRoute.js";

defineProps({
    contactStoreUrl: {
        type: String,
        required: true,
    },
});

const page = usePage();
const activeLocale = computed(() => page.props.locale || "en");
const pageRef = ref(null);

useScrollReveal(pageRef, { variant: "propertyListings" });

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

const contactUsBannerUrl = computed(() => {
    const url = media.value.contact_us_banner;
    if (typeof url !== "string" || url.trim() === "") {
        return "";
    }
    const trimmed = url.trim();
    if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) {
        return "";
    }
    return trimmed;
});

function trans(key) {
    return page.props.translations[key] || key;
}

const documentTitle = computed(
    () => `${trans("contact_us.title")} | ${page.props.appName}`,
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
    const banner = media.value.contact_us_banner;
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

const canonicalUrl = computed(() =>
    localizedRoute(
        "support.contact-us",
        {},
        activeLocale.value,
        "/contact-us",
    ),
);

const ogUrl = computed(() => canonicalUrl.value);

const twitterCard = computed(() =>
    ogImage.value ? "summary_large_image" : "summary",
);

const blogHeadingItems = computed(() => {
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
        title: trans("navBar.Contact us"),
        href: null,
    });
    return rows;
});
</script>
