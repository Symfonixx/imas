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
            @click="toggle"
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
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";

const MOBILE_PANEL_MQ = "(max-width: 991.98px)";
const DESKTOP_PANEL_WIDTH = 230;

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
const rootRef = ref(null);
const triggerRef = ref(null);
const panelRef = ref(null);
const open = ref(false);
const expandedSections = ref({
    areas: true,
    districts: true,
});
const useMobilePanel = ref(false);
const panelStyle = ref({});
let mobileMq = null;

function isRtlDocument() {
    return (
        document.documentElement.getAttribute("dir") === "rtl" ||
        page.props.text_direction === "rtl" ||
        page.props.locale === "ar"
    );
}

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
        return props.placeholder || trans("Municipalities");
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

function toggle() {
    open.value = !open.value;
    if (open.value) {
        expandAllSections();
        schedulePanelPositionUpdate();
    } else {
        panelStyle.value = {};
    }
}

function syncMobilePanelMode() {
    useMobilePanel.value =
        typeof window !== "undefined" &&
        window.matchMedia(MOBILE_PANEL_MQ).matches;
    if (open.value) {
        schedulePanelPositionUpdate();
    }
}

function schedulePanelPositionUpdate() {
    nextTick(() => {
        updatePanelPosition();
        requestAnimationFrame(updatePanelPosition);
    });
}

function resolvePanelWidth(triggerWidth, viewportMargin) {
    const base = Math.round(triggerWidth);
    const widened = Math.max(Math.round(base * 1.4), base + 48);

    return Math.min(widened, window.innerWidth - viewportMargin, 352);
}

function resolveDesktopPanelWidth(triggerRect, viewportMargin) {
    if (props.layout === "sidebar") {
        return Math.min(
            Math.round(triggerRect.width),
            window.innerWidth - viewportMargin,
        );
    }

    return Math.min(DESKTOP_PANEL_WIDTH, window.innerWidth - viewportMargin);
}

function updatePanelPosition() {
    if (!open.value || !triggerRef.value) {
        panelStyle.value = {};
        return;
    }

    const triggerRect = triggerRef.value.getBoundingClientRect();
    const margin = 12;
    const top = `${Math.round(triggerRect.bottom + 6)}px`;
    const viewportMargin =
        useMobilePanel.value && window.innerWidth <= 576 ? 24 : margin * 2;

    if (useMobilePanel.value) {
        const panelWidth = resolvePanelWidth(
            triggerRect.width,
            viewportMargin,
        );
        panelStyle.value = {
            position: "fixed",
            top,
            left: "50%",
            right: "auto",
            transform: "translateX(-50%)",
            width: `${panelWidth}px`,
            maxWidth: `calc(100vw - ${viewportMargin}px)`,
        };
        return;
    }

    const panelWidth = resolveDesktopPanelWidth(triggerRect, margin * 2);
    const isRtl = isRtlDocument();

    if (isRtl) {
        let right = window.innerWidth - triggerRect.right;
        const maxRight = window.innerWidth - panelWidth - margin;
        right = Math.min(Math.max(right, margin), maxRight);

        panelStyle.value = {
            position: "fixed",
            top,
            right: `${Math.round(right)}px`,
            left: "auto",
            width: `${panelWidth}px`,
            maxWidth: `${panelWidth}px`,
            transform: "none",
        };
        return;
    }

    let left = triggerRect.left;
    left = Math.max(
        margin,
        Math.min(left, window.innerWidth - panelWidth - margin),
    );

    panelStyle.value = {
        position: "fixed",
        top,
        left: `${Math.round(left)}px`,
        right: "auto",
        width: `${panelWidth}px`,
        maxWidth: `${panelWidth}px`,
        transform: "none",
    };
}

function onViewportChange() {
    syncMobilePanelMode();
    updatePanelPosition();
}

function onOutsideClick(event) {
    if (!open.value) {
        return;
    }
    const root = rootRef.value;
    const panel = panelRef.value;
    if (
        (root && root.contains(event.target)) ||
        (panel && panel.contains(event.target))
    ) {
        return;
    }
    open.value = false;
}

function onKeydown(event) {
    if (event.key === "Escape" && open.value) {
        open.value = false;
    }
}

onMounted(() => {
    document.addEventListener("click", onOutsideClick, true);
    document.addEventListener("keydown", onKeydown);
    window.addEventListener("resize", onViewportChange);
    window.addEventListener("scroll", onViewportChange, true);

    if (typeof window !== "undefined") {
        mobileMq = window.matchMedia(MOBILE_PANEL_MQ);
        syncMobilePanelMode();
        mobileMq.addEventListener("change", syncMobilePanelMode);
    }
});

onBeforeUnmount(() => {
    document.removeEventListener("click", onOutsideClick, true);
    document.removeEventListener("keydown", onKeydown);
    window.removeEventListener("resize", onViewportChange);
    window.removeEventListener("scroll", onViewportChange, true);
    mobileMq?.removeEventListener("change", syncMobilePanelMode);
});

