<template>
    <section
        id="hero-area"
        class="parallax-searchs home15 overlay thome-6 thome-1"
        :class="{'imas-hero-slider': slides.length > 0}"
        data-stellar-background-ratio="0.5"
    >
        <div v-if="slides.length > 0" class="imas-hero-slider__layers" aria-hidden="true">
            <div
                v-for="(slide, index) in slides"
                :key="slide.id"
                class="imas-hero-slider__slide"
                :class="{ 'imas-hero-slider__slide--active': index === activeSlideIndex }"
                :style="{ backgroundImage: `url(${slide.image})` }"
            />
            <div class="imas-hero-slider__scrim"/>
        </div>

        <div class="hero-main">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="hero-inner">
                            <div class="imas-hero-copy">
                                <div ref="heroCopyRef" class="welcome-text">
                                    <h1 class="h1 imas-hero-title" :aria-label="heroTitle">
                                        <component
                                            :is="heroTitleTag"
                                            v-bind="heroTitleAttrs"
                                            class="imas-hero-title-link"
                                        >
                                            <span
                                                v-if="titleParts.lead"
                                                ref="titleLeadRef"
                                                class="imas-hero-title-lead"
                                            >{{ titleParts.lead }}</span>
                                            <span
                                                v-if="titleParts.lead"
                                                class="imas-hero-title-gap"
                                                aria-hidden="true"
                                            >&nbsp;</span>
                                            <span
                                                ref="titleTypedRef"
                                                class="imas-hero-title-typed"
                                            >
                                                {{ displayedTypedText }}<span
                                                    v-if="showTypeCursor"
                                                    class="imas-hero-type-cursor"
                                                    aria-hidden="true"
                                                >|</span>
                                            </span>
                                        </component>
                                    </h1>
                                    <p ref="heroSubtitleRef" class="mt-4 imas-hero-subtitle">
                                        {{ heroSubtitle }}
                                    </p>
                                </div>

                                <div v-if="slides.length > 1" class="imas-hero-dots" role="tablist" aria-label="Slides">
                                    <button
                                        v-for="(slide, index) in slides"
                                        :key="slide.id"
                                        type="button"
                                        class="imas-hero-dot"
                                        :class="{ 'imas-hero-dot--active': index === activeSlideIndex }"
                                        :aria-selected="index === activeSlideIndex"
                                        @click="goToSlide(index)"
                                    />
                                </div>
                            </div>

                            <div ref="heroFilterRef" class="imas-hero-filter">
                                <HomeHeroPropertySearch
                                    :action="propertyIndexUrl"
                                    :purpose="purpose"
                                    :property-types="propertyTypes"
                                    :cities="cities"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import {computed, nextTick, onBeforeUnmount, onMounted, ref, watch} from 'vue';
import {useGsap} from '@/composables/useGsap';
import {createGsapContext, prefersReducedMotion} from '@/plugins/gsap';
import HomeHeroPropertySearch from './HomeHeroPropertySearch.vue';

const props = defineProps({
    welcomeTitle: {type: String, required: true},
    welcomeSubtitle: {type: String, required: true},
    slides: {type: Array, default: () => []},
    propertyTypes: {type: Array, default: () => []},
    cities: {type: Array, default: () => []},

});

const purpose = ref('sale');

const activeSlideIndex = ref(0);
const heroCopyRef = ref(null);
const titleLeadRef = ref(null);
const titleTypedRef = ref(null);
const heroSubtitleRef = ref(null);
const heroFilterRef = ref(null);
const displayedTypedText = ref('');
const showTypeCursor = ref(false);

const {gsap, context} = useGsap();
let slideTimer = null;
let heroAnimToken = 0;
/** @type {import('gsap').Context | null} */
let heroSearchCtx = null;
let searchEnterHasPlayed = false;

const slides = computed(() => props.slides || []);

const activeSlide = computed(() => slides.value[activeSlideIndex.value] ?? null);

function pickSlideText(value, fallback) {
    if (typeof value !== 'string') {
        return fallback;
    }
    const trimmed = value.trim();

    return trimmed !== '' ? trimmed : fallback;
}

const heroTitle = computed(() =>
    slides.value.length > 0
        ? pickSlideText(activeSlide.value?.title, props.welcomeTitle)
        : props.welcomeTitle
);

