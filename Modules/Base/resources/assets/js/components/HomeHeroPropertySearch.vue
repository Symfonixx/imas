<template>
    <div class="banner-search-wrap imas-hero-property-search">
        <form class="tab-content" method="get" :action="action">
            <input type="hidden" name="purpose" :value="purpose" />
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
                    <div class="imas-hero-search-row">
                        <div class="imas-hero-search-fields">
                            <div class="rld-single-select imas-hero-city-cell">
                                <LocationCityPicker
                                    v-model="searchCityIds"
                                    :cities="cities"
                                    name="location_id[]"
                                />
                            </div>
                            <div class="rld-single-select imas-hero-location-cell">
                                <LocationAreaPicker
                                    v-model="searchLocationIds"
                                    :districts="filteredDistricts"
                                    :areas="filteredAreas"
                                    name="location_id[]"
                                />
                            </div>
                            <div class="rld-single-select ml-22">
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

                            <div class="imas-hero-advanced-wrap">
                                <div
                                    class="dropdown-filter"
                                    role="button"
                                    tabindex="0"
                                    :aria-expanded="advancedOpen"
                                    @click.prevent="toggleAdvanced"
                                    @keydown.enter.prevent="toggleAdvanced"
                                    @keydown.space.prevent="toggleAdvanced"
                                >
                                    <span>{{ trans("Advanced Search") }}</span>
                                </div>

                                <div
                                    class="explore__form-checkbox-list full-filter imas-hero-advanced-panel"
                                    :class="{ 'filter-block': advancedOpen }"
                                >
                                    <div class="row">
                                        <div class="col-12 py-1 pr-30 sld">
                                            <div class="main-search-field-2">
                                                <div class="range-slider">
                                                    <label>{{
                                                        trans("Area Size")
                                                    }}</label>
                                                    <div
                                                        id="imas-hero-area-range"
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
                                                        id="imas-hero-price-range"
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
                                    </div>
                                </div>
                            </div>

                            <div class="imas-hero-search-submit">
                                <button
                                    type="submit"
                                    class="btn btn-yellow"
                                >
                                    {{ trans("Search Now") }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
import {
    destroyHeroRangeSliders,
    initHeroRangeSliders,
    loadJqueryUi,
} from "@/utils/initHeroRangeSliders.js";
import LocationAreaPicker from "@/components/Global/LocationAreaPicker.vue";
import LocationCityPicker from "@/components/Global/LocationCityPicker.vue";
import { useLocationSearchFilters } from "@/composables/useLocationSearchFilters.js";

const props = defineProps({
    action: { type: String, required: true },
    purpose: { type: String, default: "sale" },
    propertyTypes: { type: Array, default: () => [] },
    cities: { type: Array, default: () => [] },
    districts: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
});

const page = usePage();

const searchKeyword = ref("");
const searchPropertyTypeId = ref("");
const searchUnitTypeId = ref("");
const advancedOpen = ref(false);
const rangesDirty = ref(false);
const slidersReady = ref(false);

const { searchCityIds, searchLocationIds, filteredDistricts, filteredAreas } =
    useLocationSearchFilters(
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

function syncRangeDefaults() {
    priceRange.value = [priceBounds.value.min, priceBounds.value.max];
    areaRange.value = [areaBounds.value.min, areaBounds.value.max];
}

function toggleAdvanced() {
    advancedOpen.value = !advancedOpen.value;
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

watch(advancedOpen, async (open) => {
    if (open) {
        await nextTick();
        await ensureSliders();
    }
});

onMounted(() => {
    syncRangeDefaults();
});

onBeforeUnmount(() => {
    destroyHeroRangeSliders();
});
</script>

<style lang="scss" scoped>
.imas-hero-property-search :deep(.single-select),
.imas-hero-property-search :deep(.nice-select) {
    text-align: start;
}

.imas-hero-property-search :deep(.nice-select .current) {
    text-align: start;
}

.imas-hero-property-search :deep(.dropdown-filter span) {
    text-align: start;
}

/* Match theme spacing between hero search fields (.single-select has margin-right: 15px) */
@media (max-width: 991.98px) {
    .imas-hero-property-search :deep(.imas-hero-city-cell),
    .imas-hero-property-search :deep(.imas-hero-location-cell) {
        margin-inline-end: 15px;
    }
}

/* Advanced filters panel — mobile: directly below Advanced Search button */
@media (max-width: 991.98px) {
    .imas-hero-property-search :deep(.imas-hero-advanced-wrap) {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        width: 100%;
        position: relative;
    }

    .imas-hero-property-search
        :deep(.imas-hero-advanced-panel.full-filter:not(.filter-block)) {
        height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
        border: none !important;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .imas-hero-property-search
        :deep(.imas-hero-advanced-panel.full-filter.filter-block) {
        position: relative !important;
        top: auto !important;
        left: auto !important;
        transform: none !important;
        height: auto !important;
        width: 100% !important;
        max-width: 100% !important;
        margin-inline: 0 !important;
        margin-top: 0.65rem !important;
        border-radius: 8px !important;
        border: 1px solid var(--border) !important;
        background: var(--surface) !important;
        padding: 0.85rem 0.9rem !important;
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        box-sizing: border-box;
    }

    .imas-hero-property-search
        :deep(.imas-hero-advanced-panel .range-slider label) {
        font-size: var(--text-sm) !important;
        margin-bottom: 0.65rem !important;
        line-height: 1.35;
    }

    .imas-hero-property-search
        :deep(.imas-hero-advanced-panel .range-slider) {
        line-height: 1.4 !important;
        margin-bottom: 0.35rem;
    }

    .imas-hero-property-search
        :deep(.imas-hero-advanced-panel input.first-slider-value),
    .imas-hero-property-search
        :deep(.imas-hero-advanced-panel input.second-slider-value) {
        font-size: 11px !important;
        width: 47% !important;
        max-width: 47% !important;
        margin-top: 1rem !important;
        line-height: 1.3 !important;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .imas-hero-property-search
        :deep(.imas-hero-advanced-panel input.first-slider-value) {
        float: inline-start;
        margin-inline-start: 0 !important;
        text-align: start;
    }

    .imas-hero-property-search
        :deep(.imas-hero-advanced-panel input.second-slider-value) {
        float: inline-end;
        margin-inline-end: 0 !important;
        text-align: end;
    }

    .imas-hero-property-search :deep(.imas-hero-advanced-panel .sld),
    .imas-hero-property-search :deep(.imas-hero-advanced-panel .pr-30) {
        padding-inline: 0 !important;
        margin-inline: 0 !important;
    }

    .imas-hero-property-search :deep(.imas-hero-advanced-panel .row) {
        margin-inline: 0 !important;
        width: 100%;
    }

    .imas-hero-property-search
        :deep(.imas-hero-advanced-panel .ui-slider-horizontal) {
        width: calc(100% - 8px) !important;
        margin-inline: 4px !important;
        margin-bottom: 1rem !important;
    }
}

.imas-hero-property-search
    :deep(.imas-hero-advanced-panel .main-search-field-2) {
    margin-top: 0;
}

.imas-hero-property-search
    :deep(.imas-hero-advanced-panel .range-slider label) {
    color: var(--text) !important;
    font-size: 0.95rem;
    margin-bottom: 1rem;
}

.imas-hero-property-search
    :deep(.imas-hero-advanced-panel input.first-slider-value),
.imas-hero-property-search
    :deep(.imas-hero-advanced-panel input.second-slider-value) {
    background: transparent !important;
    background-color: transparent !important;
    color: var(--text) !important;
    border: 0 !important;
    box-shadow: none !important;
}

.imas-hero-property-search
    :deep(.imas-hero-advanced-panel input.first-slider-value) {
    float: inline-start !important;
    text-align: start;
}

.imas-hero-property-search
    :deep(.imas-hero-advanced-panel input.second-slider-value) {
    float: inline-end !important;
    text-align: end;
}

.hp-6 .dropdown-filter span::after {
    margin-left: 0;
    margin-inline-start: 15px;
}

/* Small screens: full-width fields, tighter height, more horizontal padding */
@media (max-width: 991.98px) {
    .imas-hero-property-search :deep(.imas-hero-advanced-panel.full-filter) {
        max-width: 100%;
    }
    .hp-6 .dropdown-filter span {
        line-height: 25px !important;
    }
    .imas-hero-property-search :deep(.rld-main-search) {
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        height: auto !important;
        min-height: 0 !important;
        padding-top: 1.25rem !important;
        padding-bottom: 1.25rem !important;
        padding-inline: 1.5rem !important;
        overflow: visible !important;
    }

    .imas-hero-property-search :deep(.imas-hero-search-row) {
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        width: 100%;
        height: auto !important;
    }

    .imas-hero-property-search :deep(.banner-search-wrap),
    .imas-hero-property-search :deep(.tab-content),
    .imas-hero-property-search :deep(.imas-hero-city-cell),
    .imas-hero-property-search :deep(.imas-hero-location-cell) {
        overflow: visible !important;
    }

    .imas-hero-property-search :deep(.imas-hero-search-fields) {
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        justify-content: flex-start !important;
        width: 100%;
        gap: 0.75rem;
        margin-inline: 0;
    }

    .imas-hero-property-search :deep(.rld-main-search .row) {
        flex-direction: column !important;
        align-items: stretch !important;
        justify-content: flex-start !important;
        width: 100%;
        gap: 0.75rem;
        margin-inline: 0;
    }

    .imas-hero-property-search :deep(.imas-hero-search-submit) {
        width: 100% !important;
        max-width: 100% !important;
        margin-top: 0 !important;
        padding: 0 !important;
    }

    .imas-hero-property-search :deep(.imas-hero-search-submit .btn.btn-yellow) {
        width: 100% !important;
        max-width: 100% !important;
        margin-top: 0 !important;
    }

    .imas-hero-property-search :deep(.rld-single-select),
    .imas-hero-property-search :deep(.rld-single-select.ml-22),
    .imas-hero-property-search :deep(.imas-hero-city-cell),
    .imas-hero-property-search :deep(.imas-hero-location-cell),
    .imas-hero-property-search :deep(.imas-hero-advanced-wrap),
    .imas-hero-property-search :deep(.dropdown-filter),
    .imas-hero-property-search :deep(.imas-hero-search-submit),
    .imas-hero-property-search
        :deep(.imas-hero-search-fields > *),
    .imas-hero-property-search
        :deep(.rld-main-search > .row > [class*="col-"]) {
        width: 100% !important;
        max-width: 100% !important;
        margin-inline: 0 !important;
        padding-inline: 0 !important;
    }

    .imas-hero-property-search :deep(.rld-single-select .single-select),
    .imas-hero-property-search :deep(.rld-single-select .nice-select),
    .imas-hero-property-search :deep(.dropdown-filter span),
    .imas-hero-property-search :deep(.btn.btn-yellow) {
        width: 100% !important;
        max-width: 100% !important;
        margin-inline: 0 !important;
        color: var(--text) !important;
    }

    .imas-hero-property-search :deep(.dropdown-filter span) {
        display: block;
        box-sizing: border-box;
    }
}

@media (max-width: 575.98px) {
    .imas-hero-property-search :deep(.rld-main-search) {
        padding-inline: 1.75rem !important;
    }
}

.imas-hero-property-search :deep(#imas-hero-area-range .first-slider-value),
.imas-hero-property-search :deep(#imas-hero-area-range .second-slider-value),
.imas-hero-property-search :deep(#imas-hero-price-range .first-slider-value),
.imas-hero-property-search :deep(#imas-hero-price-range .second-slider-value) {
    background: transparent !important;
}

.hp-6 .dropdown-filter span {
    color: var(--text) !important;
    border-color: var(--border) !important;
    background: var(--surface-2) !important;
}
</style>

<style>
@media (min-width: 992px) {
    .imas-hero-property-search,
    .imas-hero-property-search .banner-search-wrap,
    .imas-hero-property-search .tab-content,
    .imas-hero-property-search .tab-pane.active,
    .imas-hero-property-search .rld-main-search {
        width: fit-content;
        max-width: 100%;
        margin-inline: auto;
    }

    .imas-hero-property-search .rld-main-search {
        height: auto !important;
        min-height: 0 !important;
        padding: 2rem 2.25rem !important;
        overflow: visible !important;
    }

    .imas-hero-property-search .imas-hero-search-row {
        position: relative;
        display: flex;
        flex-direction: column;
        width: fit-content;
        max-width: 100%;
    }

    .imas-hero-property-search .imas-hero-search-fields {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: flex-start;
        width: auto;
        gap: 12px;
    }

    .imas-hero-property-search .imas-hero-search-fields > .rld-single-select,
    .imas-hero-property-search .imas-hero-search-fields > .imas-hero-advanced-wrap {
        flex: 0 0 auto;
        width: auto !important;
        min-width: 0;
        max-width: none !important;
        margin: 0 !important;
    }

    .imas-hero-property-search .imas-hero-search-fields > .imas-hero-advanced-wrap {
        position: relative;
    }

    .imas-hero-property-search .imas-hero-search-fields .single-select,
    .imas-hero-property-search .imas-hero-search-fields .nice-select,
    .imas-hero-property-search .imas-hero-search-fields .nice-select .current,
    .imas-hero-property-search .imas-hero-search-fields .imas-loc-picker,
    .imas-hero-property-search .imas-hero-search-fields .imas-loc-picker__trigger,
    .imas-hero-property-search .imas-hero-search-fields .dropdown-filter span {
        width: auto !important;
        max-width: none !important;
        margin: 0 !important;
        white-space: nowrap;
        box-sizing: border-box;
    }

    .imas-hero-property-search
        .imas-hero-search-fields
        .imas-loc-picker__trigger-label {
        max-width: 14rem;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .imas-hero-property-search .imas-hero-search-fields .imas-hero-search-submit {
        flex: 0 0 104px;
        width: 104px;
        min-width: 104px;
        padding: 0 !important;
        margin: 0 !important;
    }

    .imas-hero-property-search
        .imas-hero-search-fields
        .imas-hero-search-submit
        .btn.btn-yellow {
        width: 104px !important;
        min-width: 104px !important;
        max-width: 104px !important;
        margin: 0 !important;
        padding-inline: 0.5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .imas-hero-property-search .imas-hero-search-fields > .rld-single-select.ml-22 {
        margin-inline-start: 0 !important;
    }

    /* Advanced panel: absolute dropdown anchored under Advanced Search button */
    .imas-hero-property-search .imas-hero-advanced-panel.full-filter {
        position: absolute !important;
        top: calc(100% + 0.35rem);
        left: 50%;
        right: auto;
        transform: translateX(-50%);
        width: 32rem;
        max-width: min(32rem, calc(100vw - 2rem));
        margin-top: 0 !important;
        margin-inline: 0;
        border-radius: 8px !important;
        z-index: 1000;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }

    .imas-hero-property-search
        .imas-hero-advanced-panel.full-filter:not(.filter-block) {
        height: 0 !important;
        padding: 0 !important;
        border: none !important;
        overflow: hidden !important;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .imas-hero-property-search .imas-hero-advanced-panel.full-filter.filter-block {
        height: auto !important;
        padding: 1.25rem 1.5rem !important;
        border: 1px solid var(--border) !important;
        background: var(--surface) !important;
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        box-shadow: var(--shadow-lg);
    }
}

@media (max-width: 991.98px) {
    .homepage-9.hp-6.mh .imas-hero-property-search .rld-main-search,
    .hp-6.mh .imas-hero-property-search .rld-main-search,
    .hp-6 .imas-hero-property-search .rld-main-search {
        height: auto !important;
        min-height: 0 !important;
    }

    .hp-6 .imas-hero-property-search .imas-hero-search-submit .btn.btn-yellow {
        margin-top: 0 !important;
    }
}

.rld-main-search .rld-single-select .single-select {
    box-shadow: none !important;
}

html[dir="ltr"] .unitTypeSelect .single-select {
    margin-right: 0 !important;
}
</style>
