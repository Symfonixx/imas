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
        <div class="imas-blog-v2">
            <InnerPageHeadingHero
                :page-title="page.title"
                :items="headingItems"
                :banner-image-url="heroBannerUrl"
            />

            <div class="imas-cms-page-show__page container">
                <article class="imas-blog-show imas-cms-page-show">
                   
                    <div class="imas-blog-show__content">
                        <div
                            class="imas-blog-show-body imas-cms-page-show__body text-base text-start"
                            v-html="page.content"
                        />
                    </div>
                </article>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import InnerPageHeadingHero from "@/components/global/InnerPageHeadingHero.vue";
import { localizedRoute } from "@/utils/localizedRoute.js";

const props = defineProps({
    title: { type: String, required: true },
    page: { type: Object, required: true },
});

const inertiaPage = usePage();
const activeLocale = computed(() => inertiaPage.props.locale || "en");

function trans(key) {
    return inertiaPage.props.translations?.[key] || key;
}

const showHeroImage = computed(() => {
    const src = props.page.image;
    return (
        typeof src === "string" &&
        src.trim() !== "" &&
        !src.includes("blank.png") &&
        !/\/default\.jpg(?:\?.*)?$/i.test(src.trim())
    );
});

const heroBannerUrl = computed(() => {
    if (!showHeroImage.value) {
        return "";
    }
    return props.page.image.trim();
});

function plainText(value) {
    if (typeof value !== "string") {
        return "";
    }
    return value.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
}

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
        title: props.page.title,
        href: null,
    });
    return rows;
});

const meta = computed(() => props.page.meta ?? {});

const documentTitle = computed(
    () =>
        `${plainText(String(meta.value.title || props.title))} | ${inertiaPage.props.appName}`,
);

const metaDescription = computed(() => {
    const d = meta.value.description;
    return typeof d === "string" && d.trim() !== "" ? plainText(d) : "";
});

const metaKeywords = computed(() => {
    const k = meta.value.keywords;
    if (Array.isArray(k)) {
        const s = k.filter(Boolean).join(", ").trim();
        return s !== "" ? s : "";
    }
    if (typeof k === "string" && k.trim() !== "") {
        return k.trim();
    }
    return "";
});

const canonicalUrl = computed(() => {
    const u = meta.value.canonical_url;
    return typeof u === "string" && u.trim() !== "" ? u.trim() : "";
});

const ogTitle = computed(() => documentTitle.value);

const ogDescription = computed(() => metaDescription.value);

const ogImage = computed(() => {
    const u = meta.value.image;
    if (typeof u === "string" && u.trim() !== "") {
        return u.trim();
    }
    const fallback = props.page.image;
    return typeof fallback === "string" && fallback.trim() !== ""
        ? fallback.trim()
        : "";
});

const ogUrl = computed(() => canonicalUrl.value);

const twitterCard = computed(() =>
    ogImage.value ? "summary_large_image" : "summary",
);
</script>