const heroSubtitle = computed(() =>
    slides.value.length > 0
        ? pickSlideText(activeSlide.value?.description, props.welcomeSubtitle)
        : props.welcomeSubtitle
);

const heroTitleTag = computed(() => (activeSlide.value?.link ? 'a' : 'span'));

const heroTitleAttrs = computed(() => {
    const link = activeSlide.value?.link;
    if (typeof link === 'string' && link.trim() !== '') {
        return {href: link.trim()};
    }

    return {};
});

/**
 * @param {string} title
 * @returns {{ lead: string, typed: string }}
 */
function splitTitleForTypewriter(title) {
    const words = String(title || '')
        .trim()
        .split(/\s+/u)
        .filter(Boolean);

    if (words.length === 0) {
        return {lead: '', typed: ''};
    }

    if (words.length <= 2) {
        return {lead: '', typed: words.join(' ')};
    }

    return {
        lead: words.slice(0, -2).join(' '),
        typed: words.slice(-2).join(' '),
    };
}

const titleParts = computed(() => splitTitleForTypewriter(heroTitle.value));

const propertyIndexUrl = computed(() => route('property.index'));

function playHeroSearchEnterAnimation() {
    if (searchEnterHasPlayed) {
        return;
    }

    const filterEl = heroFilterRef.value;
    if (!filterEl) {
        return;
    }

    searchEnterHasPlayed = true;

    if (prefersReducedMotion()) {
        gsap.set(filterEl, {opacity: 1, scale: 1});
        return;
    }

    heroSearchCtx = createGsapContext(() => {
        gsap.fromTo(
            filterEl,
            {opacity: 0, scale: 0.5},
            {opacity: 1, scale: 1, duration: 1.5, ease: 'power2.out'},
        );
    }, heroFilterRef);
}

function playHeroCopyAnimation() {
    const token = ++heroAnimToken;
    const {lead, typed} = titleParts.value;

    if (prefersReducedMotion()) {
        displayedTypedText.value = typed;
        showTypeCursor.value = false;
        return;
    }

    displayedTypedText.value = '';
    showTypeCursor.value = false;

    const leadEl = titleLeadRef.value;
    const typedEl = titleTypedRef.value;
    const subEl = heroSubtitleRef.value;

    context(() => {
        const tl = gsap.timeline({
            defaults: {ease: 'power2.out'},
            onComplete: () => {
                if (token !== heroAnimToken) {
                    return;
                }
                showTypeCursor.value = false;
            },
        });

        if (subEl) {
            gsap.set(subEl, {opacity: 0, y: -20});
        }

        if (leadEl && lead) {
            gsap.set(leadEl, {opacity: 0, y: -20});
            tl.fromTo(
                leadEl,
                {opacity: 0, y: -20},
                {opacity: 1, y: 0, duration: 0.55},
                0,
            );
        } else if (typedEl && !lead) {
            gsap.set(typedEl, {opacity: 0, y: -20});
        }

        const typeStart = lead ? 0.32 : 0;
        const chars = [...typed];

        if (!lead && typedEl && chars.length) {
            tl.fromTo(
                typedEl,
                {opacity: 0, y: -20},
                {opacity: 1, y: 0, duration: 0.4},
                typeStart,
            );
        }

        if (chars.length) {
            tl.call(
                () => {
                    if (token !== heroAnimToken) {
                        return;
                    }
                    showTypeCursor.value = true;
                },
                null,
                typeStart + (lead ? 0.08 : 0.2),
            );

            chars.forEach((char, index) => {
                tl.call(
                    () => {
                        if (token !== heroAnimToken) {
                            return;
                        }
                        displayedTypedText.value += char;
                    },
                    null,
                    typeStart + (lead ? 0.12 : 0.28) + index * 0.045,
                );
            });
        }

        const afterType =
            typeStart +
            (lead ? 0.12 : 0.28) +
            Math.max(chars.length, 1) * 0.045 +
            0.08;

        tl.call(
            () => {
                if (token !== heroAnimToken) {
                    return;
                }
                showTypeCursor.value = false;
            },
            null,
            afterType,
        );

        if (subEl) {
            tl.fromTo(
                subEl,
                {opacity: 0, y: -20},
                {opacity: 1, y: 0, duration: 0.5},
                afterType + 0.06,
            );
        }

    }, heroCopyRef);
}

