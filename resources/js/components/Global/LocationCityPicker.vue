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

        <Teleport to="body">
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
                        <div class="imas-loc-picker__column-head imas-loc-picker__column-head--static">
                            <span class="imas-loc-picker__column-head-main">
                                <span class="imas-loc-picker__column-title">{{
                                    trans("Cities")
                                }}</span>
                                <span
                                    v-if="selected.length"
                                    class="imas-loc-picker__column-count"
                                    >{{ selected.length }}</span
                                >
                            </span>
                        </div>
                        <div class="imas-loc-picker__grid">
                            <label
                                v-for="city in cities"
                                :key="`c-${city.id}`"
                                class="imas-loc-picker__item"
                                :class="{
                                    'is-checked': isSelected(city.id),
                                }"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isSelected(city.id)"
                                    @change="toggleValue(city.id)"
                                />
                                <span class="imas-loc-picker__item-label">{{
                                    city.name
                                }}</span>
                            </label>
                            <p
                                v-if="!cities.length"
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
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useLocationPickerPanel } from "@/composables/useLocationPickerPanel.js";

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    cities: { type: Array, default: () => [] },
    name: { type: String, default: "location_id[]" },
    placeholder: { type: String, default: "" },
    layout: {
        type: String,
        default: "hero",
        validator: (value) => ["hero", "sidebar"].includes(value),
    },
});

const emit = defineEmits(["update:modelValue"]);

const page = usePage();

const {
    rootRef,
    triggerRef,
    panelRef,
    open,
    useMobilePanel,
    panelStyle,
    toggle,
} = useLocationPickerPanel(() => props.layout);

function trans(key) {
    return page.props.translations?.[key] || key;
}

const selected = computed(() => props.modelValue.map((v) => String(v)));

const triggerLabel = computed(() => {
    const count = selected.value.length;
    if (count === 0) {
        return props.placeholder || trans("Cities");
    }
    if (count === 1) {
        const match = props.cities.find(
            (x) => String(x.id) === selected.value[0],
        );
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

function handleToggle() {
    toggle();
}
</script>

<style lang="scss" scoped>
@import "./location-picker.scss";

.imas-loc-picker__column-head--static {
    cursor: default;
    pointer-events: none;
}
</style>
