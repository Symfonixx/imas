<template>
    <section class="headings-2 pt-0">
        <div class="pro-wrapper">
            <div class="detail-wrapper-body">
                <div class="listing-title-bar">
                    <div class="text-heading text-left">
                        <p class="font-weight-bold mb-0 mt-3">
                            {{ resultsLabel }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                class="cod-pad single detail-wrapper mr-2 mt-0 d-flex justify-content-md-end align-items-center grid flex-wrap"
            >
                <div class="input-group border rounded input-group-lg w-auto  mb-2 mb-md-0">
                    <label
                        class="input-group-text bg-transparent border-0 text-uppercase letter-spacing-093"
                        :for="sortToggleId"
                    >
                        <i class="fas fa-align-left fs-16 px-2"></i>{{ trans("listing_page.sort_by") }}:
                    </label>
                    <!--
                      Theme markup uses bootstrap-select's `.selectpicker` (jQuery plugin).
                      That script is not loaded on our Inertia shell, so a native <select>
                      stays OS-styled. Replicate the bootstrap-select UI with a Vue dropdown.
                    -->
                    <div
                        ref="sortRootRef"
                        class="imas-sort-bs position-relative flex-grow-1"
                        :class="{ 'imas-sort-bs--open': sortMenuOpen }"
                    >
                        <button
                            :id="sortToggleId"
                            type="button"
                            class="imas-sort-bs__toggle d-flex align-items-center justify-content-between w-100 border-0 bg-transparent shadow-none pl-0  text-left"
                            :aria-expanded="sortMenuOpen"
                            aria-haspopup="listbox"
                            @click.stop="sortMenuOpen = !sortMenuOpen"
                        >
                            <span class="imas-sort-bs__label text-truncate">{{
                                currentSortLabel
                            }}</span>
                            <i
                                class="fas pl-2 flex-shrink-0"
                                :class="
                                    sortMenuOpen ? 'fa-angle-up' : 'fa-angle-down'
                                "
                                aria-hidden="true"
                            ></i>
                        </button>
                        <ul
                            v-show="sortMenuOpen"
                            class="imas-sort-bs__menu dropdown-menu show"
                            role="listbox"
                            @click.stop
                        >
                            <li v-for="opt in sortOptions" :key="opt.value" role="none">
                                <button
                                    type="button"
                                    class="dropdown-item border-0 bg-transparent text-left w-100"
                                    :class="{
                                        active: props.sort === opt.value,
                                    }"
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
            </div>
        </div>
    </section>
</template>

<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
} from "vue";
import { router, usePage } from "@inertiajs/vue3";

const props = defineProps({
    properties: { type: Object, required: true },
    filters: { type: Object, required: true },
    sort: { type: String, required: true },
});

const page = usePage();
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
console.log(props.properties);

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
    router.get(route("property.index"), buildQuery({ sort: value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
    });
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

<style scoped>
.imas-sort-bs__toggle {
    min-height: 3rem;
    font-weight: 600;
    font-size: 1rem;
    line-height: 1.35;
    color: #707070 !important;
    background: transparent !important;
    white-space: nowrap;
    cursor: pointer;
    border-radius: 0;
}

.imas-sort-bs__toggle:hover,
.imas-sort-bs__toggle:focus {
    color: #505050 !important;
    background: transparent !important;
    outline: none;
    box-shadow: none;
}

.imas-sort-bs__label {
    color: inherit;
    font-weight: normal;
}

.imas-sort-bs__toggle .fas {
    color: #707070;
}

.imas-sort-bs__menu {
    
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    margin-top: 0.25rem;
    z-index: 10050;
    min-width: 100%;
    padding: 0 0;
    font-size: 0.95rem;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 0.3rem;
    box-shadow:
        0 0.5rem 1rem rgba(0, 0, 0, 0.08),
        0 0.15rem 0.35rem rgba(0, 0, 0, 0.04);
    background: #fff;
}

.imas-sort-bs__menu .dropdown-item {
    padding: 0.55rem 1rem;
    color: #555;
    cursor: pointer;
    transition:
        background-color 0.15s ease,
        color 0.15s ease;
}

/* Brand gold on option hover/focus — not on the closed trigger */
.imas-sort-bs__menu .dropdown-item:hover,
.imas-sort-bs__menu .dropdown-item:focus {
    background-color: var(--color-action-primary) !important;
    color: white !important;
}

.imas-sort-bs__menu .dropdown-item.active {
    font-weight: 700;
    color: var(--brand-navy, #1a2a4a) !important;
    background: transparent !important;
}

.imas-sort-bs__menu .dropdown-item.active:hover,
.imas-sort-bs__menu .dropdown-item.active:focus {
    background-color: var(--color-action-primary) !important;
    color: white !important;
}

.imas-sort-bs__menu li {
    list-style: none;
    margin: 0;
    padding: 0;
}
html[dir="rtl"] .single.detail-wrapper{
    margin-left: 0 !important;
}

 html[dir="rtl"] .input-group-text{
    padding:0;
}
</style>
