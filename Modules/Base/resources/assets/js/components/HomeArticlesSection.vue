<template>
    <section
        v-if="articles.length"
        ref="sectionRef"
        class="blog-section bg-white-2 imas-home-section"
    >
        <div class="container">
            <div class="sec-title">
                <h2>{{ trans("articles.title") }}</h2>
                <p>{{ trans("articles.description") }}</p>
            </div>
            <div class="news-wrap">
                <div class="imas-articles-rail">
                    <div
                        ref="viewportRef"
                        dir="ltr"
                        class="row imas-articles-viewport"
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
                            v-for="(article, index) in articles"
                            :key="article.id"
                            class="imas-articles-slide d-flex"
                            :dir="slideTextDir"
                        >
                            <ArticleCard
                                :article="article"
                                :is-last="index === articles.length - 1"
                                :read-more-label="trans('articles.read_more')"
                            />
                        </div>
                    </div>
                    <ul
                        v-if="isMobileCarousel && pageCount > 1"
                        class="slick-dots imas-articles-dots"
                        role="tablist"
                        :aria-label="trans('articles.title')"
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
            <ReadMore
                class="btnMarginTop"
                href="#"
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
import ArticleCard from "@/components/articles/ArticleCard.vue";
import ReadMore from "@/components/buttons/ReadMore.vue";
import { useScrollReveal } from "@/composables/useScrollReveal";

const props = defineProps({
    articles: {
        type: Array,
        default: () => [],
    },
});

const sectionRef = ref(null);

useScrollReveal(sectionRef, {
    preset: "home",
    variant: "cards",
    when: computed(() => props.articles.length > 0),
});

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}

/** RTL/LTR for card content; scroll rail stays `dir="ltr"` so scrollLeft is reliable. */
const slideTextDir = computed(() =>
    page.props.text_direction === "rtl" ? "rtl" : "ltr",
);

const MOBILE_MAX = 768;

const viewportRef = ref(null);
const isMobileCarousel = ref(false);
const activePage = ref(0);
const isDragging = ref(false);

let dragPointerId = null;
let lastClientX = 0;
let dragDistance = 0;

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
    const n = props.articles.length;
    return n > 1 ? n : 1;
});

function cumulativeOffsetBeforeSlide(vp, index) {
    const slides = vp.querySelectorAll(".imas-articles-slide");
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
        const slides = vp.querySelectorAll(".imas-articles-slide");
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
    const slides = vp.querySelectorAll(".imas-articles-slide");
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
    requestAnimationFrame(() => {
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
    () => props.articles,
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
    if (
        !isMobileCarousel.value ||
        !isDragging.value ||
        e.pointerId !== dragPointerId
    ) {
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
.btnMarginTop {
    margin-top: 30px !important;
}

.imas-articles-rail {
    width: 100%;
    max-width: 100%;
}

.imas-articles-viewport {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    width: 100%;
    max-width: 100%;
    margin-left: 0;
    margin-right: 0;
}

.imas-articles-slide {
    box-sizing: border-box;
    padding-left: 15px;
    padding-right: 15px;
    margin-bottom: 30px;
}

@media (min-width: 769px) {
    .imas-articles-slide {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (min-width: 992px) {
    .imas-articles-slide {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
    }
}

@media (max-width: 768px) {
    .imas-articles-rail {
        margin-left: -15px;
        margin-right: -15px;
        width: calc(100% + 30px);
        max-width: calc(100% + 30px);
    }

    .imas-articles-viewport {
        --imas-slides-visible: 1;
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
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

    .imas-articles-viewport::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }

    .imas-articles-viewport.is-dragging {
        cursor: grabbing;
        scroll-behavior: auto;
    }

    .imas-articles-viewport .imas-articles-slide {
        flex: 0 0 calc(100% / var(--imas-slides-visible, 1));
        max-width: calc(100% / var(--imas-slides-visible, 1));
        margin-bottom: 0;
    }
}

.imas-articles-dots {
    list-style: none !important;
    list-style-type: none !important;
    margin-top: 1.25rem;
    margin-bottom: 0;
    padding: 0;
    text-align: center;
}

.imas-articles-dots li::after {
    pointer-events: none !important;
}

.imas-articles-dots li button {
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

.imas-articles-dots.slick-dots li {
    color: var(--brand-gold, #d9a800) !important;
    -webkit-box-shadow: inset 0 0 0 2px var(--brand-gold, #d9a800) !important;
    box-shadow: inset 0 0 0 2px var(--brand-gold, #d9a800) !important;
}

.imas-articles-dots.slick-dots li.slick-active {
    -webkit-box-shadow: inset 0 0 0 6px var(--brand-gold, #d9a800) !important;
    box-shadow: inset 0 0 0 6px var(--brand-gold, #d9a800) !important;
}

.imas-articles-dots.slick-dots li:after {
    background-color: var(--brand-gold, #d9a800) !important;
}

.imas-articles-slide :deep(.imas-article-card) {
    width: 100% !important;
    max-width: 100% !important;
    flex: 1 1 auto;
}
</style>

<style>
@media (max-width: 768px) {
    .imas-articles-viewport {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        scrollbar-color: transparent transparent !important;
    }

    .imas-articles-viewport::-webkit-scrollbar,
    .imas-articles-viewport::-webkit-scrollbar-thumb,
    .imas-articles-viewport::-webkit-scrollbar-track {
        width: 0 !important;
        height: 0 !important;
        display: none !important;
        background: transparent !important;
    }
}

html[dir="rtl"] ul.imas-articles-dots {
    direction: ltr;
}
</style>
