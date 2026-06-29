<template>
    <section
        v-if="properties.length > 0"
        ref="sectionRef"
        class="featured portfolio bg-white-2 imas-home-section"
    >
        <div class="container">
            <div class="sec-title">
                <h2>{{ title }}</h2>
                <p>{{ subtitle }}</p>
            </div>
            <div class="imas-featured-rail">
                <div
                    ref="viewportRef"
                    dir="ltr"
                    class="row portfolio-items imas-featured-viewport"
                    :class="{ 'is-dragging': isDragging }"
                    @scroll.passive="onScroll"
                    @pointerdown="onPointerDown"
                    @pointermove="onPointerMove"
                    @pointerup="onPointerUp"
                    @pointercancel="onPointerUp"
                    @pointerleave="onPointerLeave"
                    @click.capture="onViewportClickCapture"
                >
                    <div
                        v-for="property in properties"
                        :key="property.id"
                        class="imas-featured-slide"
                        :dir="slideTextDir"
                    >
                        <PropertyCard
                            :property="property"
                            column-class=""
                        />
                    </div>
                </div>
                <ul
                    v-if="isMobileCarousel && pageCount > 1"
                    class="slick-dots imas-featured-dots"
                    role="tablist"
                    :aria-label="title"
                >
                    <li
                        v-for="i in pageCount"
                        :key="i - 1"
                        :class="{ 'slick-active': activePage === i - 1 }"
                        role="presentation"
                    >
                        <button
                            type="button"
                            :aria-label="`Slide ${i}`"
                            :aria-current="
                                activePage === i - 1 ? 'page' : undefined
                            "
                            @click.prevent="goToPage(i - 1)"
                        ></button>
                    </li>
                </ul>
            </div>
            <ReadMore
                :href="viewMoreHref"
                :text="trans('global.view_more')"
            />
        </div>
    </section>
</template>

<script setup>
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";
import { usePage } from "@inertiajs/vue3";
import ReadMore from "@/components/buttons/ReadMore.vue";
import { useScrollReveal } from "@/composables/useScrollReveal";

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}

/** RTL/LTR for card content; scroll rail stays `dir="ltr"` so scrollLeft is reliable. */
const slideTextDir = computed(() =>
    page.props.text_direction === "rtl" ? "rtl" : "ltr",
);

const viewMoreHref = computed(() => {
    try {
        if (typeof route === "function" && route().has?.("property.index")) {
            return route("property.index");
        }
    } catch {
        /* ignore */
    }

    return "/property";
});

const props = defineProps({
    properties: {
        type: Array,
        default: () => [],
    },
    title: {
        type: String,
        default: "Featured properties",
    },
    subtitle: {
        type: String,
        default: "We provide full service at every step.",
    },
});

const sectionRef = ref(null);

useScrollReveal(sectionRef, {
    preset: "home",
    variant: "cards",
    when: computed(() => props.properties.length > 0),
});

const MOBILE_MAX = 768;

const viewportRef = ref(null);
const isMobileCarousel = ref(false);
const activePage = ref(0);
const isDragging = ref(false);

const AXIS_LOCK_THRESHOLD = 8;

let dragPointerId = null;
let lastClientX = 0;
let dragStartX = 0;
let dragStartY = 0;
let dragAxisLocked = null;
let dragDistance = 0;

function resetPointerDrag() {
    isDragging.value = false;
    dragPointerId = null;
    dragAxisLocked = null;
}

function isInteractiveTarget(target) {
    if (!(target instanceof Element)) {
        return false;
    }

    return Boolean(
        target.closest(
            "a, button, input, textarea, select, label, [role='button']",
        ),
    );
}

function syncLayoutMode() {
    if (typeof window === "undefined") {
        return;
    }
    isMobileCarousel.value = window.innerWidth <= MOBILE_MAX;
}

const pageCount = computed(() => {
    if (!isMobileCarousel.value) {
        return 1;
    }
    const n = props.properties.length;
    return n > 1 ? n : 1;
});

function cumulativeOffsetBeforeSlide(vp, index) {
    const slides = vp.querySelectorAll(".imas-featured-slide");
    let left = 0;
    const stop = Math.min(index, slides.length);
    for (let i = 0; i < stop; i++) {
        left += slides[i].offsetWidth;
    }
    return left;
}

function scrollViewportToSlideIndex(vp, index, behavior = "smooth") {
    const offset = cumulativeOffsetBeforeSlide(vp, index);
    vp.scrollTo({ left: offset, behavior });
}

