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
                    <div class="row">
                        <!-- <div class="rld-single-input">
                            <input
                                v-model="searchKeyword"
                                type="search"
                                name="q"
                                autocomplete="off"
                                class="pt-0 pb-0"
                                :placeholder="trans('Enter Keyword...')"
                            />
                        </div> -->
                        <div class="rld-single-select">
                            <select
                                v-model="searchLocationId"
                                class="select single-select mr-22"
                                name="location_id"
                            >
                                <option value="">
                                    {{ trans("Location") }}
                                </option>
                                <option
                                    v-for="c in cities"
                                    :key="c.id"
                                    :value="String(c.id)"
                                >
                                    {{ c.name }}
                                </option>
                            </select>
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

                        <div class="col-xl-2 col-lg-2 col-md-4 pl-0">
                            <button
                                type="submit"
                                class="btn btn-yellow btn-block"
                            >
                                {{ trans("Search Now") }}
                            </button>
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

const props = defineProps({
    action: { type: String, required: true },
    purpose: { type: String, default: "sale" },
    propertyTypes: { type: Array, default: () => [] },
    cities: { type: Array, default: () => [] },
});

const page = usePage();

const searchKeyword = ref("");
const searchPropertyTypeId = ref("");
const searchLocationId = ref("");
const searchUnitTypeId = ref("");
const advancedOpen = ref(false);
const rangesDirty = ref(false);
const slidersReady = ref(false);

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

/* Advanced filters panel (area / price sliders) — not the Advanced Search button */
.imas-hero-property-search :deep(.imas-hero-advanced-panel.full-filter) {
    position: relative !important;
    top: auto !important;
    left: auto !important;
    width: 100%;
    max-width: 32rem;
    margin-inline: auto;
    border-radius: 8px !important;
    border: 1px solid var(--border) !important;
    background: var(--surface) !important;
    padding: 1.25rem 1.5rem !important;
}

.imas-hero-property-search :deep(.imas-hero-advanced-panel.filter-block) {
    margin-top: 0.75rem !important;
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

.hp-6 .dropdown-filter span::after {
    margin-left: 0;
    margin-inline-start: 15px;
}

/* Small screens: full-width fields, tighter height, more horizontal padding */
@media (max-width: 991.98px) {
    .homepage-9.hp-6
        .imas-hero-property-search
        .imas-hero-advanced-panel.full-filter.filter-block {
        margin-top: -6.25rem !important;
    }

    .imas-hero-property-search :deep(.imas-hero-advanced-panel.full-filter) {
        max-width: 100%;
    }
    .hp-6 .dropdown-filter span {
        line-height: 25px !important;
    }
    .imas-hero-property-search :deep(.rld-main-search) {
        /* height: fit-content !important; */
        min-height: 0 !important;
        padding-top: 1.25rem !important;
        padding-bottom: 1.25rem !important;
        padding-inline: 1.5rem !important;
    }

    .imas-hero-property-search :deep(.rld-main-search .row) {
        flex-direction: column !important;
        align-items: stretch !important;
        justify-content: flex-start !important;
        width: 100%;
        gap: 0.75rem;
        margin-inline: 0;
    }

    .imas-hero-property-search :deep(.rld-single-select),
    .imas-hero-property-search :deep(.rld-single-select.ml-22),
    .imas-hero-property-search :deep(.dropdown-filter),
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

html[dir="rtl"]
    .imas-hero-property-search
    :deep(#imas-hero-area-range .first-slider-value) {
    text-align: end !important;
}
html[dir="rtl"]
    .imas-hero-property-search
    :deep(#imas-hero-price-range .first-slider-value) {
    text-align: end !important;
}

.hp-6 .dropdown-filter span {
    color: var(--text) !important;
    border-color: var(--border) !important;
    background: var(--surface-2) !important;
}
</style>

<style>
@media (min-width: 992px) {
    .imas-hero-property-search .rld-main-search .row {
        justify-content: center !important;
    }
}

.rld-main-search .rld-single-select .single-select {
    box-shadow: none !important;
}

html[dir="ltr"] .unitTypeSelect .single-select {
    margin-right: 0 !important;
}
</style>
