<template>
    <div
        ref="rootRef"
        class="imas-loc-picker"
        :class="{
            'is-open': open,
            'imas-loc-picker--sidebar': layout === 'sidebar',
        }"
    >
        <button
            ref="triggerRef"
            type="button"
            class="imas-loc-picker__trigger"
            :aria-expanded="open"
            aria-haspopup="listbox"
            @click="handleToggle"
        >
            <i class="fa fa-map-marker" aria-hidden="true"></i>
            <span class="imas-loc-picker__trigger-label">{{
                triggerLabel
            }}</span>
            <i
                class="fa imas-loc-picker__caret"
                :class="open ? 'fa-angle-up' : 'fa-angle-down'"
                aria-hidden="true"
            ></i>
        </button>

        <Teleport v-if="mounted" to="body">
            <div
                v-show="open"
                ref="panelRef"
                class="imas-loc-picker__panel"
                :class="{
                    'imas-loc-picker__panel--mobile': useMobilePanel,
                    'imas-loc-picker__panel--sidebar': layout === 'sidebar',
                }"
                :style="panelStyle"
            >
                <div class="imas-loc-picker__columns">
                    <div class="imas-loc-picker__section">
                        <button
                            type="button"
                            class="imas-loc-picker__column-head"
                            :aria-expanded="isSectionExpanded('areas')"
                            @click="toggleSection('areas')"
                        >
                            <span class="imas-loc-picker__column-head-main">
                                <span class="imas-loc-picker__column-title">{{
                                    trans("Areas")
                                }}</span>
                                <span
                                    v-if="areaSelectedCount"
                                    class="imas-loc-picker__column-count"
                                    >{{ areaSelectedCount }}</span
                                >
                            </span>
                            <i
                                class="fa imas-loc-picker__section-caret"
                                :class="
                                    isSectionExpanded('areas')
                                        ? 'fa-angle-up'
                                        : 'fa-angle-down'
                                "
                                aria-hidden="true"
                            ></i>
                        </button>
                        <div
                            v-show="isSectionExpanded('areas')"
                            class="imas-loc-picker__grid"
                        >
                            <label
                                v-for="a in areas"
                                :key="`a-${a.id}`"
                                class="imas-loc-picker__item"
                                :class="{
                                    'is-checked': isSelected(a.id),
                                }"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isSelected(a.id)"
                                    @change="toggleValue(a.id)"
                                />
                                <span class="imas-loc-picker__item-label">{{
                                    a.name
                                }}</span>
                            </label>
                            <p
                                v-if="!areas.length"
                                class="imas-loc-picker__empty text-dim"
                            >
                                {{ trans("No results found") }}
                            </p>
                        </div>
                    </div>

                    <div class="imas-loc-picker__section">
                        <button
                            type="button"
                            class="imas-loc-picker__column-head"
                            :aria-expanded="isSectionExpanded('districts')"
                            @click="toggleSection('districts')"
                        >
                            <span class="imas-loc-picker__column-head-main">
                                <span class="imas-loc-picker__column-title">{{
                                   
                                    trans("Municipalities")
                                }}</span>
                                <span
                                    v-if="districtSelectedCount"
                                    class="imas-loc-picker__column-count"
                                    >{{ districtSelectedCount }}</span
                                >
                            </span>
                            <i
                                class="fa imas-loc-picker__section-caret"
                                :class="
                                    isSectionExpanded('districts')
                                        ? 'fa-angle-up'
                                        : 'fa-angle-down'
                                "
                                aria-hidden="true"
                            ></i>
                        </button>
                        <div
                            v-show="isSectionExpanded('districts')"
                            class="imas-loc-picker__grid"
                        >
                            <label
                                v-for="d in districts"
                                :key="`d-${d.id}`"
                                class="imas-loc-picker__item"
                                :class="{
                                    'is-checked': isSelected(d.id),
                                }"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isSelected(d.id)"
                                    @change="toggleValue(d.id)"
                                />
                                <span class="imas-loc-picker__item-label">{{
                                    d.name
                                }}</span>
                            </label>
                            <p
                                v-if="!districts.length"
                                class="imas-loc-picker__empty text-dim"
                            >
                                {{ trans("No results found") }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="selected.length" class="imas-loc-picker__footer">
                    <button
                        type="button"
                        class="imas-loc-picker__clear"
                        @click="clearAll"
                    >
                        {{ trans("Clear") }}
                    </button>
                </div>
            </div>
        </Teleport>

        <input
            v-for="id in selected"
            :key="`hidden-${id}`"
            type="hidden"
            :name="name"
            :value="id"
        />
    </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useLocationPickerPanel } from "@/composables/useLocationPickerPanel.js";

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    districts: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
    name: { type: String, default: "location_id[]" },
    placeholder: { type: String, default: "" },
    /** hero: fixed 230px panel on desktop; sidebar: match trigger width */
    layout: {
        type: String,
        default: "hero",
        validator: (value) => ["hero", "sidebar"].includes(value),
    },
});

const emit = defineEmits(["update:modelValue"]);

const page = usePage();
const expandedSections = ref({
    areas: true,
    districts: true,
});

const {
    rootRef,
    triggerRef,
    panelRef,
    open,
    useMobilePanel,
    panelStyle,
    mounted,
    toggle,
    schedulePanelPositionUpdate,
} = useLocationPickerPanel(() => props.layout);

function trans(key) {
    return page.props.translations?.[key] || key;
}

const selected = computed(() => props.modelValue.map((v) => String(v)));

const districtIds = computed(
    () => new Set(props.districts.map((d) => String(d.id))),
);
const areaIds = computed(() => new Set(props.areas.map((a) => String(a.id))));

const districtSelectedCount = computed(
    () => selected.value.filter((id) => districtIds.value.has(id)).length,
);
const areaSelectedCount = computed(
    () => selected.value.filter((id) => areaIds.value.has(id)).length,
);

const triggerLabel = computed(() => {
    const count = selected.value.length;
    if (count === 0) {
        return props.placeholder || trans("Location");
    }
    if (count === 1) {
        const all = [...props.districts, ...props.areas];
        const match = all.find((x) => String(x.id) === selected.value[0]);
        if (match?.name) {
            return match.name;
        }
    }
    return `${count} ${trans("selected")}`;
});

function isSelected(id) {
    return selected.value.includes(String(id));
}

function toggleValue(id) {
    const value = String(id);
    const next = selected.value.includes(value)
        ? selected.value.filter((v) => v !== value)
        : [...selected.value, value];
    emit("update:modelValue", next);
}

function clearAll() {
    emit("update:modelValue", []);
}

function expandAllSections() {
    expandedSections.value = {
        areas: true,
        districts: true,
    };
}

function isSectionExpanded(section) {
    return expandedSections.value[section] === true;
}

function toggleSection(section) {
    expandedSections.value = {
        ...expandedSections.value,
        [section]: !expandedSections.value[section],
    };
    if (open.value) {
        schedulePanelPositionUpdate();
    }
}

function handleToggle() {
    toggle(expandAllSections);
}

watch(open, (isOpen) => {
    if (isOpen) {
        expandAllSections();
        schedulePanelPositionUpdate();
    }
});

watch(
    expandedSections,
    () => {
        if (open.value) {
            schedulePanelPositionUpdate();
        }
    },
    { deep: true },
);
</script>

<style lang="scss" scoped>
@use "./location-picker.scss";
</style>
