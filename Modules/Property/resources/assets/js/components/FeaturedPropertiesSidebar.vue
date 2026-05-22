<template>
    <div
        v-if="featuredProperties.length > 0"
        class="imas-blog-v2-sidebar__box imas-featured-properties-sidebar"
    >
        <div class="imas-featured-properties-sidebar__header">
            <h4
                class="imas-blog-v2-sidebar__heading imas-featured-properties-sidebar__heading text-start"
            >
                {{ trans("listing_page.feature_properties") }}
            </h4>
            <div
                v-show="showCarouselArrows"
                ref="navRef"
                class="imas-featured-properties-sidebar__nav"
            >
                <button
                    ref="prevArrowRef"
                    type="button"
                    class="imas-featured-properties-sidebar__arrow imas-featured-properties-sidebar__arrow--prev"
                    :aria-label="trans('Previous')"
                ></button>
                <button
                    ref="nextArrowRef"
                    type="button"
                    class="imas-featured-properties-sidebar__arrow imas-featured-properties-sidebar__arrow--next"
                    :aria-label="trans('Next')"
                ></button>
            </div>
        </div>

        <div class="imas-featured-properties-sidebar__body">
            <div
                ref="slickRootRef"
                class="imas-featured-properties-sidebar__carousel"
            >
                <article
                    v-for="p in featuredProperties"
                    :key="p.id"
                    class="imas-featured-properties-sidebar__slide"
                >
                    <a
                        :href="p.url"
                        class="imas-featured-properties-sidebar__card"
                    >
                        <div class="imas-featured-properties-sidebar__media">
                            <img
                                :src="p.thumbnail_url"
                                :alt="displayTitle(p)"
                                loading="lazy"
                            />
                            <div
                                class="imas-featured-properties-sidebar__badges"
                            >
                                <span
                                    class="imas-featured-properties-sidebar__badge imas-featured-properties-sidebar__badge--price"
                                >
                                    {{ formatMoney(propertyStartPrice(p)) }}
                                </span>
                               
                            </div>
                            <div
                                class="imas-featured-properties-sidebar__overlay"
                            >
                                <h5
                                    class="imas-featured-properties-sidebar__title"
                                >
                                    {{ displayTitle(p) }}
                                </h5>
                                <p
                                    v-if="locationLine(p)"
                                    class="imas-featured-properties-sidebar__location text-dim"
                                >
                                    {{ locationLine(p) }}
                                </p>
                                <ul
                                    v-if="statRows(p).length > 0"
                                    class="imas-featured-properties-sidebar__stats"
                                >
                                    <li
                                        v-for="(row, idx) in statRows(p)"
                                        :key="idx"
                                    >
                                        <span
                                            class="imas-featured-properties-sidebar__stat-label"
                                            >{{ row.label }}</span
                                        >
                                        <span
                                            class="imas-featured-properties-sidebar__stat-value"
                                            >{{ row.value }}</span
                                        >
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </a>
                </article>
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
const navRef = ref(null);
const prevArrowRef = ref(null);
const nextArrowRef = ref(null);

const showCarouselArrows = computed(
    () => props.featuredProperties.length > 1,
);

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

function initSlick() {
    const el = slickRootRef.value;
    const jq = window.jQuery || window.$;
    const prev = prevArrowRef.value;
    const next = nextArrowRef.value;
    const nav = navRef.value;
    if (!el || !jq?.fn?.slick || props.featuredProperties.length === 0) {
        return;
    }

    const options = {
        rtl: slickIsRtl.value,
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        dots: false,
        arrows: showCarouselArrows.value,
        adaptiveHeight: true,
    };

    if (showCarouselArrows.value && prev && next && nav) {
        options.prevArrow = jq(prev);
        options.nextArrow = jq(next);
        options.appendArrows = jq(nav);
    }

    jq(el).slick(options);
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
