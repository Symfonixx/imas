<template>
    <div class="widget imas-property-filter-sidebar">
        <div class="widget-boxed main-search-field">
            <div class="widget-boxed-header d-flex justify-content-between align-items-center">
                <h4>{{ trans("listing_page.find_your_house") }}</h4>
            </div>
            <div class="trip-search">
                <form class="form" @submit.prevent="submit">
                    <div class="form-group looking">
                        <div class="first-select wide">
                            <div class="main-search-input-item">
                                <input
                                    v-model="q"
                                    type="text"
                                    class="form-control"
                                    :placeholder="
                                        trans('listing_page.enter_keyword')
                                    "
                                />
                            </div>
                        </div>
                    </div>
                    <div class="form-group location">
                        <span :id="locationLabelId" class="sr-only">{{
                            trans("listing_page.location")
                        }}</span>
                        <div
                            ref="locationNiceSelectRef"
                            class="nice-select form-control wide d-flex"
                            :class="{ open: locationOpen }"
                            tabindex="0"
                            role="combobox"
                            :aria-expanded="locationOpen"
                            :aria-labelledby="locationLabelId"
                            @click="onLocationNiceSelectClick"
                            @keydown="onLocationKeydown"
                        >
                            <span class="current"
                                ><i class="fa fa-map-marker"></i
                                >{{ currentLocationLabel }}</span
                            >
                            <ul class="list">
                                <li
                                    data-value=""
                                    class="option"
                                    :class="{
                                        selected: locationId === '',
                                    }"
                                    @click.stop="selectLocation('')"
                                >
                                    {{ trans("listing_page.all_locations") }}
                                </li>
                                <li
                                    v-for="c in cities"
                                    :key="c.id"
                                    :data-value="String(c.id)"
                                    class="option"
                                    :class="{
                                        selected: locationId === String(c.id),
                                    }"
                                    @click.stop="selectLocation(String(c.id))"
                                >
                                    {{ c.name }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group categories">
                        <span :id="propertyTypeLabelId" class="sr-only">{{
                            trans("listing_page.property_type")
                        }}</span>
                        <div
                            ref="propertyTypeNiceSelectRef"
                            class="nice-select form-control wide d-flex"
                            :class="{ open: propertyTypeOpen }"
                            tabindex="0"
                            role="combobox"
                            :aria-expanded="propertyTypeOpen"
                            :aria-labelledby="propertyTypeLabelId"
                            @click="onPropertyTypeNiceSelectClick"
                            @keydown="onPropertyTypeKeydown"
                        >
                            <span class="current"
                                ><i class="fa fa-home" aria-hidden="true"></i
                                >{{ currentPropertyTypeLabel }}</span
                            >
                            <ul class="list">
                                <li
                                    data-value=""
                                    class="option"
                                    :class="{
                                        selected: propertyTypeId === '',
                                    }"
                                    @click.stop="selectPropertyType('')"
                                >
                                    {{ trans("listing_page.all_types") }}
                                </li>
                                <li
                                    v-for="t in propertyTypes"
                                    :key="t.id"
                                    :data-value="String(t.id)"
                                    class="option"
                                    :class="{
                                        selected:
                                            propertyTypeId === String(t.id),
                                    }"
                                    @click.stop="
                                        selectPropertyType(String(t.id))
                                    "
                                >
                                    {{ t.name }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-12 no-pds">
                        <div class="at-col-default-mar">
                            <button
                                class="btn btn-default hvr-bounce-to-right"
                                type="submit"
                            >
                                {{ trans("listing_page.search") }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const props = defineProps({
    filters: { type: Object, required: true },
    sort: { type: String, required: true },
    cities: { type: Array, default: () => [] },
    propertyTypes: { type: Array, default: () => [] },
});

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}

function syncFromFilters(f) {
    q.value = f.q ?? "";
    locationId.value =
        f.location_id != null && f.location_id !== ""
            ? String(f.location_id)
            : "";
    propertyTypeId.value =
        f.property_type_id != null && f.property_type_id !== ""
            ? String(f.property_type_id)
            : "";
}

const q = ref("");
const locationId = ref("");
const propertyTypeId = ref("");

const locationLabelId = "filter-location-label";
const locationOpen = ref(false);
const locationNiceSelectRef = ref(null);

const currentLocationLabel = computed(() => {
    if (locationId.value === "") {
        return trans("listing_page.all_locations");
    }
    const c = props.cities.find((x) => String(x.id) === locationId.value);
    return c?.name ?? trans("listing_page.all_locations");
});

function onLocationNiceSelectClick(e) {
    if (e.target.closest(".option")) {
        return;
    }
    const next = !locationOpen.value;
    if (next) {
        propertyTypeOpen.value = false;
    }
    locationOpen.value = next;
}

function selectLocation(value) {
    locationId.value = value;
    locationOpen.value = false;
    propertyTypeOpen.value = false;
}

function onLocationKeydown(e) {
    if (e.key === "Escape" && locationOpen.value) {
        locationOpen.value = false;
        e.preventDefault();
        return;
    }
    if ((e.key === "Enter" || e.key === " ") && !locationOpen.value) {
        propertyTypeOpen.value = false;
        locationOpen.value = true;
        e.preventDefault();
    }
}

const propertyTypeLabelId = "filter-property-type-label";
const propertyTypeOpen = ref(false);
const propertyTypeNiceSelectRef = ref(null);

const currentPropertyTypeLabel = computed(() => {
    if (propertyTypeId.value === "") {
        return trans("listing_page.all_types");
    }
    const t = props.propertyTypes.find(
        (x) => String(x.id) === propertyTypeId.value,
    );
    return t?.name ?? trans("listing_page.all_types");
});

function onPropertyTypeNiceSelectClick(e) {
    if (e.target.closest(".option")) {
        return;
    }
    const next = !propertyTypeOpen.value;
    if (next) {
        locationOpen.value = false;
    }
    propertyTypeOpen.value = next;
}

function selectPropertyType(value) {
    propertyTypeId.value = value;
    propertyTypeOpen.value = false;
    locationOpen.value = false;
}

function onPropertyTypeKeydown(e) {
    if (e.key === "Escape" && propertyTypeOpen.value) {
        propertyTypeOpen.value = false;
        e.preventDefault();
        return;
    }
    if ((e.key === "Enter" || e.key === " ") && !propertyTypeOpen.value) {
        locationOpen.value = false;
        propertyTypeOpen.value = true;
        e.preventDefault();
    }
}

function closeNiceSelectsOnOutsideClick(e) {
    const loc = locationNiceSelectRef.value;
    const pt = propertyTypeNiceSelectRef.value;
    if (locationOpen.value && loc && !loc.contains(e.target)) {
        locationOpen.value = false;
    }
    if (propertyTypeOpen.value && pt && !pt.contains(e.target)) {
        propertyTypeOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener("click", closeNiceSelectsOnOutsideClick, true);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", closeNiceSelectsOnOutsideClick, true);
});

syncFromFilters(props.filters);

watch(
    () => props.filters,
    (f) => syncFromFilters(f ?? {}),
    { deep: true },
);

function submit() {
    locationOpen.value = false;
    propertyTypeOpen.value = false;
    const params = {
        sort: props.sort,
        page: 1,
    };
    const kw = q.value.trim();
    if (kw !== "") {
        params.q = kw;
    }
    if (locationId.value !== "") {
        params.location_id = locationId.value;
    }
    if (propertyTypeId.value !== "") {
        params.property_type_id = propertyTypeId.value;
    }
    router.get(route("property.index"), params, {
        preserveState: true,
        preserveScroll: false,
    });
}
</script>

<style scoped lang="scss">
/*
 * Theme `.widget-boxed` uses z-index: 90 + transform, so each sidebar card is its own
 * stacking context. This block must stack above the next `.widget-boxed` siblings
 * (e.g. featured properties) so open nice-select lists are not painted underneath.
 */
.imas-property-filter-sidebar {
    position: relative;
    z-index: 100;
}

.main-search-field {
    background-color: white !important;
}

/* Scrollable option lists (theme sets .nice-select .list { overflow: hidden }) */
.main-search-field :deep(.nice-select .list) {
    text-align: start;
    max-height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 1001;

    scrollbar-width: none;
    -ms-overflow-style: none;
    &::-webkit-scrollbar {
        display: none;
    }
}

.main-search-field :deep(.nice-select.open) {
    z-index: 1002;
}

/* Space between Font Awesome icon and label in the trigger */
.main-search-field :deep(.nice-select .current i) {
    display: inline-block;
    margin-inline-end: 0.5rem;
    vertical-align: -0.06em;
}
</style>

<style lang="scss">
html[dir="rtl"] .imas-property-filter-sidebar .nice-select {
    padding-left: 30px !important;
    padding-right: 18px !important;
    text-align: start !important;
}

html[dir="rtl"] .imas-property-filter-sidebar .nice-select::after {
    right: auto !important;
    left: 12px !important;
}

html[dir="rtl"] .imas-property-filter-sidebar .nice-select .option {
    padding-left: 29px !important;
    padding-right: 18px !important;
    text-align: start;
}
</style>
