<template>
    <div
        v-if="unitTypes.length > 0"
        class="imas-unit-types-bar text-base pb-3"
        role="group"
        :aria-label="trans('properties.unit_types_aria')"
    >
        <div class="imas-unit-types-bar__left">
            <i
                class="fa fa-building imas-unit-types-bar__icon"
                aria-hidden="true"
            ></i>
            <div class="imas-unit-types-flip" aria-live="polite">
                <transition name="imas-unit-flip" mode="out-in">
                    <div
                        :key="activeIndex"
                        class="imas-unit-types-flip__slide"
                    >
                        <span class="imas-unit-types-flip__name">{{
                            activeUnit.name
                        }}</span>
                        <span
                            v-if="activeUnit.area"
                            class="imas-unit-types-flip__sep"
                            aria-hidden="true"
                        >→</span>
                        <span
                            v-if="activeUnit.area"
                            class="imas-unit-types-flip__area"
                            dir="ltr"
                        >{{ activeUnit.area }}</span>
                    </div>
                </transition>
            </div>
        </div>
        <span class="imas-unit-types-bar__count">
            <i
                class="fa fa-circle imas-unit-types-bar__dot"
                aria-hidden="true"
            ></i>
            {{ countLabel }}
        </span>
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

const activeUnit = computed(() =>
    unitTypeDisplayParts(props.unitTypes[activeIndex.value]),
);

const countLabel = computed(() => {
    const n = props.unitTypes.length;
    if (n === 1) {
        return trans("properties.unit_types_count_one");
    }

    const template = trans("properties.unit_types_count");
    return template.includes(":count")
        ? template.replace(":count", String(n))
        : `${n} ${template}`;
});

function clearRotateTimer() {
    if (rotateTimer !== null) {
        clearInterval(rotateTimer);
        rotateTimer = null;
    }
}

function startRotateTimer() {
    clearRotateTimer();
    activeIndex.value = 0;

    if (props.unitTypes.length <= 1 || prefersReducedMotion()) {
        return;
    }

    rotateTimer = setInterval(() => {
        activeIndex.value =
            (activeIndex.value + 1) % props.unitTypes.length;
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
.imas-unit-types-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    min-height: 1.5em;
    line-height: 1.4;
    font-size: var(--text-base);
    color: var(--text-dim);
    direction: inherit;
}

.imas-unit-types-bar__left {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    flex: 1;
    min-width: 0;
    gap: 0.5rem;
}

.imas-unit-types-bar__icon {
    flex-shrink: 0;
    color: var(--brand-gold);
    font-size: 1.05rem;
    line-height: 1;
}

.imas-unit-types-flip {
    position: relative;
    overflow: hidden;
    flex: 1;
    min-width: 0;
    height: 1.4em;
}

.imas-unit-types-flip__slide {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 0.35rem;
    width: 100%;
    max-width: 100%;
    color: var(--text);
    font-size: var(--text-base);
    font-weight: 600;
    line-height: 1.4;
}

.imas-unit-types-flip__name {
    flex-shrink: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.imas-unit-types-flip__sep {
    flex-shrink: 0;
    display: inline-block;
    color: var(--brand-gold);
    font-weight: 700;
    line-height: 1;

}


html[dir="rtl"] .imas-unit-types-flip__sep {
    transform: rotate(180deg);
}

.imas-unit-types-flip__area {
    flex-shrink: 0;
    white-space: nowrap;
    unicode-bidi: isolate;
}

.imas-unit-types-bar__count {
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
    gap: 0.4rem;
    white-space: nowrap;
    color: var(--text);
    font-size: var(--text-base);
    font-weight: 600;
}

.imas-unit-types-bar__dot {
    flex-shrink: 0;
    color: var(--brand-gold);
    font-size: 0.4rem;
    line-height: 1;
}

:global(html[dir="rtl"]) .imas-unit-types-bar__count {
    flex-direction: row-reverse;
}

.imas-unit-flip-enter-active,
.imas-unit-flip-leave-active {
    display: flex !important;
    align-items: center;
    justify-content: flex-start;
    gap: 0.35rem;
    transition:
        transform 0.4s cubic-bezier(0.4, 0, 0.2, 1),
        opacity  0.35s ease;
}

.imas-unit-flip-leave-active {
    position: absolute;
    inset: 0;
    display: flex !important;
    align-items: center;
    justify-content: flex-start;
    gap: 0.35rem;
}

.imas-unit-flip-leave-to {
    transform: translateY(-100%);
    opacity: 0;
}

.imas-unit-flip-enter-from {
    transform: translateY(100%);
    opacity: 0;
}

.imas-unit-flip-enter-to {
    transform: translateY(0);
    opacity: 1;
}
</style>