function goToSlide(index) {
    activeSlideIndex.value = index;
}

function startSlideAutoplay() {
    stopSlideAutoplay();
    if (slides.value.length <= 1) {
        return;
    }
    slideTimer = window.setInterval(() => {
        activeSlideIndex.value =
            (activeSlideIndex.value + 1) % slides.value.length;
    }, 6500);
}

function stopSlideAutoplay() {
    if (slideTimer !== null) {
        clearInterval(slideTimer);
        slideTimer = null;
    }
}

watch(slides, () => {
    activeSlideIndex.value = 0;
    startSlideAutoplay();
    nextTick(() => playHeroCopyAnimation());
}, {deep: true});

watch(activeSlideIndex, () => {
    nextTick(() => playHeroCopyAnimation());
});

onMounted(() => {
    startSlideAutoplay();
    nextTick(() => {
        playHeroSearchEnterAnimation();
        playHeroCopyAnimation();
    });
});

onBeforeUnmount(() => {
    stopSlideAutoplay();
    heroSearchCtx?.revert?.();
    heroSearchCtx = null;
});
</script>

<style scoped>
/*
 * Theme fixes:
 * - Do not use overflow:hidden here: combined with theme height:95vh it clipped the search bar.
 * - height:auto + min-height lets the section grow with title + search (theme hero-inner padding is large).
 * - Remove reliance on AOS in markup (aos.js is not loaded on app.blade) — invisible content otherwise.
 */
.imas-hero-slider.parallax-searchs.home15.thome-6.thome-1 {
    background: none !important;
    background-image: none !important;
    position: relative;
    height: auto !important;
    min-height: 95vh;
    display: flex;
    flex-direction: column;
}

.imas-hero-slider .hero-main {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
}

.imas-hero-slider .hero-main .container {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
}

/* Only the outer hero grid is a column stack; the property search bar uses its own flex row. */
.imas-hero-slider .hero-main > .container > .row {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

.imas-hero-slider .hero-main > .container > .row > [class*='col-'] {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

.imas-hero-slider .hero-inner {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 0;
    padding-top: 120px !important;
    padding-bottom: 32px !important;
}

@media (min-width: 992px) {
    .imas-hero-slider .hero-inner {
        padding-top: 160px !important;
        padding-bottom: 40px !important;
    }
}

.imas-hero-copy {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    min-height: 0;
    padding-bottom: 1rem;
}

.imas-hero-filter {
    flex: 0 0 auto;
    width: 100%;
    max-width: 100%;
    padding-top: 1rem;
    transform-origin: center center;
    will-change: transform, opacity;
}

.imas-hero-slider__layers {
    position: absolute;
    inset: 0;
    z-index: 0;
}

.imas-hero-slider__slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center center;
    opacity: 0;
    transition: opacity 1s ease-in-out;
}

.imas-hero-slider__slide--active {
    opacity: 1;
}

.imas-hero-slider__scrim {
    position: absolute;
    inset: 0;
    background: linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.55));
    pointer-events: none;
}

.hero-main {
    position: relative;
    z-index: 2;
}

.welcome-text {
    position: relative;
    z-index: 1;
}

.imas-hero-title-link {
    color: inherit;
    text-decoration: none;
}

.imas-hero-title-link:hover {
    color: inherit;
    text-decoration: underline;
}

.imas-hero-title-link {
    display: inline;
}

.imas-hero-title-typed {
    display: inline;
}

.imas-hero-type-cursor {
    display: inline-block;
    margin-inline-start: 2px;
    font-weight: 300;
    animation: imas-hero-cursor-blink 0.75s step-end infinite;
}

@keyframes imas-hero-cursor-blink {
    50% {
        opacity: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .imas-hero-title-lead,
    .imas-hero-title-typed,
    .imas-hero-subtitle,
    .imas-hero-filter {
        opacity: 1 !important;
        transform: none !important;
    }

    .imas-hero-type-cursor {
        display: none;
    }
}

.imas-hero-dots {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    margin-top: 1rem;
}

.imas-hero-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.75);
    background: transparent;
    padding: 0;
    cursor: pointer;
}

.imas-hero-dot--active {
    background: rgba(255, 255, 255, 0.95);
}
</style>
