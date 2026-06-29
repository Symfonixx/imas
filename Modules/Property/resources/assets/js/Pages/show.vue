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
            ref="pageRef"
            class="inner-pages blog imas-property-show-page imas-blog-v2 imas-property-listings"
        >
            <InnerPageHeadingHero
                :page-title="trans('properties.proprty_details')"
                :items="propertyHeadingItems"
                :banner-image-url="propertyShowBannerUrl"
            />

            <section class="single-proper blog details imas-property-show">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-md-12 blog-pots">
                            <div class="row">
                                <div class="col-md-12">
                                    <section
                                        data-imas-reveal
                                        class="headings-2 pt-0"
                                    >
                                        <div
                                            class="pro-wrapper imas-property-title-row"
                                        >
                                            <div class="detail-wrapper-body">
                                                <div
                                                    class="listing-title-bar text-start"
                                                >
                                                    <h3>{{ displayTitle }}</h3>
                                                    <div
                                                        v-if="addressLine"
                                                        class="mt-0"
                                                    >
                                                        <a
                                                            v-if="hasMapCoordinates"
                                                            href="#listing-location"
                                                            class="listing-address"
                                                        >
                                                            <i
                                                                class="fa fa-map-marker imas-address-marker"
                                                                aria-hidden="true"
                                                            ></i>
                                                            <span>{{
                                                                addressLine
                                                            }}</span>
                                                        </a>
                                                        <span
                                                            v-else
                                                            class="listing-address"
                                                        >
                                                            <i
                                                                class="fa fa-map-marker imas-address-marker"
                                                                aria-hidden="true"
                                                            ></i>
                                                            <span>{{
                                                                addressLine
                                                            }}</span>
                                                        </span>
                                                    </div>
                                                    <div
                                                        v-if="propertyTypeLabel"
                                                        class="imas-property-type-badge mt-2"
                                                    >
                                                        {{ propertyTypeLabel }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="single detail-wrapper ms-lg-auto"
                                            >
                                                <div
                                                    class="detail-wrapper-body"
                                                >
                                                    <div
                                                        class="listing-title-bar text-start text-lg-end"
                                                    >
                                                        <h4
                                                            class="imas-price-heading"
                                                        >
                                                            <template
                                                                v-if="
                                                                    priceAmount
                                                                "
                                                            >
                                                                <span
                                                                    class="imas-price-heading__prefix"
                                                                    >{{
                                                                        pricePrefix
                                                                    }}</span
                                                                >
                                                                <span
                                                                    class="imas-price-heading__amount text-gold"
                                                                >
                                                                    {{
                                                                        priceAmount
                                                                    }}
                                                                </span>
                                                            </template>
                                                            <span
                                                                v-else
                                                                class="imas-price-heading__amount text-gold"
                                                                >—</span
                                                            >
                                                        </h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <div data-imas-reveal>
                                        <PropertyShowGallery
                                            :property-id="property.id"
                                            :slides="property.slides"
                                            :thumbnail-url="
                                                property.thumbnail_url
                                            "
                                            :alt="displayTitle"
                                            :title="
                                                trans('property_show.gallery')
                                            "
                                        />
                                    </div>

                                    <div
                                        v-if="overviewHtml"
                                        data-imas-reveal
                                        class="blog-info details mb-30 text-start imas-property-show-panel"
                                    >
                                        <h5 class="imas-section-title mb-4">
                                            {{
                                                trans(
                                                    "property_show.description",
                                                )
                                            }}
                                        </h5>
                                        <div
                                            class="imas-rich-content text-md"
                                            v-html="overviewHtml"
                                        />
                                    </div>

                                    <div
                                        v-if="property.unit_types?.length"
                                        data-imas-reveal
                                    >
                                        <PropertyShowUnitTypesTable
                                            :unit-types="property.unit_types"
                                            :title="
                                                trans(
                                                    'property_show.unit_types_title',
                                                )
                                            "
                                            :col-rooms="
                                                trans('property_show.col_rooms')
                                            "
                                            :col-area="
                                                trans('property_show.col_area')
                                            "
                                            :col-price="
                                                trans('property_show.col_price')
                                            "
                                        />
                                    </div>

                                    <div
                                        v-if="facilitiesHtml"
                                        data-imas-reveal
                                        class="blog-info details mb-30 text-start imas-property-show-panel"
                                    >
                                        <h5 class="imas-section-title mb-4">
                                            {{
                                                trans(
                                                    "property_show.facilities",
                                                )
                                            }}
                                        </h5>
                                        <div
                                            class="imas-rich-content text-md"
                                            v-html="facilitiesHtml"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div
                                v-if="contentHtml"
                                data-imas-reveal
                                class="blog-info details mb-30 text-start imas-property-show-panel"
                            >
                                <h5 class="imas-section-title mb-4">
                                    {{ trans("property_show.details") }}
                                </h5>
                                <div
                                    class="imas-rich-content text-md"
                                    v-html="contentHtml"
                                />
                            </div>

                            <div
                                v-if="property.youtube_video_url"
                                data-imas-reveal
                            >
                                <PropertyShowVideo
                                    :video-url="property.youtube_video_url"
                                    :poster-url="property.thumbnail_url"
                                    :poster-alt="displayTitle"
                                    :title="
                                        trans('property_show.property_video')
                                    "
                                />
                            </div>
                            <div
                                v-if="whyToBuyHtml"
                                data-imas-reveal
                                class="blog-info details mb-30 text-start imas-property-show-panel"
                            >
                                <h5 class="imas-section-title mb-4">
                                    {{ trans("property_show.why_to_buy") }}
                                </h5>
                                <div
                                    class="imas-rich-content text-md"
                                    v-html="whyToBuyHtml"
                                />
                            </div>

                            <div
                                v-if="hasMapCoordinates"
                                id="listing-location"
                                data-imas-reveal
                            >
                                <PropertyShowMap
                                    :lat="property.lat"
                                    :lng="property.lng"
                                    :title="trans('property_show.location')"
                                />
                            </div>
                        </div>

                        <aside class="col-lg-4 col-md-12 car imas-blog-v2-sidebar">
                            <div data-imas-reveal="aside">
                                <PropertyShowContactSidebar
                                    :contact-store-url="contactStoreUrl"
                                    :default-subject="canonicalUrl"
                                    hide-form-subject
                                />
                            </div>
                            <div data-imas-reveal="aside">
                                <RecentPropertiesSidebar
                                    :recent-properties="recentProperties"
                                />
                            </div>
                            <div data-imas-reveal="aside">
                                <FeaturedPropertiesSidebar
                                    :featured-properties="featuredProperties"
                                />
                            </div>
                        </aside>
                    </div>
                </div>
            </section>

            <PopularPropertiesSection
                v-if="similarProperties.length > 0"
                :properties="similarProperties"
                :hide-title="true"
                :custom-title="trans('property_show.similar_properties')"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import InnerPageHeadingHero from "@/components/global/InnerPageHeadingHero.vue";
import { useScrollReveal } from "@/composables/useScrollReveal";
import { localizedRoute } from "@/utils/localizedRoute.js";
import PopularPropertiesSection from "../../../../../Base/resources/assets/js/components/PopularPropertiesSection.vue";
import PropertyShowGallery from "../components/PropertyShowGallery.vue";
import PropertyShowVideo from "../components/PropertyShowVideo.vue";
import PropertyShowUnitTypesTable from "../components/PropertyShowUnitTypesTable.vue";
import PropertyShowMap from "../components/PropertyShowMap.vue";
import PropertyShowContactSidebar from "../components/PropertyShowContactSidebar.vue";
import FeaturedPropertiesSidebar from "../components/FeaturedPropertiesSidebar.vue";
import RecentPropertiesSidebar from "../components/RecentPropertiesSidebar.vue";
import { localizedField } from "../utils/propertyLocalized.js";
import { propertyLocationLine } from "../utils/propertyLocation.js";
import { propertyStartPrice } from "../utils/propertyPrice.js";

const props = defineProps({
    property: { type: Object, required: true },
    recentProperties: { type: Array, default: () => [] },
    featuredProperties: { type: Array, default: () => [] },
    similarProperties: { type: Array, default: () => [] },
    contactStoreUrl: { type: String, required: true },
});

const page = usePage();
const locale = computed(() => page.props.locale || "en");
const activeLocale = locale;

const globals = computed(() => page.props.globals ?? {});
const media = computed(() => globals.value.media ?? {});

function trans(key) {
    return page.props.translations[key] || key;
}

const displayTitle = computed(() => {
    const fromTitle = localizedField(props.property.title, locale.value);
    if (fromTitle) {
        return fromTitle;
    }
    const fromProject = localizedField(
        props.property.project_name,
        locale.value,
    );
    if (fromProject) {
        return fromProject;
    }
    return props.property.project_code || "Property";
});

const propertyHeadingItems = computed(() => {
    const rows = [];
    try {
        if (typeof route === "function" && route().has?.("home")) {
            rows.push({
                title: trans("navBar.Home"),
                href: localizedRoute("home", {}, activeLocale.value, "/"),
            });
        }
        if (typeof route === "function" && route().has?.("property.index")) {
            rows.push({
                title: trans("navBar.Buy Real Estate"),
                href: localizedRoute(
                    "property.index",
                    {},
                    activeLocale.value,
                    "/property",
                ),
            });
        }
    } catch {
        /* Ziggy may be unavailable */
    }
    rows.push({
        title: displayTitle.value,
        href: null,
    });
    return rows;
});

const propertyShowBannerUrl = computed(() => {
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

const addressLine = computed(() =>
    propertyLocationLine(props.property.location, locale.value),
);

const propertyTypeLabel = computed(() => {
    const type = props.property.property_type;
    if (!type) {
        return "";
    }
    return localizedField(type.name, locale.value);
});

function formatMoney(amount) {
    const n = Number(amount);
    if (!Number.isFinite(n)) {
        return "—";
    }
    return new Intl.NumberFormat(locale.value, {
        style: "currency",
        currency: "USD",
        maximumFractionDigits: 0,
    }).format(n);
}

const pricePrefix = computed(() => trans("properties.start_price"));

const priceAmount = computed(() => {
    const start = propertyStartPrice(props.property);
    if (start == null) {
        return null;
    }
    return formatMoney(start);
});

const overviewHtml = computed(() =>
    localizedField(props.property.overview, locale.value),
);
const contentHtml = computed(() =>
    localizedField(props.property.content, locale.value),
);
const whyToBuyHtml = computed(() =>
    localizedField(props.property.why_to_buy, locale.value),
);
const facilitiesHtml = computed(() =>
    localizedField(props.property.facilities, locale.value),
);

function hasValidCoordinate(value) {
    if (value === null || value === undefined || value === "") {
        return false;
    }
    return Number.isFinite(Number(value));
}

const hasMapCoordinates = computed(
    () =>
        hasValidCoordinate(props.property.lat) &&
        hasValidCoordinate(props.property.lng),
);

const meta = computed(() => props.property.metadata ?? {});

const documentTitle = computed(() => {
    const custom = meta.value.meta_title;
    const title =
        typeof custom === "string" && custom.trim() !== ""
            ? custom.trim()
            : displayTitle.value;
    return `${title} | ${page.props.appName}`;
});

const metaDescription = computed(() => {
    const d = meta.value.meta_description;
    if (typeof d === "string" && d.trim() !== "") {
        return d.trim();
    }
    const plain = overviewHtml.value.replace(/<[^>]*>/g, " ").trim();
    return plain.slice(0, 160);
});

const metaKeywords = computed(() => {
    const k = meta.value.meta_keywords;
    if (Array.isArray(k) && k.length > 0) {
        return k.join(", ");
    }
    if (typeof k === "string" && k.trim() !== "") {
        return k.trim();
    }
    return "";
});

const ogTitle = computed(() => documentTitle.value);
const ogDescription = computed(() => metaDescription.value);
const ogImage = computed(() => props.property.thumbnail_url || "");
const canonicalUrl = computed(() => {
    try {
        if (typeof route === "function" && route().has?.("property.show")) {
            const slug =
                props.property.project_code || props.property.slug;
            if (slug) {
                return route("property.show", slug);
            }
        }
    } catch {
        /* ignore */
    }
    return "";
});
const ogUrl = computed(() => canonicalUrl.value);
const twitterCard = computed(() =>
    ogImage.value ? "summary_large_image" : "summary",
);

const pageRef = ref(null);

useScrollReveal(pageRef, { variant: "propertyListings" });
</script>

<style scoped lang="scss">
.imas-property-show {
    text-align: start;
}

.imas-section-title {
    position: relative;
    display: inline-block;
    padding-bottom: 0.35rem;
    margin-bottom: 1rem;
    font-size: var(--text-md);
    font-weight: 700;
    color: var(--text);
}

.imas-section-title::after {
    content: "";
    position: absolute;
    inset-inline-start: 0;
    bottom: 0;
    width: 3.5rem;
    height: 2px;
    background: var(--brand-gold);
    border-radius: 2px;
}

.imas-rich-content {
    color: var(--text);
    line-height: var(--line-height-base);
}

.imas-rich-content :deep(p),
.imas-rich-content :deep(li),
.imas-rich-content :deep(blockquote) {
    color: var(--text);
}

.imas-rich-content :deep(p:last-child) {
    margin-bottom: 0;
}

.imas-rich-content :deep(a) {
    color: var(--text);
    text-decoration: underline;
    text-underline-offset: 2px;
}

.imas-rich-content :deep(a:hover) {
    color: var(--brand-gold);
}

.imas-price-heading {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.2rem;
    color: var(--text);
    font-weight: 600;
    font-size: var(--text-xl);
    line-height: 1.3;
}

.listing-title-bar.text-lg-end .imas-price-heading {
    text-transform: capitalize !important;
    align-items: flex-end;
    text-align: end;
}

.imas-price-heading__prefix {
    display: block;
    font-size: var(--text-md);
    font-weight: 600;
    color: var(--text);
    text-transform: none;
}

.imas-price-heading__amount {
    display: block;
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--brand-gold);
    line-height: 1.3;
}

.imas-for-sale-pill {
    background: var(--brand-gold);
    color: var(--text-on-gold);
}

.imas-sold-out-pill {
    background: var(--danger);
    color: var(--text);
}

.imas-property-type-badge {
    display: inline-block;
    font-size: var(--text-xs);
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: var(--text-on-gold);
    background: var(--brand-gold);
    padding: 5px 10px;
    border-radius: 4px;
}

.imas-address-marker {
    margin-inline-end: 0.5rem;
    color: var(--brand-gold);
}

.listing-title-bar h3 {
    
    text-align: start;
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--text);
    text-transform: capitalize !important;
}

.imas-property-show .listing-address {
    color: var(--text-dim);
    font-size: var(--text-sm);
}

.imas-property-show .listing-address:hover {
    color: var(--brand-gold);
}

.imas-property-show .imas-property-title-row {
    width: 100%;
    justify-content: space-between;
    align-items: flex-start;
}

html[dir="rtl"]
    .imas-property-show
    .imas-property-title-row
    .single.detail-wrapper {
    margin-left: 0 !important;
    margin-inline-start: auto;
    flex-shrink: 0;
}

html[dir="rtl"]
    .imas-property-show
    .imas-property-title-row
    .imas-price-heading {
    text-align: left;
}
</style>

<style lang="scss">
.imas-property-show .imas-section-title {
    position: relative;
    display: inline-block;
    padding-bottom: 0.35rem;
    margin-bottom: 1rem;
    font-size: var(--text-md);
    font-weight: 700;
    color: var(--text);
    text-align: start;
    text-transform: capitalize !important;
}

.imas-property-show .imas-section-title::after {
    content: "";
    position: absolute;
    inset-inline-start: 0;
    bottom: 0;
    width: 3.5rem;
    height: 2px;
    background: var(--brand-gold);
    border-radius: 2px;
}

html[dir="rtl"] .imas-property-show .carousel-control.left {
    left: auto;
    right: 0;
}

html[dir="rtl"] .imas-property-show .carousel-control.right {
    right: auto;
    left: 0;
}

html[dir="rtl"] .imas-property-show .imas-property-gallery .carousel-inner {
    direction: ltr;
}

.imas-property-show-page .imas-property-show-panel h5.imas-section-title::after {
    margin-bottom: 0 !important;
}
</style>