watch(open, (isOpen) => {
    if (isOpen) {
        expandAllSections();
        schedulePanelPositionUpdate();
    } else {
        panelStyle.value = {};
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
.imas-loc-picker {
    position: relative;
    width: 100%;
    text-align: start;
}

.imas-loc-picker__trigger {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
    min-height: 50px;
    padding: 0 14px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--surface-2);
    color: var(--text);
    font-size: 0.95rem;
    line-height: 1.2;
    cursor: pointer;
    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease;
}

.imas-loc-picker__trigger:hover,
.imas-loc-picker.is-open .imas-loc-picker__trigger {
    border-color: var(--brand-gold);
}

.imas-loc-picker__trigger:focus-visible {
    outline: none;
    border-color: var(--brand-gold);
    box-shadow: var(--ring);
}

.imas-loc-picker__trigger-label {
    flex: 1 1 auto;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    text-align: start;
}

.imas-loc-picker__caret {
    flex: 0 0 auto;
    color: var(--text-dim);
}

.imas-loc-picker__panel {
    z-index: 1200;
    width: 230px;
    min-width: 10rem;
    max-width: min(230px, calc(100vw - 24px));
    padding: 0.85rem 0.95rem;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: var(--surface);
    box-shadow: var(--shadow-lg);
    box-sizing: border-box;
    text-align: start;
}

.imas-loc-picker__columns {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.imas-loc-picker__section {
    min-width: 0;
}

.imas-loc-picker__column-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    width: 100%;
    margin: 0;
    padding: 0 0 0.45rem;
    border: 0;
    border-bottom: 1px solid var(--divider);
    background: transparent;
    color: inherit;
    cursor: pointer;
    text-align: start;
}

.imas-loc-picker__column-head:hover .imas-loc-picker__column-title {
    color: var(--brand-gold);
}

.imas-loc-picker__column-head:focus-visible {
    outline: none;
    box-shadow: var(--ring);
    border-radius: 4px;
}

.imas-loc-picker__column-head-main {
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
    gap: 0.4rem;
    min-width: 0;
    text-align: start;
}

.imas-loc-picker__column-title {
    font-weight: 600;
    color: var(--text);
    text-align: start;
}

.imas-loc-picker__column-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    height: 1.25rem;
    padding: 0 0.35rem;
    border-radius: 999px;
    background: var(--brand-gold);
    color: var(--text-on-gold);
    font-size: 0.7rem;
    font-weight: 700;
}

.imas-loc-picker__section-caret {
    flex: 0 0 auto;
    color: var(--text-dim);
    font-size: 0.9rem;
}

.imas-loc-picker__grid {
    display: grid;
    grid-template-columns: 1fr;
    row-gap: 0.5rem;
    max-height: 12rem;
    margin-top: 0.65rem;
    overflow-y: auto;
    padding-inline-end: 0.35rem;
    text-align: start;
    scrollbar-width: thin;
    scrollbar-color: var(--surface-3) transparent;

    &::-webkit-scrollbar {
        width: 4px;
    }

    &::-webkit-scrollbar-track {
        background: transparent;
    }

    &::-webkit-scrollbar-thumb {
        background: var(--surface-3);
        border-radius: 4px;
    }

    &::-webkit-scrollbar-thumb:hover {
        background: var(--brand-gold);
    }
}

.imas-loc-picker__item {
    display: flex;
    align-items: flex-start;
    gap: 0.45rem;
    margin: 0;
    padding: 0.45rem 0.55rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--surface-2);
    color: var(--text);
    font-size: 0.85rem;
    line-height: 1.35;
    cursor: pointer;
    text-align: start;
    transition:
        background 0.12s ease,
        border-color 0.12s ease;
}

html[dir="rtl"] .imas-loc-picker__item {
    flex-direction: row-reverse;
}

.imas-loc-picker__item:hover {
    border-color: var(--brand-gold);
}

.imas-loc-picker__item.is-checked {
    border-color: var(--brand-gold);
    background: rgba(217, 168, 0, 0.12);
}

.imas-loc-picker__item input[type="checkbox"] {
    flex: 0 0 auto;
    width: 0.95rem;
    height: 0.95rem;
    margin: 0.1rem 0 0;
    accent-color: var(--brand-gold);
    cursor: pointer;
}

.imas-loc-picker__item-label {
    flex: 1 1 auto;
    min-width: 0;
    white-space: normal;
    word-break: break-word;
    overflow-wrap: anywhere;
}

.imas-loc-picker__empty {
    grid-column: 1 / -1;
    margin: 0;
    font-size: 0.85rem;
}

.imas-loc-picker__footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 0.85rem;
    padding-top: 0.65rem;
    border-top: 1px solid var(--divider);
}

.imas-loc-picker__clear {
    border: 1px solid var(--brand-gold);
    border-radius: 6px;
    background: transparent;
    color: var(--brand-gold);
    padding: 0.35rem 0.9rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background 0.12s ease,
        color 0.12s ease;
}

.imas-loc-picker__clear:hover {
    background: var(--brand-gold);
    color: var(--text-on-gold);
}

.imas-loc-picker.is-open {
    z-index: 1100;
}

.imas-loc-picker__panel--sidebar {
    width: auto;
    max-width: min(100%, calc(100vw - 24px));
}

.imas-loc-picker__panel--mobile {
    max-height: min(32rem, calc(100vh - 5rem));
    overflow-y: auto;
}
</style>
