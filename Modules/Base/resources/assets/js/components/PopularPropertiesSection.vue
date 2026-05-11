<template>
    <section
        v-if="properties.length > 0"
        class="featured portfolio rec-pro disc"
    >
        <div class="container-fluid">
            <div class="sec-title discover">
                <h2>
                    <span>{{ titlePrefix }} </span>{{ titleSuffix }}
                </h2>
                <p>{{ subtitle }}</p>
            </div>
            <div class="portfolio col-xl-12">
                <div class="imas-popular-rail">
                    <div
                        ref="viewportRef"
                        dir="ltr"
                        class="slick-lancers imas-popular-viewport"
                        :class="{ 'is-dragging': isDragging }"
                        :style="{ '--imas-slides-visible': visibleCount }"
                        @scroll.passive="syncActiveFromScroll"
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
                            class="imas-popular-slide agents-grid"
                            :dir="slideTextDir"
                        >
                            <div class="landscapes w-100">
                                <PropertyCard :property="property" />
                            </div>
                        </div>
                    </div>
                </div>
                <ul
                    v-if="pageCount > 1"
                    class="slick-dots imas-popular-dots"
                    role="tablist"
                    aria-label="Popular properties"
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

const page = usePage();

/** RTL/LTR for card content; scroll rail stays `dir="ltr"` so scrollLeft is reliable. */
const slideTextDir = computed(() =>
    page.props.text_direction === "rtl" ? "rtl" : "ltr",
);

const props = defineProps({
    properties: {
        type: Array,
        default: () => [],
    },
    titlePrefix: {
        type: String,
        default: "Discover",
    },
    titleSuffix: {
        type: String,
        default: "Popular properties",
    },
    subtitle: {
        type: String,
        default: "",
    },
});

const viewportRef = ref(null);
/** Matches theme `index.html` slick-lancers: 4 / 2 / 1 */
const visibleCount = ref(4);
const activePage = ref(0);
const isDragging = ref(false);

let dragPointerId = null;
let lastClientX = 0;
let dragDistance = 0;

function syncVisibleCount() {
    if (typeof window === "undefined") {
        return;
    }
    const w = window.innerWidth;
    if (w < 769) {
        visibleCount.value = 1;
    } else if (w < 1293) {
        visibleCount.value = 2;
    } else {
        visibleCount.value = 4;
    }
}

const pageCount = computed(() => {
    const n = props.properties.length;
    const v = visibleCount.value;
    if (n <= v) {
        return 1;
    }

    return n - v + 1;
});

/** Offset from the left edge of scroll content to align slide `index` at the viewport’s left edge. */
function cumulativeOffsetBeforeSlide(vp, index) {
    const slides = vp.querySelectorAll(".imas-popular-slide");
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
    const vp = viewportRef.value;
    if (!vp) {
        return;
    }
    requestAnimationFrame(() => {
        const slides = vp.querySelectorAll(".imas-popular-slide");
        const maxStart = Math.max(0, slides.length - visibleCount.value);
        const i = Math.min(maxStart, Math.max(0, index));
        scrollViewportToSlideIndex(vp, i);
        activePage.value = i;
    });
}

