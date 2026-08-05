<template>
    <div
        v-if="names.length > 0"
        class="imas-featured-unit-areas"
        :aria-label="trans('properties.unit_types_aria')"
        aria-live="polite"
    >
        <div class="imas-featured-unit-areas__flip">
            <transition name="imas-featured-unit-area-flip" mode="out-in">
                <span
                    :key="activeIndex"
                    class="imas-featured-unit-areas__value"
                >{{ activeName }}</span>
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

/** Unit type names only (skip empty labels). */
const names = computed(() =>
    (Array.isArray(props.unitTypes) ? props.unitTypes : [])
        .map((ut) => unitTypeDisplayParts(ut).name)
        .filter((name) => typeof name === "string" && name.trim() !== "" && name !== "—"),
);

const activeName = computed(
    () => names.value[activeIndex.value] ?? names.value[0] ?? "",
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

    if (names.value.length <= 1 || prefersReducedMotion()) {
        return;
    }

    rotateTimer = setInterval(() => {
        activeIndex.value = (activeIndex.value + 1) % names.value.length;
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
    display: block;
    width: 100%;
    margin: 0;
    padding: 0;
    background: transparent;
    color: inherit;
    font-size: var(--text-sm);
    line-height: 1.35;
}

.imas-featured-unit-areas__flip {
    position: relative;
    overflow: hidden;
    width: 100%;
    min-height: 1.35em;
}

.imas-featured-unit-areas__value {
    display: block;
    width: 100%;
    color: var(--text);
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.imas-featured-unit-area-flip-enter-active,
.imas-featured-unit-area-flip-leave-active {
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
