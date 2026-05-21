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
                                    <template v-if="useStaticHeroCopy">
                                        <h1 class="h1 imas-hero-title imas-hero-title--static">
                                            {{ heroTitle }}
                                        </h1>
                                        <p ref="heroSubtitleRef" class="mt-4 imas-hero-subtitle">
                                            {{ heroSubtitle }}
                                        </p>
                                    </template>
                                    <template v-else>
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
                                    </template>
                                    <a
                                        v-if="activeSlideLink"
                                        ref="heroActionRef"
                                        :href="activeSlideLink"
                                        class="imas-hero-action"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        {{ actionLabel }}
                                    </a>
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
import {usePage} from '@inertiajs/vue3';
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
const heroActionRef = ref(null);
const heroFilterRef = ref(null);
const displayedTypedText = ref('');
const showTypeCursor = ref(false);
const MOBILE_HERO_MQ = '(max-width: 767.98px)';

const useStaticHeroCopy = ref(
    typeof window !== 'undefined' && window.matchMedia(MOBILE_HERO_MQ).matches,
);

let mobileHeroMq = null;

const {gsap, context} = useGsap();
let slideTimer = null;
let heroAnimToken = 0;
/** @type {import('gsap').Context | null} */
let heroSearchCtx = null;
let searchEnterHasPlayed = false;

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}

function pickTranslation(key, fallback) {
    const value = trans(key);
    if (value && value !== key) {
        return value;
    }

    return fallback;
}

const slides = computed(() => props.slides || []);

const activeSlide = computed(() => slides.value[activeSlideIndex.value] ?? null);

const activeSlideLink = computed(() => {
    const link = activeSlide.value?.link;
    if (typeof link === 'string' && link.trim() !== '') {
        return link.trim();
    }

    return '';
});

const actionLabel = computed(() =>
    pickTranslation('turkishCitizenship.discover_more', 'Discover More'),
);

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

const heroTitleTag = computed(() => 'span');

const heroTitleAttrs = computed(() => ({}));

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

function updateStaticHeroCopy() {
    useStaticHeroCopy.value = window.matchMedia(MOBILE_HERO_MQ).matches;
}

function killHeroCopyTweens() {
    const root = heroCopyRef.value;
    if (!root) {
        return;
    }

    gsap.killTweensOf(root);
    gsap.killTweensOf(root.querySelectorAll('*'));
}

function setHeroCopyVisible() {
    const root = heroCopyRef.value;
    if (!root) {
        return;
    }

    const targets = [
        root.querySelector('.imas-hero-title'),
        root.querySelector('.imas-hero-subtitle'),
        ...root.querySelectorAll(
            '.imas-hero-title-lead, .imas-hero-title-typed, .imas-hero-title-link, .imas-hero-action',
        ),
        titleLeadRef.value,
        titleTypedRef.value,
        heroSubtitleRef.value,
        heroActionRef.value,
    ].filter(Boolean);

    gsap.killTweensOf(targets);

    if (targets.length) {
        gsap.set(targets, {opacity: 1, y: 0, clearProps: 'opacity,transform'});
    }
}

function onMobileHeroMqChange() {
    updateStaticHeroCopy();
    nextTick(() => {
        killHeroCopyTweens();
        setHeroCopyVisible();
        if (useStaticHeroCopy.value) {
            displayedTypedText.value = titleParts.value.typed;
            showTypeCursor.value = false;
        } else {
            playHeroCopyAnimation();
        }
    });
}

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
            {opacity: 1, scale: 1, duration: 2.75, ease: 'power2.out'},
        );
    }, heroFilterRef);
}

