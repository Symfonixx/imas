<template>
    <div class="widget">
        <div class="widget-boxed main-search-field">
            <div class="widget-boxed-header">
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
                                    :placeholder="trans('listing_page.enter_keyword')"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="form-group location">
                        <label class="sr-only" for="filter-location-id">{{
                            trans("listing_page.location")
                        }}</label>
                        <select
                            id="filter-location-id"
                            v-model="locationId"
                            class="form-control wide"
                        >
                            <option value="">
                                {{ trans("listing_page.all_locations") }}
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
                    <div class="form-group categories">
                        <label class="sr-only" for="filter-property-type-id">{{
                            trans("listing_page.property_type")
                        }}</label>
                        <select
                            id="filter-property-type-id"
                            v-model="propertyTypeId"
                            class="form-control wide"
                        >
                            <option value="">
                                {{ trans("listing_page.all_types") }}
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
import { ref, watch } from "vue";
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

syncFromFilters(props.filters);

watch(
    () => props.filters,
    (f) => syncFromFilters(f ?? {}),
    { deep: true },
);

function submit() {
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
.main-search-field{
    background-color: white !important;
}
</style>