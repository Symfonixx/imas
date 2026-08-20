<template>
    <div
        v-if="units.length > 0"
        class="imas-featured-unit-areas"
        role="group"
        :aria-label="trans('properties.unit_types_aria')"
        aria-live="polite"
    >
        <i
            class="fa fa-building imas-featured-unit-areas__icon"
            aria-hidden="true"
        ></i>
        <div class="imas-featured-unit-areas__flip">
            <transition name="imas-featured-unit-area-flip" mode="out-in">
                <div
                    :key="activeIndex"
                    class="imas-featured-unit-areas__slide"
                >
                    <span class="imas-featured-unit-areas__name">{{
                        activeUnit.name
                    }}</span>
                    <span
                        v-if="activeUnit.area"
                        class="imas-featured-unit-areas__sep"
                        aria-hidden="true"
                    >→</span>
                    <span
                        v-if="activeUnit.area"
                        class="imas-featured-unit-areas__area"
                        dir="ltr"
                    >{{ activeUnit.area }}</span>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { prefersReducedMotion } from "@/plugins/gsap";
import { unitTypeDisplayParts } from "../utils/propertyUnitType.js";

const props = defineProps({
    unitTypes: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const trans = (key) => page.props.translations[key] || key;

const activeIndex = ref(0);
let rotateTimer = null;

/** Unit types with a displayable name (skip empty labels). */
const units = computed(() =>
    (Array.isArray(props.unitTypes) ? props.unitTypes : []).filter((ut) => {
        const name = unitTypeDisplayParts(ut).name;
        return typeof name === "string" && name.trim() !== "" && name !== "—";
    }),
);

const activeUnit = computed(() =>
    unitTypeDisplayParts(units.value[activeIndex.value] ?? units.value[0]),
);

function clearRotateTimer() {
    if (rotateTimer !== null) {
        clearInterval(rotateTimer);
        rotateTimer = null;
    }
}

function startRotateTimer() {
    clearRotateTimer();
    activeIndex.value = 0;

    if (units.value.length <= 1 || prefersReducedMotion()) {
        return;
    }

    rotateTimer = setInterval(() => {
        activeIndex.value = (activeIndex.value + 1) % units.value.length;
    }, 3000);
}

watch(
    () => props.unitTypes,
    () => startRotateTimer(),
    { deep: true },
);

onMounted(() => startRotateTimer());
onBeforeUnmount(() => clearRotateTimer());
</script>

<style scoped lang="scss">
.imas-featured-unit-areas {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 0.45rem;
    width: 100%;
    margin: 0;
    padding: 0;
    background: transparent;
    color: inherit;
    font-size: var(--text-sm);
    line-height: 1.35;
}

.imas-featured-unit-areas__icon {
    flex-shrink: 0;
    color: var(--brand-gold);
    font-size: 0.95rem;
    line-height: 1;
}

.imas-featured-unit-areas__flip {
    position: relative;
    overflow: hidden;
    flex: 1;
    min-width: 0;
    min-height: 1.35em;
}

.imas-featured-unit-areas__slide {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 0.3rem;
    width: 100%;
    max-width: 100%;
    color: var(--text);
    font-weight: 600;
    line-height: 1.35;
}

.imas-featured-unit-areas__name {
    flex-shrink: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.imas-featured-unit-areas__sep {
    flex-shrink: 0;
    display: inline-block;
    color: var(--brand-gold);
    font-weight: 700;
    line-height: 1;
}

html[dir="rtl"] .imas-featured-unit-areas__sep {
    transform: rotate(180deg);
}

.imas-featured-unit-areas__area {
    flex-shrink: 0;
    white-space: nowrap;
    unicode-bidi: isolate;
}

.imas-featured-unit-area-flip-enter-active,
.imas-featured-unit-area-flip-leave-active {
    display: flex !important;
    align-items: center;
    justify-content: flex-start;
    gap: 0.3rem;
    transition:
        transform 0.4s cubic-bezier(0.4, 0, 0.2, 1),
        opacity 0.35s ease;
}

.imas-featured-unit-area-flip-leave-active {
    position: absolute;
    inset: 0;
    width: 100%;
}

.imas-featured-unit-area-flip-leave-to {
    transform: translateY(-100%);
    opacity: 0;
}

.imas-featured-unit-area-flip-enter-from {
    transform: translateY(100%);
    opacity: 0;
}

.imas-featured-unit-area-flip-enter-to {
    transform: translateY(0);
    opacity: 1;
}

@media (prefers-reduced-motion: reduce) {
    .imas-featured-unit-area-flip-enter-active,
    .imas-featured-unit-area-flip-leave-active {
        transition: none;
    }

    .imas-featured-unit-area-flip-leave-to,
    .imas-featured-unit-area-flip-enter-from {
        transform: none;
        opacity: 1;
    }
}
</style>