function goToPage(index) {
    if (!isMobileCarousel.value) {
        return;
    }
    const vp = viewportRef.value;
    if (!vp) {
        return;
    }
    requestAnimationFrame(() => {
        const slides = vp.querySelectorAll(".imas-featured-slide");
        const maxStart = Math.max(0, slides.length - 1);
        const i = Math.min(maxStart, Math.max(0, index));
        scrollViewportToSlideIndex(vp, i);
        activePage.value = i;
    });
}

function syncActiveFromScroll() {
    if (!isMobileCarousel.value) {
        return;
    }
    const vp = viewportRef.value;
    if (!vp) {
        return;
    }
    const slides = vp.querySelectorAll(".imas-featured-slide");
    if (!slides.length) {
        return;
    }
    const maxStart = Math.max(0, slides.length - 1);
    const pos = vp.scrollLeft;
    let best = 0;
    let bestDist = Infinity;
    for (let i = 0; i <= maxStart; i++) {
        const target = cumulativeOffsetBeforeSlide(vp, i);
        const d = Math.abs(pos - target);
        if (d < bestDist) {
            bestDist = d;
            best = i;
        }
    }
    activePage.value = best;
}

function onScroll() {
    syncActiveFromScroll();
}

function onResize() {
    const wasMobile = isMobileCarousel.value;
    syncLayoutMode();
    requestAnimationFrame(async () => {
        const vp = viewportRef.value;
        if (!vp) {
            return;
        }
        if (isMobileCarousel.value) {
            const i = Math.min(activePage.value, pageCount.value - 1);
            activePage.value = i;
            scrollViewportToSlideIndex(vp, i, "auto");
            syncActiveFromScroll();
        } else if (wasMobile) {
            vp.scrollLeft = 0;
            activePage.value = 0;
        }
    });
}

watch(
    () => props.properties,
    async () => {
        activePage.value = 0;
        await nextTick();
        if (isMobileCarousel.value) {
            goToPage(0);
        } else {
            const vp = viewportRef.value;
            if (vp) {
                vp.scrollLeft = 0;
            }
        }
    },
    { deep: true },
);

watch(isMobileCarousel, async (mobile) => {
    await nextTick();
    const vp = viewportRef.value;
    if (!vp) {
        return;
    }
    if (mobile) {
        activePage.value = Math.min(activePage.value, pageCount.value - 1);
        scrollViewportToSlideIndex(vp, activePage.value, "auto");
        syncActiveFromScroll();
    } else {
        vp.scrollLeft = 0;
        activePage.value = 0;
    }
});

function onPointerDown(e) {
    if (!isMobileCarousel.value) {
        return;
    }
    if (e.pointerType === "touch") {
        return;
    }
    if (e.pointerType === "mouse" && e.button !== 0) {
        return;
    }
    if (isInteractiveTarget(e.target)) {
        return;
    }
    const vp = viewportRef.value;
    if (!vp || !(e.target instanceof Node) || !vp.contains(e.target)) {
        return;
    }
    dragPointerId = e.pointerId;
    dragStartX = e.clientX;
    dragStartY = e.clientY;
    lastClientX = e.clientX;
    dragDistance = 0;
    dragAxisLocked = null;
    isDragging.value = false;
}

function onPointerMove(e) {
    if (!isMobileCarousel.value || e.pointerId !== dragPointerId) {
        return;
    }
    const vp = viewportRef.value;
    if (!vp) {
        return;
    }

    if (dragAxisLocked === null) {
        const totalDx = e.clientX - dragStartX;
        const totalDy = e.clientY - dragStartY;
        if (
            Math.abs(totalDx) < AXIS_LOCK_THRESHOLD &&
            Math.abs(totalDy) < AXIS_LOCK_THRESHOLD
        ) {
            return;
        }
        if (Math.abs(totalDy) > Math.abs(totalDx)) {
            resetPointerDrag();
            return;
        }
        dragAxisLocked = "x";
        isDragging.value = true;
        try {
            vp.setPointerCapture(e.pointerId);
        } catch {
            /* ignore */
        }
    }

    if (!isDragging.value || dragAxisLocked !== "x") {
        return;
    }

    const dx = e.clientX - lastClientX;
    lastClientX = e.clientX;
    dragDistance += Math.abs(dx);
    vp.scrollLeft -= dx;
}

