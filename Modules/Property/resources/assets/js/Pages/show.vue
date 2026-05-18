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
        <div class="inner-pages blog">
          <section class="single-proper blog details imas-property-show">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-12 blog-pots">
                        <div class="row">
                            <div class="col-md-12">
                                <section class="headings-2 pt-0">
                                    <div class="pro-wrapper imas-property-title-row">
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
                                            <div class="detail-wrapper-body">
                                                <div
                                                    class="listing-title-bar text-start text-lg-end"
                                                >
                                                    <h4
                                                        class="imas-price-heading"
                                                    >
                                                        {{ priceLabel }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <PropertyShowGallery
                                    :property-id="property.id"
                                    :slides="property.slides"
                                    :thumbnail-url="property.thumbnail_url"
                                    :alt="displayTitle"
                                    :title="trans('property_show.gallery')"
                                />

                                <div
                                    v-if="overviewHtml"
                                    class="blog-info details mb-30 text-start"
                                >
                                    <h5 class="imas-section-title mb-4">
                                        {{ trans("property_show.description") }}
                                    </h5>
                                    <div
                                        class="imas-rich-content"
                                        v-html="overviewHtml"
                                    />
                                </div>

                             
                                <PropertyShowUnitTypesTable
                                    :unit-types="property.unit_types"
                                    :title="
                                        trans('property_show.unit_types_title')
                                    "
                                    :col-rooms="
                                        trans('property_show.col_rooms')
                                    "
                                    :col-area="trans('property_show.col_area')"
                                    :col-price="
                                        trans('property_show.col_price')
                                    "
                                />

                           

                                <div
                                    v-if="facilitiesHtml"
                                    class="blog-info details mb-30 text-start"
                                >
                                    <h5 class="imas-section-title mb-4">
                                        {{ trans("property_show.facilities") }}
                                    </h5>
                                    <div
                                        class="imas-rich-content"
                                        v-html="facilitiesHtml"
                                    />
                                </div>
                            </div>
                        </div>
                        <div
                                    v-if="contentHtml"
                                    class="blog-info details mb-30 text-start"
                                >
                                    <h5 class="imas-section-title mb-4">
                                        {{ trans("property_show.details") }}
                                    </h5>
                                    <div
                                        class="imas-rich-content"
                                        v-html="contentHtml"
                                    />
                                </div>

                        <PropertyShowVideo
                            v-if="property.youtube_video_url"
                            :video-url="property.youtube_video_url"
                            :poster-url="property.thumbnail_url"
                            :poster-alt="displayTitle"
                            :title="trans('property_show.property_video')"
                        />
                            <div
                                    v-if="whyToBuyHtml"
                                    class="blog-info details mb-30 text-start"
                                >
                                    <h5 class="imas-section-title mb-4">
                                        {{ trans("property_show.why_to_buy") }}
                                    </h5>
                                    <div
                                        class="imas-rich-content"
                                        v-html="whyToBuyHtml"
                                    />
                                </div>

                        <div id="listing-location">
                            <PropertyShowMap
                                :lat="property.lat"
                                :lng="property.lng"
                                :title="trans('property_show.location')"
                                :unavailable-text="
                                    trans('property_show.map_unavailable')
                                "
                            />
                        </div>
                    </div>

                    <aside class="col-lg-4 col-md-12 car">
                        <PropertyShowContactSidebar
                            :contact-store-url="contactStoreUrl"
                            :default-subject="inquirySubject"
                        />
                        <RecentPropertiesSidebar
                            :recent-properties="recentProperties"
                        />
                        <FeaturedPropertiesSidebar
                            :featured-properties="featuredProperties"
                        />
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
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
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

const priceLabel = computed(() => {
    const start = propertyStartPrice(props.property);
    if (start == null) {
        return "—";
    }
    return `${trans("properties.price_from")} ${formatMoney(start)}`;
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

const inquirySubject = computed(() =>
    trans("property_show.inquiry_subject").replace(
        ":title",
        displayTitle.value,
    ),
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
            return route("property.show", props.property.id);
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
    font-weight: 700;
    color: var(--brand-navy);
}

.imas-section-title::after {
    content: "";
    position: absolute;
    inset-inline-start: 0;
    bottom: 0;
    width: 3.5rem;
    height: 3px;
    background: var(--brand-gold);
    border-radius: 2px;
}

.imas-rich-content :deep(p:last-child) {
    margin-bottom: 0;
}

.imas-price-heading {
    color: var(--brand-gold);
    font-weight: 700;
}

.imas-for-sale-pill {
    background: var(--brand-gold);
    color: var(--brand-navy);
}

.imas-sold-out-pill {
    background: #dc3545;
    color: #fff;
}

.imas-property-type-badge {
    display: inline-block;
    font-size: 0.85rem;
    color: #fff;
    background: var(--brand-gold);
    padding: 5px 10px;
    border-radius: 7px;
}

.imas-address-marker {
    margin-inline-end: 0.5rem;
}

.listing-title-bar h3 {
    text-align: start;
    font-size: 24px;
}

.imas-property-show .imas-property-title-row {
    width: 100%;
    justify-content: space-between;
    align-items: flex-start;
}

html[dir="rtl"] .imas-property-show .imas-property-title-row .single.detail-wrapper {
    margin-left: 0 !important;
    margin-inline-start: auto;
    flex-shrink: 0;
}

html[dir="rtl"] .imas-property-show .imas-property-title-row .imas-price-heading {
    text-align: left;
}
</style>

<style lang="scss">
.imas-property-show .imas-section-title {
    position: relative;
    display: inline-block;
    padding-bottom: 0.35rem;
    margin-bottom: 1rem;
    font-weight: 700;
    color: var(--brand-navy);
    text-align: start;
}

.imas-property-show .imas-section-title::after {
    content: "";
    position: absolute;
    inset-inline-start: 0;
    bottom: 0;
    width: 3.5rem;
    height: 3px;
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
h5:after{
    margin-bottom:0 !important;
}
</style>
