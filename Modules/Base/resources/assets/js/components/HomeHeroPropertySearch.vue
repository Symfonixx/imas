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
                        <div class="rld-single-input">
                            <input
                                v-model="searchKeyword"
                                type="search"
                                name="q"
                                autocomplete="off"
                                class="pt-0 pb-0"
                                :placeholder="trans('Enter Keyword...')"
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

                        <div class="rld-single-select">
                            <select
                                v-model="searchLocationId"
                                class="select single-select mr-0"
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
                                class="btn btn-yellow btn-block !text-white"
                            >
                                {{ trans("Search Now") }}
                            </button>
                        </div>

                        <div
                            class="explore__form-checkbox-list full-filter"
                            :class="{ 'filter-block': advancedOpen }"
                        >
                            <div class="row">
                                <div
                                    v-if="unitTypesColumnA.length"
                                    class="col-lg-3 col-md-6 col-sm-12 py-1 imas-hero-unit-col"
                                >
                                    <div
                                        class="checkboxes one-in-row margin-bottom-10 ch-1"
                                    >
                                        <template
                                            v-for="ut in unitTypesColumnA"
                                            :key="ut.id"
                                        >
                                            <input
                                                :id="`imas-hero-ut-${ut.id}`"
                                                v-model="selectedUnitTypeIds"
                                                type="checkbox"
                                                name="project_unit_type_id[]"
                                                :value="String(ut.id)"
                                            />
                                            <label :for="`imas-hero-ut-${ut.id}`">{{
                                                ut.name
                                            }}</label>
                                        </template>
                                    </div>
                                </div>

                                <div
                                    v-if="unitTypesColumnB.length"
                                    class="col-lg-3 col-md-6 col-sm-12 py-1 imas-hero-unit-col"
                                >
                                    <div
                                        class="checkboxes one-in-row margin-bottom-10 ch-2"
                                    >
                                        <template
                                            v-for="ut in unitTypesColumnB"
                                            :key="ut.id"
                                        >
                                            <input
                                                :id="`imas-hero-ut-${ut.id}`"
                                                v-model="selectedUnitTypeIds"
                                                type="checkbox"
                                                name="project_unit_type_id[]"
                                                :value="String(ut.id)"
                                            />
                                            <label :for="`imas-hero-ut-${ut.id}`">{{
                                                ut.name
                                            }}</label>
                                        </template>
                                    </div>
                                </div>

                                <div
                                    class="col-lg-5 col-md-12 col-sm-12 py-1 pr-30 mr-5 sld"
                                >
                                    <div class="main-search-field-2">
                                        <div class="range-slider">
                                            <label>{{ trans("Area Size") }}</label>
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
                                            <label>{{ trans("Price Range") }}</label>
                                            <div
                                                id="imas-hero-price-range"
                                                :data-min="priceBounds.min"
                                                :data-max="priceBounds.max"
                                                :data-unit="priceBounds.currency"
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
const advancedOpen = ref(false);
const selectedUnitTypeIds = ref([]);
const rangesDirty = ref(false);
const slidersReady = ref(false);

const propertySearch = computed(
    () =>
        page.props.property_search ??
        page.props.globals?.property_search ??
        {},
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

const unitTypesColumnA = computed(() => {
    const list = projectUnitTypes.value;
    const mid = Math.ceil(list.length / 2);
    return list.slice(0, mid);
});

const unitTypesColumnB = computed(() => {
    const list = projectUnitTypes.value;
    const mid = Math.ceil(list.length / 2);
    return list.slice(mid);
});

const priceRange = ref([0, 1]);
const areaRange = ref([0, 1]);

const includeAdvancedParams = computed(() => {
    if (selectedUnitTypeIds.value.length > 0) {
        return true;
    }

    return rangesDirty.value;
});

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

<style scoped>
.imas-hero-unit-col {
    padding-inline-end: 30px;
}

.imas-hero-property-search :deep(.single-select),
.imas-hero-property-search :deep(.nice-select) {
    text-align: start;
}

.imas-hero-property-search :deep(.nice-select .current) {
    text-align: start;
}

.imas-hero-property-search :deep(.explore__form-checkbox-list.full-filter) {
    width: 100%;
}

.imas-hero-property-search :deep(.checkboxes.one-in-row label) {
    width: 100%;
    text-align: start;
}

.hp-6 .dropdown-filter span::after {
    margin-left: 0;
    margin-inline-start: 15px;
}
</style>

<style>
/**
 * Theme hp-6 checkboxes position the faux box with physical `left` + `padding-left`.
 * In RTL that leaves labels on the right and boxes on the far left of each row.
 */
html[dir="rtl"] body.hp-6 .imas-hero-property-search .checkboxes.one-in-row label {
    padding-left: 0;
    padding-right: 28px;
    margin-right: 0;
}

html[dir="rtl"] body.hp-6 .imas-hero-property-search .checkboxes.one-in-row label::before {
    left: auto;
    right: 0;
    margin-right: 0;
    margin-left: 0;
}
</style>
