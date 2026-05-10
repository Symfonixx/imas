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
                                <div class="welcome-text">
                                    <h1 class="h1">
                                        <template v-if="activeSlide?.link">
                                            <a :href="activeSlide.link" class="imas-hero-title-link">{{ heroTitle }}</a>
                                        </template>
                                        <template v-else>{{ heroTitle }}</template>
                                    </h1>
                                    <p class="mt-4">{{ heroSubtitle }}</p>
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

                            <div class="imas-hero-filter">
                                <div class="banner-search-wrap">
                                    <form
                                        class="tab-content"
                                        method="get"
                                        :action="propertyIndexUrl"
                                    >
                                        <input type="hidden" name="purpose" :value="purpose">

                                        <div class="tab-pane fade show active">
                                            <div class="rld-main-search">
                                                <div class="row ">
                                                    <div class="rld-single-input">
                                                        <input
                                                            v-model="searchKeyword"
                                                            type="search"
                                                            name="q"
                                                            autocomplete="off"
                                                            :placeholder="trans('Enter Keyword...')"
                                                        >
                                                    </div>
                                                    <div class="rld-single-select ml-22">
                                                        <select
                                                            v-model="searchPropertyTypeId"
                                                            class="select single-select wide"
                                                            name="property_type_id"
                                                        >
                                                            <option value="">{{ trans('Property Type') }}</option>
                                                            <option
                                                                v-for="t in propertyTypes"
                                                                :key="t.id"
                                                                :value="String(t.id)"
                                                            >
                                                                {{ t.name }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="rld-single-select">
                                                        <select
                                                            v-model="searchLocationId"
                                                            class="select single-select wide mr-0"
                                                            name="location_id"
                                                        >
                                                            <option value="">{{ trans('Location') }}</option>
                                                            <option
                                                                v-for="c in cities"
                                                                :key="c.id"
                                                                :value="String(c.id)"
                                                            >
                                                                {{ c.name }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="imas-hero-search-actions col-12 col-lg-2 col-xl-2 pl-0">
                                                        <button type="submit" class="btn btn-yellow btn-block">
                                                            {{ trans('Search Now') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue';
import {usePage} from '@inertiajs/vue3';

const props = defineProps({
    welcomeTitle: {type: String, required: true},
    welcomeSubtitle: {type: String, required: true},
    slides: {type: Array, default: () => []},
    propertyTypes: {type: Array, default: () => []},
    cities: {type: Array, default: () => []},

});

const page = usePage();

const purpose = ref('sale');

const searchKeyword = ref('');
const searchPropertyTypeId = ref('');
const searchLocationId = ref('');

const activeSlideIndex = ref(0);
let slideTimer = null;

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

const propertyIndexUrl = computed(() => route('property.index'));

function trans(key) {
    return page.props.translations[key] || key;
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
        activeSlideIndex.value = (activeSlideIndex.value + 1) % slides.value.length;
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
}, {deep: true});

onMounted(() => {
    startSlideAutoplay();
});

onBeforeUnmount(() => {
    stopSlideAutoplay();
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

/*
 * Only the outer hero grid may be a column stack. Nested `.row` inside `.rld-main-search`
 * must stay Bootstrap row (horizontal on lg+) — a descendant `.hero-main .row` rule breaks it.
 */
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
}

/* Search button: theme `.rld-main-search .btn { width:100% }` — pin column end on lg+, center on small screens */
@media (min-width: 992px) {
    .imas-hero-filter .imas-hero-search-actions {
        margin-left: auto;
    }
}

@media (max-width: 991px) {
    .imas-hero-filter .imas-hero-search-actions {
        display: flex;
        justify-content: center;
        margin-top: 0.5rem;
    }

    .imas-hero-filter .imas-hero-search-actions .btn.btn-yellow {
        width: auto !important;
        min-width: 200px;
    }
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
