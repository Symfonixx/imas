<template>
    <aside class="imas-blog-v2-sidebar">
        <div
            class="imas-blog-v2-sidebar__box imas-property-listings-filter"
        >
            <h4 class="imas-blog-v2-sidebar__heading text-start">
                {{ trans("listing_page.find_your_house") }}
            </h4>

            <div class="banner-search-wrap imas-listing-property-search">
                <form class="tab-content" method="get" :action="searchAction">
                    <input type="hidden" name="sort" :value="sort" />
                    <input
                        v-if="includeAdvancedParams"
                        type="hidden"
                        name="min_price"
                        :value="priceRange[0]"
                    />
                    <input
                        v-if="includeAdvancedParams"
                        type="hidden"
                        name="max_price"
                        :value="priceRange[1]"
                    />
                    <input
                        v-if="includeAdvancedParams"
                        type="hidden"
                        name="min_area"
                        :value="areaRange[0]"
                    />
                    <input
                        v-if="includeAdvancedParams"
                        type="hidden"
                        name="max_area"
                        :value="areaRange[1]"
                    />

                    <div class="tab-pane fade show active">
                        <div class="rld-main-search">
                            <div class="row imas-listing-property-search__fields">
                                <div class="rld-single-select imas-listing-city-cell">
                                    <LocationCityPicker
                                        v-model="searchCityIds"
                                        layout="sidebar"
                                        :cities="cities"
                                        name="location_id[]"
                                    />
                                </div>
                                <div class="rld-single-select imas-listing-location-cell">
                                    <LocationAreaPicker
                                        v-model="searchLocationIds"
                                        layout="sidebar"
                                        :districts="filteredDistricts"
                                        :areas="filteredAreas"
                                        name="location_id[]"
                                    />
                                </div>

                                <div class="rld-single-select">
                                    <select
                                        v-model="searchPropertyTypeId"
                                        class="select single-select wide"
                                        name="property_type_id"
                                    >
                                        <option value="">
                                            {{ trans("Property Type") }}
                                        </option>
                                        <option
                                            v-for="t in propertyTypes"
                                            :key="t.id"
                                            :value="String(t.id)"
                                        >
                                            {{ t.name }}
                                        </option>
                                    </select>
                                </div>

                                <div
                                    v-if="projectUnitTypes.length"
                                    class="rld-single-select unitTypeSelect"
                                >
                                    <select
                                        v-model="searchUnitTypeId"
                                        class="select single-select wide"
                                    >
                                        <option value="">
                                            {{ trans("Unit Types") }}
                                        </option>
                                        <option
                                            v-for="ut in projectUnitTypes"
                                            :key="ut.id"
                                            :value="String(ut.id)"
                                        >
                                            {{ ut.name }}
                                        </option>
                                    </select>
                                    <input
                                        v-if="searchUnitTypeId"
                                        type="hidden"
                                        name="project_unit_type_id[]"
                                        :value="searchUnitTypeId"
                                    />
                                </div>

                                <div class="imas-listing-range-panel">
                                    <div class="main-search-field-2">
                                        <div class="range-slider">
                                            <label>{{ trans("Area Size") }}</label>
                                            <div
                                                id="imas-listing-area-range"
                                                :data-min="areaBounds.min"
                                                :data-max="areaBounds.max"
                                                :data-unit="areaBounds.unit"
                                            ></div>
                                            <div class="clearfix"></div>
                                        </div>
                                        <br />
                                        <div class="range-slider">
                                            <label>{{
                                                trans("Price Range")
                                            }}</label>
                                            <div
                                                id="imas-listing-price-range"
                                                :data-min="priceBounds.min"
                                                :data-max="priceBounds.max"
                                                :data-unit="
                                                    priceBounds.currency
                                                "
                                            ></div>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="imas-listing-property-search__submit">
                                    <button
                                        type="submit"
                                        class="btn btn-yellow btn-block"
                                    >
                                        {{ trans("Search Now") }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div
            v-if="recentProperties.length > 0"
            class="imas-blog-v2-sidebar__box"
        >
            <h4 class="imas-blog-v2-sidebar__heading text-start">
                {{ trans("listing_page.recent_properties") }}
            </h4>
            <div class="imas-blog-v2-sidebar__recent">
                <a
                    v-for="p in recentProperties"
                    :key="p.id"
                    :href="p.url"
                    class="imas-blog-v2-sidebar__recent-item"
                >
                    <img
                        :src="p.thumbnail_url"
                        :alt="displayTitle(p)"
                        loading="lazy"
                    />
                    <div class="imas-blog-v2-sidebar__recent-body">
                        <div class="imas-blog-v2-sidebar__recent-title">
                            {{ displayTitle(p) }}
                        </div>
                        <div
                            class="imas-blog-v2-sidebar__recent-date text-dim"
                        >
                            {{ formatMoney(propertyStartPrice(p)) }}
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <FeaturedPropertiesSidebar
            :featured-properties="featuredProperties"
        />
    </aside>
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
import {
    destroyHeroRangeSliders,
    initHeroRangeSliders,
    loadJqueryUi,
} from "@/utils/initHeroRangeSliders.js";
import { formatPropertyMoney, propertyStartPrice } from "../utils/propertyPrice.js";
import FeaturedPropertiesSidebar from "./FeaturedPropertiesSidebar.vue";
import LocationAreaPicker from "@/components/Global/LocationAreaPicker.vue";
import LocationCityPicker from "@/components/Global/LocationCityPicker.vue";
import { useLocationSearchFilters } from "@/composables/useLocationSearchFilters.js";
import { splitLocationIds } from "@/utils/locationSearchFilters.js";

const LISTING_AREA_SELECTOR = "#imas-listing-area-range";
const LISTING_PRICE_SELECTOR = "#imas-listing-price-range";

const props = defineProps({
    searchAction: { type: String, required: true },
    filters: { type: Object, required: true },
    sort: { type: String, required: true },
    cities: { type: Array, default: () => [] },
    districts: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
    propertyTypes: { type: Array, default: () => [] },
    recentProperties: { type: Array, default: () => [] },
    featuredProperties: { type: Array, default: () => [] },
});

const page = usePage();

const searchPropertyTypeId = ref("");
const searchUnitTypeId = ref("");
const rangesDirty = ref(false);
const slidersReady = ref(false);

const {
    searchCityIds,
    searchLocationIds,
    filteredDistricts,
    filteredAreas,
} = useLocationSearchFilters(
    () => props.cities,
    () => props.districts,
    () => props.areas,
);

const propertySearch = computed(
    () =>
        page.props.property_search ?? page.props.globals?.property_search ?? {},
);

const priceBounds = computed(() => {
    const p = propertySearch.value.price ?? {};
    return {
        min: Number(p.min ?? 0),
        max: Number(p.max ?? 1) || 1,
        currency: String(p.currency ?? "$"),
    };
});

const areaBounds = computed(() => {
    const a = propertySearch.value.area ?? {};
    return {
        min: Number(a.min ?? 0),
        max: Number(a.max ?? 1) || 1,
        unit: String(a.unit ?? "m²"),
    };
});

const projectUnitTypes = computed(
    () => propertySearch.value.project_unit_types ?? [],
);

const priceRange = ref([0, 1]);
const areaRange = ref([0, 1]);

const includeAdvancedParams = computed(() => rangesDirty.value);

function trans(key) {
    return page.props.translations[key] || key;
}

function locale() {
    return page.props.locale || "en";
}

function syncFromFilters(f) {
    const locationIds = f.location_id;
    let rawIds = [];

    if (Array.isArray(locationIds)) {
        rawIds = locationIds
            .filter((id) => id != null && id !== "")
            .map((id) => String(id));
    } else if (locationIds != null && locationIds !== "") {
        rawIds = [String(locationIds)];
    }

    const { cityIds, districtAreaIds } = splitLocationIds(
        rawIds,
        props.cities,
        props.districts,
        props.areas,
    );

    searchLocationIds.value = districtAreaIds;
    searchCityIds.value = cityIds;
    searchPropertyTypeId.value =
        f.property_type_id != null && f.property_type_id !== ""
            ? String(f.property_type_id)
            : "";
    const unitIds = f.project_unit_type_id;
    if (Array.isArray(unitIds) && unitIds.length > 0) {
        searchUnitTypeId.value = String(unitIds[0]);
    } else if (unitIds != null && unitIds !== "") {
        searchUnitTypeId.value = String(unitIds);
    } else {
        searchUnitTypeId.value = "";
    }

    const hasPrice =
        f.min_price != null &&
        f.min_price !== "" &&
        f.max_price != null &&
        f.max_price !== "";
    const hasArea =
        f.min_area != null &&
        f.min_area !== "" &&
        f.max_area != null &&
        f.max_area !== "";

    if (hasPrice) {
        priceRange.value = [Number(f.min_price), Number(f.max_price)];
    } else {
        priceRange.value = [priceBounds.value.min, priceBounds.value.max];
    }

    if (hasArea) {
        areaRange.value = [Number(f.min_area), Number(f.max_area)];
    } else {
        areaRange.value = [areaBounds.value.min, areaBounds.value.max];
    }

    markRangesDirty();
}

function syncRangeDefaults() {
    priceRange.value = [priceBounds.value.min, priceBounds.value.max];
    areaRange.value = [areaBounds.value.min, areaBounds.value.max];
}

function markRangesDirty() {
    const priceDefault =
        priceRange.value[0] === priceBounds.value.min &&
        priceRange.value[1] === priceBounds.value.max;
    const areaDefault =
        areaRange.value[0] === areaBounds.value.min &&
        areaRange.value[1] === areaBounds.value.max;
    rangesDirty.value = !priceDefault || !areaDefault;
}

async function ensureSliders() {
    if (slidersReady.value) {
        return;
    }

    const themeUrl = page.props.theme_url;
    if (!themeUrl) {
        return;
    }

    try {
        await loadJqueryUi(themeUrl);
        await nextTick();
        initHeroRangeSliders({
            areaSelector: LISTING_AREA_SELECTOR,
            priceSelector: LISTING_PRICE_SELECTOR,
            areaMin: areaBounds.value.min,
            areaMax: areaBounds.value.max,
            areaUnit: areaBounds.value.unit,
            priceMin: priceBounds.value.min,
            priceMax: priceBounds.value.max,
            priceUnit: priceBounds.value.currency,
            initialArea: areaRange.value,
            initialPrice: priceRange.value,
            onAreaChange(min, max) {
                areaRange.value = [min, max];
                markRangesDirty();
            },
            onPriceChange(min, max) {
                priceRange.value = [min, max];
                markRangesDirty();
            },
        });
        slidersReady.value = true;
    } catch {
        /* sliders are progressive enhancement */
    }
}

watch(
    () => props.filters,
    (f) => syncFromFilters(f ?? {}),
    { deep: true },
);

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

function formatMoney(amount) {
    return formatPropertyMoney(amount, locale());
}

onMounted(async () => {
    syncRangeDefaults();
    syncFromFilters(props.filters ?? {});
    await nextTick();
    await ensureSliders();
});

onBeforeUnmount(() => {
    destroyHeroRangeSliders({
        areaSelector: LISTING_AREA_SELECTOR,
        priceSelector: LISTING_PRICE_SELECTOR,
    });
});
</script>

<style lang="scss" scoped>
.imas-property-listings-filter {
    position: relative;
    z-index: 100;
    max-width: 100%;
    min-width: 0;
    overflow: visible;
}

.imas-listing-property-search {
    max-width: 100%;
    min-width: 0;
    overflow: visible;
}

.imas-listing-property-search :deep(.imas-listing-city-cell),
.imas-listing-property-search :deep(.imas-listing-location-cell) {
    overflow: visible !important;
    position: relative;
    z-index: 1;
}

.imas-listing-property-search
    :deep(.imas-listing-city-cell .imas-loc-picker.is-open),
.imas-listing-property-search
    :deep(.imas-listing-location-cell .imas-loc-picker.is-open) {
    z-index: 1100;
}

.imas-listing-property-search :deep(.imas-loc-picker__trigger) {
    width: 100%;
    box-shadow: none !important;
}

.imas-listing-property-search :deep(.rld-main-search),
.imas-listing-property-search :deep(.tab-content),
.imas-listing-property-search :deep(.banner-search-wrap) {
    overflow: visible !important;
}

.imas-listing-property-search :deep(.banner-search-wrap),
.imas-listing-property-search :deep(.tab-content),
.imas-listing-property-search :deep(.rld-main-search),
.imas-listing-property-search :deep(.main-search-field-2) {
    min-height: 0 !important;
    height: auto !important;
    padding: 0 !important;
    margin: 0 !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    max-width: 100%;
}

.imas-listing-property-search :deep(.imas-listing-property-search__fields) {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    margin: 0 !important;
    padding: 0 !important;
    --bs-gutter-x: 0;
    --bs-gutter-y: 0;
}

.imas-listing-property-search :deep(.rld-single-select),
.imas-listing-property-search :deep(.imas-listing-city-cell),
.imas-listing-property-search :deep(.imas-listing-location-cell),
.imas-listing-property-search :deep(.imas-listing-range-panel),
.imas-listing-property-search :deep(.imas-listing-property-search__submit) {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    flex: 0 0 auto;
}

.imas-listing-property-search :deep(.rld-single-select .single-select) {
    margin-bottom: 0 !important;
    margin-inline: 0 !important;
}

.imas-listing-property-search :deep(.single-select),
.imas-listing-property-search :deep(.nice-select) {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    text-align: start;
    box-shadow: none !important;
    box-sizing: border-box;
}

.imas-listing-property-search :deep(.nice-select .current) {
    text-align: start;
    color: var(--text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

.imas-listing-property-search :deep(.btn.btn-yellow) {
    width: 100%;
    margin: 0;
}

/* Area + price — below last dropdown (unit type or property type) */
.imas-listing-property-search :deep(.imas-listing-range-panel) {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    padding: 0;
    overflow: visible;
    border: none !important;
    background: transparent !important;
}

.imas-listing-property-search :deep(.imas-listing-range-panel .main-search-field-2) {
    margin: 0;
    width: 100%;
    max-width: 100%;
    min-width: 0;
}

.imas-listing-property-search :deep(.imas-listing-range-panel .range-slider) {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    margin-bottom: 1.25rem;
    overflow: visible;
    line-height: 1.4;
}

.imas-listing-property-search :deep(.imas-listing-range-panel .range-slider .clearfix) {
    display: block;
    clear: both;
    height: 0;
}

.imas-listing-property-search :deep(.imas-listing-range-panel .range-slider:last-child) {
    margin-bottom: 0;
}

.imas-listing-property-search :deep(.imas-listing-range-panel .range-slider label) {
    display: block;
    width: 100%;
    color: var(--text) !important;
    font-size: 0.95rem;
    margin: 0 0 1rem;
    padding: 0;
    text-align: start;
}

/* Same as HomeHeroPropertySearch — values live inside #imas-listing-*-range (.hp-6 theme) */
.imas-listing-property-search
    :deep(.imas-listing-range-panel input.first-slider-value),
.imas-listing-property-search
    :deep(.imas-listing-range-panel input.second-slider-value) {
    background: transparent !important;
    background-color: transparent !important;
    color: var(--text) !important;
    border: 0 !important;
    box-shadow: none !important;
}

.imas-listing-property-search :deep(#imas-listing-area-range .first-slider-value),
.imas-listing-property-search :deep(#imas-listing-area-range .second-slider-value),
.imas-listing-property-search :deep(#imas-listing-price-range .first-slider-value),
.imas-listing-property-search :deep(#imas-listing-price-range .second-slider-value) {
    background: transparent !important;
}

html[dir="rtl"]
    .imas-listing-property-search
    :deep(#imas-listing-area-range .first-slider-value) {
    text-align: end !important;
}

html[dir="rtl"]
    .imas-listing-property-search
    :deep(#imas-listing-price-range .first-slider-value) {
    text-align: end !important;
}

.imas-listing-property-search :deep(.nice-select .list) {
    max-height: 200px;
    overflow-y: auto;
    z-index: 1001;
    left: 0;
    right: 0;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.imas-listing-property-search :deep(.nice-select.open) {
    z-index: 1002;
}
</style>

<style lang="scss">
html[dir="rtl"] .imas-listing-property-search .nice-select {
    padding-left: 30px !important;
    padding-right: 18px !important;
    text-align: start !important;
}

html[dir="rtl"] .imas-listing-property-search .nice-select::after {
    right: auto !important;
    left: 12px !important;
}

html[dir="rtl"] .imas-listing-property-search .nice-select .option {
    padding-left: 29px !important;
    padding-right: 18px !important;
    text-align: start;
}

html[dir="rtl"] .imas-listing-property-search .nice-select.open .list {
    left: 0 !important;
    right: 0 !important;
}

/* Sidebar filter box */
.imas-blog-v2-sidebar__box.imas-property-listings-filter {
    overflow-x: visible;
    overflow-y: visible;
}

.imas-blog-v2-sidebar__box.imas-property-listings-filter .rld-main-search,
.imas-blog-v2-sidebar__box.imas-property-listings-filter .banner-search-wrap,
.imas-blog-v2-sidebar__box.imas-property-listings-filter .tab-content {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
}

.imas-blog-v2-sidebar .imas-listing-property-search .nice-select {
    border: 1px solid var(--border);
    background: var(--surface-2);
    color: var(--text);
}

.imas-blog-v2-sidebar .imas-listing-property-search .imas-loc-picker__trigger {
    border: 1px solid var(--border);
    background: var(--surface-2);
    color: var(--text);
}

.imas-blog-v2-sidebar .imas-listing-property-search .nice-select .list {
    background: var(--surface);
    border-color: var(--border);
}

.imas-blog-v2-sidebar .imas-listing-property-search .nice-select .option:hover,
.imas-blog-v2-sidebar
    .imas-listing-property-search
    .nice-select
    .option.selected.focus {
    background: var(--surface-3);
}

</style>