function endPointerDrag(e) {
    if (dragPointerId === null) {
        return;
    }
    if (e != null && e.pointerId !== dragPointerId) {
        return;
    }
    const vp = viewportRef.value;
    if (vp && isDragging.value) {
        try {
            vp.releasePointerCapture(dragPointerId);
        } catch {
            /* ignore */
        }
    }
    resetPointerDrag();
    syncActiveFromScroll();
}

function onPointerUp(e) {
    endPointerDrag(e);
}

function onPointerLeave(e) {
    if (e.pointerType !== "mouse") {
        return;
    }
    endPointerDrag(e);
}

function onViewportClickCapture(e) {
    if (isInteractiveTarget(e.target)) {
        dragDistance = 0;
        return;
    }
    if (dragDistance > 8) {
        e.preventDefault();
        e.stopPropagation();
    }
    dragDistance = 0;
}

onMounted(async () => {
    syncLayoutMode();
    await nextTick();
    onResize();
    window.addEventListener("resize", onResize);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", onResize);
});
</script>

<style scoped lang="scss">
.imas-featured-rail {
    width: 100%;
    max-width: 100%;
}

.imas-featured-viewport {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    width: 100%;
    max-width: 100%;
    margin-left: 0;
    margin-right: 0;
}

.imas-featured-slide {
    box-sizing: border-box;
    padding-left: 15px;
    padding-right: 15px;
    margin-bottom: 30px;
}

@media (min-width: 769px) {
    .imas-featured-slide {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (min-width: 992px) {
    .imas-featured-slide {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
    }
}

/* Small screens: one card per view, horizontal scroll (matches PopularPropertiesSection mobile). */
@media (max-width: 768px) {
    .imas-featured-rail {
        margin-left: -15px;
        margin-right: -15px;
        width: calc(100% + 30px);
        max-width: calc(100% + 30px);
    }

    .imas-featured-viewport {
        --imas-slides-visible: 1;
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        direction: ltr;
        unicode-bidi: isolate;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-x pan-y;
        overscroll-behavior-x: contain;
        cursor: grab;
        scrollbar-width: none;
        -ms-overflow-style: none;
        scrollbar-color: transparent transparent;
    }

    .imas-featured-viewport::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }

    .imas-featured-viewport.is-dragging {
        cursor: grabbing;
        scroll-behavior: auto;
    }

    .imas-featured-viewport .imas-featured-slide {
        flex: 0 0 calc(100% / var(--imas-slides-visible, 1));
        max-width: calc(100% / var(--imas-slides-visible, 1));
        margin-bottom: 0;
    }
}

.imas-featured-dots {
    list-style: none !important;
    list-style-type: none !important;
    margin-top: 1.25rem;
    margin-bottom: 0;
    padding: 0;
    text-align: center;
}

.imas-featured-dots li::after {
    pointer-events: none !important;
}

.imas-featured-dots li button {
    position: relative;
    z-index: 2;
    display: block;
    width: 15px;
    height: 15px;
    min-width: 15px;
    min-height: 15px;
    box-sizing: border-box;
    outline: none !important;
}

.imas-featured-dots.slick-dots li {
    color: var(--brand-gold, #d9a800) !important;
    -webkit-box-shadow: inset 0 0 0 2px var(--brand-gold, #d9a800) !important;
    box-shadow: inset 0 0 0 2px var(--brand-gold, #d9a800) !important;
}

.imas-featured-dots.slick-dots li.slick-active {
    -webkit-box-shadow: inset 0 0 0 6px var(--brand-gold, #d9a800) !important;
    box-shadow: inset 0 0 0 6px var(--brand-gold, #d9a800) !important;
}

.imas-featured-dots.slick-dots li:after {
    background-color: var(--brand-gold, #d9a800) !important;
}

.imas-featured-slide :deep(.imas-property-card.item) {
    width: 100% !important;
    max-width: 100% !important;
    flex: none !important;
    float: none;
}
</style>

<style>
@media (max-width: 768px) {
    .imas-featured-viewport {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        scrollbar-color: transparent transparent !important;
    }

    .imas-featured-viewport::-webkit-scrollbar,
    .imas-featured-viewport::-webkit-scrollbar-thumb,
    .imas-featured-viewport::-webkit-scrollbar-track {
        width: 0 !important;
        height: 0 !important;
        display: none !important;
        background: transparent !important;
    }
}

html[dir="rtl"] ul.imas-featured-dots {
    direction: ltr;
}
</style>