function syncActiveFromScroll() {
    const vp = viewportRef.value;
    if (!vp) {
        return;
    }
    const slides = vp.querySelectorAll(".imas-popular-slide");
    if (!slides.length) {
        return;
    }
    const maxStart = Math.max(0, slides.length - visibleCount.value);
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

function onResize() {
    syncVisibleCount();
    requestAnimationFrame(() => {
        const vp = viewportRef.value;
        if (!vp) {
            return;
        }
        const i = Math.min(activePage.value, pageCount.value - 1);
        activePage.value = i;
        scrollViewportToSlideIndex(vp, i, "auto");
        syncActiveFromScroll();
    });
}

watch(
    () => props.properties,
    async () => {
        activePage.value = 0;
        await nextTick();
        goToPage(0);
    },
    { deep: true },
);

watch(visibleCount, async () => {
    activePage.value = Math.min(activePage.value, pageCount.value - 1);
    await nextTick();
    const vp = viewportRef.value;
    if (vp) {
        scrollViewportToSlideIndex(vp, activePage.value, "auto");
        syncActiveFromScroll();
    }
});

function onPointerDown(e) {
    if (e.pointerType === "mouse" && e.button !== 0) {
        return;
    }
    const vp = viewportRef.value;
    if (!vp || !(e.target instanceof Node) || !vp.contains(e.target)) {
        return;
    }
    isDragging.value = true;
    dragPointerId = e.pointerId;
    lastClientX = e.clientX;
    dragDistance = 0;
    try {
        vp.setPointerCapture(e.pointerId);
    } catch {
        /* ignore */
    }
}

function onPointerMove(e) {
    if (!isDragging.value || e.pointerId !== dragPointerId) {
        return;
    }
    const vp = viewportRef.value;
    if (!vp) {
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
    if (vp && dragPointerId !== null) {
        try {
            vp.releasePointerCapture(dragPointerId);
        } catch {
            /* ignore */
        }
    }
    isDragging.value = false;
    dragPointerId = null;
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
    if (dragDistance > 8) {
        e.preventDefault();
        e.stopPropagation();
    }
    dragDistance = 0;
}

onMounted(() => {
    syncVisibleCount();
    syncActiveFromScroll();
    window.addEventListener("resize", onResize);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", onResize);
});
</script>

<style scoped lang="scss">
.imas-popular-rail {
    width: 100%;
    max-width: 100%;
}

/* Scroll math uses scrollLeft; keep axis LTR even when the page is RTL (see `dir` on template). */
.imas-popular-viewport {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    width: 100%;
    max-width: 100%;
    direction: ltr;
    unicode-bidi: isolate;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x;
    cursor: grab;
    scrollbar-width: none;
    -ms-overflow-style: none;
    scrollbar-color: transparent transparent;
}

.imas-popular-viewport::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}

.imas-popular-viewport.is-dragging {
    cursor: grabbing;
    scroll-behavior: auto;
}

.imas-popular-slide {
    flex: 0 0 calc(100% / var(--imas-slides-visible, 4));
    box-sizing: border-box;
    max-width: calc(100% / var(--imas-slides-visible, 4));
    padding-left: 0;
    padding-right: 0;
}

.imas-popular-dots {
    list-style: none !important;
    list-style-type: none !important;
    margin-top: 1.25rem;
    margin-bottom: 0;
    padding: 0;
    text-align: center;
    li{
        button{
            outline: none !important;
        }
    }
}

/*
 * Theme `.slick-dots li:after` is position:absolute on the 15×15 li and sits above the
 * button (width:auto), so clicks hit the pseudo-element and never reach @click on the button.
 */
.imas-popular-dots li::after {
    pointer-events: none !important;
}

.imas-popular-dots li button {
    position: relative;
    z-index: 2;
    display: block;
    width: 15px;
    height: 15px;
    min-width: 15px;
    min-height: 15px;
    box-sizing: border-box;
}

/* Theme PropertyCard is a Bootstrap column — full width inside carousel slide. */
.imas-popular-slide :deep(.imas-property-card.item) {
    width: 100% !important;
    max-width: 100% !important;
    flex: none !important;
    float: none;
}
</style>

<!-- Unscoped: WebKit sometimes ignores scoped ::-webkit-scrollbar; keep scrollbar hidden. -->
<style>
.imas-popular-viewport {
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
    scrollbar-color: transparent transparent !important;
}

.imas-popular-viewport::-webkit-scrollbar,
.imas-popular-viewport::-webkit-scrollbar-thumb,
.imas-popular-viewport::-webkit-scrollbar-track {
    width: 0 !important;
    height: 0 !important;
    display: none !important;
    background: transparent !important;
}

/* Keep dot order LTR so index 0 matches the leading page in both directions. */
html[dir="rtl"] ul.imas-popular-dots {
    direction: ltr;
}
</style>
