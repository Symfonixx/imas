<template>
    <div
        v-if="featuredProperties.length > 0"
        class="widget-boxed mt-5 imas-featured-properties-widget"
    >
        <div
            class="widget-boxed-header mb-5 d-flex justify-content-between align-items-center"
        >
            <h4>{{ trans("listing_page.feature_properties") }}</h4>
        </div>
        <div class="widget-boxed-body">
            <div
                ref="slickRootRef"
                class="slick-lancers imas-featured-properties-slick"
            >
                <div
                    v-for="p in featuredProperties"
                    :key="p.id"
                    class="agents-grid mr-0"
                >
                    <div class="listing-item compact">
                        <a :href="p.url" class="listing-img-container">
                            <div class="listing-badges">
                                <span class="featured">{{
                                    formatMoney(propertyStartPrice(p))
                                }}</span>
                                <span>{{
                                    trans("listing_page.for_sale")
                                }}</span>
                            </div>
                            <div class="listing-img-content">
                                <span class="listing-compact-title">
                                    {{ displayTitle(p) }}
                                    <i v-if="locationLine(p)">{{
                                        locationLine(p)
                                    }}</i>
                                </span>
                                <ul class="listing-hidden-content">
                                    <li
                                        v-for="(row, idx) in statRows(p)"
                                        :key="idx"
                                    >
                                        {{ row.label }}
                                        <span>{{ row.value }}</span>
                                    </li>
                                </ul>
                            </div>
                            <img :src="p.thumbnail_url" alt="" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";
import { usePage } from "@inertiajs/vue3";
import { propertyLocationLine } from "../utils/propertyLocation.js";
import { propertyStartPrice } from "../utils/propertyPrice.js";

const props = defineProps({
    featuredProperties: { type: Array, default: () => [] },
});

const page = usePage();

const slickRootRef = ref(null);

const slickIsRtl = computed(
    () => String(page.props.text_direction || "") === "rtl",
);

function trans(key) {
    return page.props.translations[key] || key;
}

const locale = () => page.props.locale || "en";

function displayTitle(p) {
    const t = p.title;
    if (typeof t === "string" && t.trim() !== "") {
        return t;
    }
    if (t && typeof t === "object") {
        const loc = locale();
        const raw =
            t[loc] ??
            t.en ??
            Object.values(t).find((v) => typeof v === "string");
        if (typeof raw === "string" && raw.trim() !== "") {
            return raw;
        }
    }
    const pn = p.project_name;
    if (typeof pn === "string" && pn.trim() !== "") {
        return pn;
    }
    if (typeof pn === "object" && pn !== null) {
        const loc = locale();
        const raw =
            pn[loc] ??
            pn.en ??
            Object.values(pn).find((v) => typeof v === "string");
        if (typeof raw === "string") {
            return raw;
        }
    }
    return p.project_code || "—";
}

function locationLine(p) {
    return propertyLocationLine(p.location, locale());
}

function formatMoney(amount) {
    const n = Number(amount);
    if (!Number.isFinite(n)) {
        return "—";
    }
    return new Intl.NumberFormat(locale(), {
        style: "currency",
        currency: "USD",
        maximumFractionDigits: 0,
    }).format(n);
}

function statRows(p) {
    const attrs = Array.isArray(p.highlights) ? p.highlights : [];
    return attrs.slice(0, 4).map((a) => ({
        label: a.name || a.code || "",
        value: a.display || "",
    }));
}

const SLICK_SCRIPT_SRC = "/theme/findhouses/js/slick.min.js";
const SLICK_SCRIPT_ID = "imas-theme-slick-carousel";

function loadSlickScriptOnce() {
    return new Promise((resolve, reject) => {
        const jq = window.jQuery || window.$;
        if (jq?.fn?.slick) {
            resolve();
            return;
        }
        const existing = document.getElementById(SLICK_SCRIPT_ID);
        if (existing) {
            existing.addEventListener("load", () => resolve(), { once: true });
            existing.addEventListener(
                "error",
                () => reject(new Error("Slick script failed")),
                { once: true },
            );
            return;
        }
        const el = document.createElement("script");
        el.id = SLICK_SCRIPT_ID;
        el.async = true;
        el.src = SLICK_SCRIPT_SRC;
        el.onload = () => resolve();
        el.onerror = () => reject(new Error("Slick script failed"));
        document.body.appendChild(el);
    });
}

function destroySlick() {
    const el = slickRootRef.value;
    const jq = window.jQuery || window.$;
    if (!el || !jq?.fn?.slick) {
        return;
    }
    const $el = jq(el);
    if ($el.hasClass("slick-initialized")) {
        $el.slick("unslick");
    }
}

/** Sidebar carousel: matches `theme/findhouses/js/slick4.js` base (1 slide, arrows, no dots). */
function initSlick() {
    const el = slickRootRef.value;
    const jq = window.jQuery || window.$;
    if (!el || !jq?.fn?.slick || props.featuredProperties.length === 0) {
        return;
    }
    jq(el).slick({
        rtl: slickIsRtl.value,
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        dots: false,
        arrows: true,
        adaptiveHeight: true,
    });
}

async function setupSlick() {
    if (props.featuredProperties.length === 0) {
        return;
    }
    try {
        await loadSlickScriptOnce();
    } catch {
        return;
    }
    await nextTick();
    destroySlick();
    await nextTick();
    initSlick();
}

onMounted(() => {
    setupSlick();
});

onBeforeUnmount(() => {
    destroySlick();
});

watch(
    () => [props.featuredProperties, slickIsRtl.value],
    () => {
        void setupSlick();
    },
    { deep: true },
);
</script>

<style scoped>
.listing-hidden-content {
    text-align: start;
}
</style>

<style lang="scss">
/*
 * RTL: theme fixes .featured at width:90px and .listing-badges { overflow:hidden },
 * which clips long formatted prices. Scoped to this widget only.
 */
html[dir="rtl"] .imas-featured-properties-widget .listing-item.compact .listing-badges {
    overflow: visible;
}

html[dir="rtl"]
    .imas-featured-properties-widget
    .listing-item.compact
    .listing-badges
    .featured {
    float: none;
    width: auto !important;
    max-width: calc(100% - 6.5rem);
    min-width: min-content;
    box-sizing: border-box;
    position: absolute;
    top: 17px;
    left: 15px;
    right: auto;
    margin: 0;
    text-align: start;
    white-space: nowrap;
    direction: ltr;
    unicode-bidi: isolate;
}

html[dir="rtl"]
    .imas-featured-properties-widget
    .listing-item.compact
    .listing-badges
    > span:not(.featured) {
    float: none;
    left: auto;
    right: 15px;
}
</style>
