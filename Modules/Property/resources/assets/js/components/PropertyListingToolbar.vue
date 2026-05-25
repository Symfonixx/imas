<template>
    <div
        ref="sortRootRef"
        class="imas-property-listings-toolbar"
        :class="{ 'imas-property-listings-toolbar--open': sortMenuOpen }"
    >
        <p class="imas-property-listings-toolbar__results text-dim">
            {{ resultsLabel }}
        </p>
        <div class="imas-property-listings-toolbar__sort">
            <label
                class="imas-property-listings-toolbar__sort-label text-dim"
                :for="sortToggleId"
            >
                <i class="fas fa-align-left" aria-hidden="true"></i>
                {{ trans("listing_page.sort_by") }}:
            </label>
            <button
                :id="sortToggleId"
                type="button"
                class="imas-property-listings-toolbar__sort-toggle"
                :aria-expanded="sortMenuOpen"
                aria-haspopup="listbox"
                @click.stop="sortMenuOpen = !sortMenuOpen"
            >
                <span class="imas-property-listings-toolbar__sort-value">{{
                    currentSortLabel
                }}</span>
                <i
                    class="fas"
                    :class="sortMenuOpen ? 'fa-angle-up' : 'fa-angle-down'"
                    aria-hidden="true"
                ></i>
            </button>
            <ul
                v-show="sortMenuOpen"
                class="imas-property-listings-toolbar__sort-menu"
                role="listbox"
                @click.stop
            >
                <li v-for="opt in sortOptions" :key="opt.value" role="none">
                    <button
                        type="button"
                        class="imas-property-listings-toolbar__sort-option"
                        :class="{ 'is-active': props.sort === opt.value }"
                        role="option"
                        :aria-selected="props.sort === opt.value"
                        @click="pickSort(opt.value)"
                    >
                        {{ trans(opt.labelKey) }}
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
} from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { localizedRoute } from "@/utils/localizedRoute.js";

const props = defineProps({
    properties: { type: Object, required: true },
    filters: { type: Object, required: true },
    sort: { type: String, required: true },
});

const page = usePage();
const activeLocale = computed(() => page.props.locale || "en");
const sortToggleId = `property-sort-${Math.random().toString(36).slice(2, 9)}`;

const sortMenuOpen = ref(false);
const sortRootRef = ref(null);

const sortOptions = [
    { value: "price_asc", labelKey: "listing_page.price_low_high" },
    { value: "price_desc", labelKey: "listing_page.price_high_low" },
];

function trans(key) {
    return page.props.translations[key] || key;
}

const currentSortLabel = computed(() => {
    const opt = sortOptions.find((o) => o.value === props.sort);
    return opt ? trans(opt.labelKey) : trans("listing_page.price_low_high");
});

const resultsLabel = computed(() => {
    const tpl = trans("listing_page.results_count");
    const n = props.properties?.total ?? 0;
    return tpl.replace(":count", String(n));
});

function buildQuery(overrides = {}) {
    const q = {
        sort: props.sort,
        q: props.filters.q ?? undefined,
        location_id: props.filters.location_id ?? undefined,
        property_type_id: props.filters.property_type_id ?? undefined,
        ...overrides,
    };
    Object.keys(q).forEach((k) => {
        if (q[k] === null || q[k] === undefined || q[k] === "") {
            delete q[k];
        }
    });
    return q;
}

function pickSort(value) {
    sortMenuOpen.value = false;
    if (value === props.sort) {
        return;
    }
    router.get(
        localizedRoute(
            "property.index",
            {},
            activeLocale.value,
            "/property",
        ),
        buildQuery({ sort: value, page: 1 }),
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function onDocClick(event) {
    const root = sortRootRef.value;
    if (!root || !sortMenuOpen.value) {
        return;
    }
    if (root.contains(event.target)) {
        return;
    }
    sortMenuOpen.value = false;
}

onMounted(() => {
    document.addEventListener("click", onDocClick);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", onDocClick);
});
</script>
