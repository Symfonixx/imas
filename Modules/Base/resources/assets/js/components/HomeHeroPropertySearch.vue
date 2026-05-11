<template>
    <div class="banner-search-wrap imas-hero-property-search">
        <form class="tab-content" method="get" :action="action">
            <input type="hidden" name="purpose" :value="purpose" />

            <div class="tab-pane fade show active">
                <div class="rld-main-search">
                    <div class="imas-hero-property-search__row">
                        <div class="d-flex">
                            <div
                                class="rld-single-input imas-hero-property-search__field imas-hero-property-search__field--keyword"
                            >
                                <input
                                    v-model="searchKeyword"
                                    type="search"
                                    name="q"
                                    autocomplete="off"
                                    class="imas-hero-property-search__control"
                                    :placeholder="trans('Enter Keyword...')"
                                />
                            </div>

                            <div
                                class="rld-single-select imas-hero-property-search__field"
                            >
                                <select
                                    v-model="searchPropertyTypeId"
                                    class="select single-select wide imas-hero-property-search__control"
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
                                class="rld-single-select imas-hero-property-search__field"
                            >
                                <select
                                    v-model="searchLocationId"
                                    class="select single-select wide imas-hero-property-search__control"
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
                        </div>
                        <div class="imas-hero-property-search__actions">
                            <button
                                type="submit"
                                class="btn btn-yellow btn-block !text-white imas-hero-property-search__submit"
                            >
                                {{ trans("Search Now") }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";

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

function trans(key) {
    return page.props.translations[key] || key;
}
</script>

<style scoped>
.imas-hero-property-search__row {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    gap: 0.75rem 1rem;
    margin-inline: 0;
}

.imas-hero-property-search__field {
    flex: 1 1 10rem;
    min-width: 0;
}

.imas-hero-property-search__field--keyword {
    flex: 2 1 14rem;
}

.imas-hero-property-search__actions {
    flex: 0 0 auto;
    display: flex;
    align-items: stretch;
    min-width: 0;
}

@media (min-width: 992px) {
    .imas-hero-property-search__actions {
        margin-inline-start: auto;
    }
}

@media (max-width: 991px) {
    .imas-hero-property-search__actions {
        flex: 1 1 100%;
        justify-content: center;
        margin-top: 0.25rem;
    }

    .imas-hero-property-search__submit {
        width: auto !important;
        min-width: 12.5rem;
    }
}

.imas-hero-property-search__control {
    text-align: start;
    width: 100%;
}

/* Nice Select / theme wrappers: keep text aligned to logical start */
.imas-hero-property-search :deep(.single-select),
.imas-hero-property-search :deep(.nice-select) {
    text-align: start;
}

.imas-hero-property-search :deep(.nice-select .current) {
    text-align: start;
}
</style>