function playHeroCopyAnimation() {
    const token = ++heroAnimToken;
    const {lead, typed} = titleParts.value;

    if (prefersReducedMotion() || useStaticHeroCopy.value) {
        displayedTypedText.value = typed;
        showTypeCursor.value = false;
        killHeroCopyTweens();
        setHeroCopyVisible();
        return;
    }

    displayedTypedText.value = '';
    showTypeCursor.value = false;

    const leadEl = titleLeadRef.value;
    const typedEl = titleTypedRef.value;
    const subEl = heroSubtitleRef.value;
    const actionEl = heroActionRef.value;

    context(() => {
        const tl = gsap.timeline({
            defaults: {ease: 'power2.out'},
            onComplete: () => {
                if (token !== heroAnimToken) {
                    return;
                }
                showTypeCursor.value = false;
                setHeroCopyVisible();
            },
        });

        if (subEl) {
            gsap.set(subEl, {opacity: 0, y: -20});
        }

        if (actionEl) {
            gsap.set(actionEl, {opacity: 0, y: 12});
        }

        if (leadEl && lead) {
            gsap.set(leadEl, {opacity: 0, y: -20});
            tl.fromTo(
                leadEl,
                {opacity: 0, y: -20},
                {opacity: 1, y: 0, duration: 1.15},
                0,
            );
        } else if (typedEl && !lead) {
            gsap.set(typedEl, {opacity: 0, y: -20});
        }

        const typeStart = lead ? 0.65 : 0;
        const charDelay = 0.095;
        const chars = [...typed];

        if (!lead && typedEl && chars.length) {
            tl.fromTo(
                typedEl,
                {opacity: 0, y: -20},
                {opacity: 1, y: 0, duration: 0.85},
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
                typeStart + (lead ? 0.16 : 0.42),
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
                    typeStart + (lead ? 0.24 : 0.52) + index * charDelay,
                );
            });
        }

        const afterType =
            typeStart +
            (lead ? 0.24 : 0.52) +
            Math.max(chars.length, 1) * charDelay +
            0.2;

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

        if (actionEl) {
            tl.fromTo(
                actionEl,
                {opacity: 0, y: 12},
                {opacity: 1, y: 0, duration: 0.85},
                afterType + 0.08,
            );
        }

        if (subEl) {
            tl.fromTo(
                subEl,
                {opacity: 0, y: -20},
                {opacity: 1, y: 0, duration: 1.1},
                afterType + (actionEl ? 0.28 : 0.18),
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
    updateStaticHeroCopy();
    mobileHeroMq = window.matchMedia(MOBILE_HERO_MQ);
    mobileHeroMq.addEventListener('change', onMobileHeroMqChange);

    startSlideAutoplay();
    nextTick(() => {
        playHeroSearchEnterAnimation();
        playHeroCopyAnimation();
    });
});

onBeforeUnmount(() => {
    mobileHeroMq?.removeEventListener('change', onMobileHeroMqChange);
    mobileHeroMq = null;
    killHeroCopyTweens();
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
    background: linear-gradient(
        rgba(10, 21, 38, 0.7),
        rgba(10, 21, 38, 0.85)
    );
    pointer-events: none;
}

.hero-main {
    position: relative;
    z-index: 2;
}

.welcome-text {
    position: relative;
    z-index: 1;
    width: 100%;
}

.imas-hero-title--static {
    display: block;
    margin: 0;
}

.imas-hero-title-link {
    color: inherit;
    text-decoration: none;
}

.imas-hero-title-link {
    display: inline;
}

.imas-hero-action {
    display: inline-block;
    margin-top: 0rem;
    margin-bottom: 22px !important;
    padding: 0.65rem 1.75rem;
    border: 2px solid var(--brand-gold, #d9a800);
    border-radius: 6px;
    background: transparent;
    color: #fff;
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.2;
    text-decoration: none;
    transition:
        background-color 0.25s ease,
        color 0.25s ease,
        border-color 0.25s ease;
}

.imas-hero-action:hover,
.imas-hero-action:focus-visible {
    background: var(--brand-gold, #d9a800);
    border-color: var(--brand-gold, #d9a800);
    color: #fff;
    text-decoration: none;
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
    .imas-hero-title--static,
    .imas-hero-title-lead,
    .imas-hero-title-typed,
    .imas-hero-action,
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
    width: 8px;
    height: 8px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.4);
    padding: 0;
    cursor: pointer;
    transition: width 0.2s ease, background 0.2s ease;
}

.imas-hero-dot--active {
    width: 24px;
    border-radius: 6px;
    background: var(--brand-gold, #d9a800);
}

/* Small screens: static title/subtitle; override GSAP inline opacity on typewriter spans. */
@media (max-width: 767.98px) {
    .imas-hero-slider .imas-hero-copy {
        flex: 0 0 auto;
        flex-shrink: 0;
        min-height: auto;
        width: 100%;
        justify-content: flex-start;
        align-items: center;
        padding-top: 0;
        padding-bottom: 0.5rem;
        overflow: visible;
    }

    .imas-hero-slider .welcome-text {
        display: block;
        flex-shrink: 0;
    }

    .imas-hero-slider .welcome-text h1,
    .imas-hero-slider .imas-hero-title,
    .imas-hero-slider .imas-hero-title--static,
    .imas-hero-slider .imas-hero-title-lead,
    .imas-hero-slider .imas-hero-title-typed,
    .imas-hero-slider .imas-hero-title-link,
    .imas-hero-slider .imas-hero-subtitle,
    .imas-hero-slider .welcome-text p {
        color: #fff !important;
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
        display: block;
    }

    .imas-hero-slider .imas-hero-title-link,
    .imas-hero-slider .imas-hero-title-typed {
        display: inline;
    }

    .imas-hero-slider .welcome-text h1,
    .imas-hero-slider .imas-hero-title--static {
        font-size: 1.65rem;
        line-height: 1.3;
    }

    .imas-hero-slider .imas-hero-subtitle,
    .imas-hero-slider .welcome-text p {
        font-size: 0.95rem;
        line-height: 1.5;
        max-width: 100%;
        margin-top: 0.75rem !important;
        margin-bottom: 0 !important;
        padding-bottom: 0;
    }

    .imas-hero-slider .imas-hero-action {
        margin-top: 1.25rem !important;
        margin-bottom: 1rem !important;
    }

    .imas-hero-slider .imas-hero-dots {
        margin-top: 0.75rem;
    }

    .imas-hero-slider .hero-inner {
        justify-content: flex-start;
        gap: 0.75rem;
        padding-top: 88px !important;
        padding-bottom: 20px !important;
    }

    .imas-hero-slider .imas-hero-filter {
        padding-top: 0.5rem;
    }
}

@media (max-width: 575.98px) {
    .imas-hero-slider .welcome-text h1,
    .imas-hero-slider .imas-hero-title--static {
        font-size: 1.45rem;
    }

    .imas-hero-slider .hero-inner {
        padding-top: 80px !important;
    }

    .imas-hero-slider .imas-hero-action {
        margin-top: 1.125rem !important;
    }
}
</style>
