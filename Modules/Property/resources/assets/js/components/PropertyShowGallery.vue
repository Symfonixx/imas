<template>
    <div
        v-if="assets.length > 0"
        :id="carouselId"
        class="carousel listing-details-sliders slide mb-30 imas-property-gallery"
    >
        <h5 class="imas-section-title mb-4">{{ title }}</h5>

        <div class="imas-gallery-main">
            <div class="carousel-inner imas-gallery-main__inner">
                <div
                    v-for="(asset, index) in assets"
                    :key="asset.key"
                    class="item carousel-item imas-gallery-slide"
                    :class="{ active: index === activeIndex }"
                    :data-slide-number="index"
                >
                    <div class="imas-gallery-main__frame">
                        <template v-if="asset.type === 'video'">
                            <img
                                :src="videoPosterUrl"
                                class="imas-gallery-main__img"
                                :alt="asset.alt || fallbackAlt"
                                loading="lazy"
                            />
                            <button
                                type="button"
                                class="imas-gallery-video-play"
                                :aria-label="playVideoLabel"
                                @click="openVideoLightbox(asset.url)"
                            >
                                <span
                                    class="imas-gallery-video-play__btn"
                                    aria-hidden="true"
                                >
                                    <i class="fa fa-play"></i>
                                </span>
                            </button>
                        </template>
                        <img
                            v-else
                            :src="asset.url"
                            class="imas-gallery-main__img"
                            :alt="asset.alt"
                            loading="lazy"
                        />
                    </div>
                </div>
            </div>

            <template v-if="assets.length > 1">
                <a
                    class="carousel-control left imas-gallery-control"
                    href="#"
                    role="button"
                    :aria-label="previousLabel"
                    @click.prevent="goPrev"
                >
                    <i class="fa fa-angle-left" aria-hidden="true"></i>
                </a>
                <a
                    class="carousel-control right imas-gallery-control"
                    href="#"
                    role="button"
                    :aria-label="nextLabel"
                    @click.prevent="goNext"
                >
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </template>
        </div>

        <div
            v-if="assets.length > 1 || categories.length > 0"
            class="imas-gallery-toolbar"
        >
            <div
                v-if="assets.length > 1"
                class="imas-gallery-counter"
                aria-live="polite"
            >
                <i
                    class="fa fa-image imas-gallery-counter__icon"
                    aria-hidden="true"
                ></i>
                <span class="imas-gallery-counter__text">
                    {{ activeIndex + 1 }} / {{ assets.length }}
                </span>
            </div>

            <nav
                v-if="categories.length > 0"
                class="imas-gallery-categories"
                :aria-label="categoriesRegionLabel"
            >
                <template
                    v-for="(category, catIndex) in categories"
                    :key="category.id"
                >
                    <span
                        v-if="catIndex > 0"
                        class="imas-gallery-categories__sep"
                        aria-hidden="true"
                        >|</span
                    >
                    <button
                        type="button"
                        class="imas-gallery-categories__btn"
                        :class="{
                            'is-active': category.id === activeCategoryId,
                        }"
                        :aria-pressed="category.id === activeCategoryId"
                        :aria-current="
                            category.id === activeCategoryId
                                ? 'true'
                                : undefined
                        "
                        @click="selectCategory(category.id, $event)"
                    >
                        {{ category.name }}
                    </button>
                </template>
            </nav>
        </div>

        <div
            v-if="assets.length > 1"
            ref="thumbsViewportRef"
            class="imas-gallery-thumbs-outer"
            :dir="thumbsDir"
            role="region"
            tabindex="0"
            :aria-label="thumbsRegionLabel"
        >
            <ul
                class="carousel-indicators smail-listing list-inline imas-gallery-thumbs"
            >
                <li
                    v-for="(asset, index) in assets"
                    :key="'thumb-' + asset.key"
                    :ref="(el) => setThumbRef(el, index)"
                    class="list-inline-item imas-gallery-thumbs__item"
                    :class="{ active: index === activeIndex }"
                >
                    <a
                        href="#"
                        :class="{ selected: index === activeIndex }"
                        :aria-label="
                            asset.type === 'video'
                                ? `Video ${index + 1}`
                                : `Image ${index + 1}`
                        "
                        :aria-current="
                            index === activeIndex ? 'true' : undefined
                        "
                        @click.prevent="selectThumb(index)"
                    >
                        <span class="imas-gallery-thumb__frame">
                            <img
                                :src="
                                    asset.type === 'video'
                                        ? videoPosterUrl
                                        : asset.url
                                "
                                class="imas-gallery-thumb__img"
                                :alt="asset.alt || fallbackAlt"
                                loading="lazy"
                            />
                            <span
                                v-if="asset.type === 'video'"
                                class="imas-gallery-thumb__play"
                                aria-hidden="true"
                            >
                                <i class="fa fa-play"></i>
                            </span>
                        </span>
                    </a>
                </li>
            </ul>
        </div>

        <VideoLightbox
            v-model="lightboxOpen"
            :video-url="lightboxVideoUrl"
            :aria-label="playVideoLabel"
            :invalid-message="videoUnavailableLabel"
        />
    </div>
</template>

<script setup>
import { computed, nextTick, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import VideoLightbox from "@/components/Global/VideoLightbox.vue";

const props = defineProps({
    propertyId: { type: [Number, String], required: true },
    slides: { type: Array, default: () => [] },
    slideCategories: { type: Array, default: () => [] },
    thumbnailUrl: { type: String, default: "" },
    thumbnailAlt: { type: String, default: "" },
    alt: { type: String, default: "" },
    title: { type: String, default: "Gallery" },
});

const page = usePage();
const activeIndex = ref(0);
const activeCategoryId = ref(null);
const thumbsViewportRef = ref(null);
const thumbItemRefs = ref([]);
const lightboxOpen = ref(false);
const lightboxVideoUrl = ref("");

const carouselId = computed(() => `listingDetailsSlider-${props.propertyId}`);

const isRtl = computed(() => {
    const dir = String(page.props.text_direction ?? "");
    const locale = String(page.props.locale ?? "");
    return dir === "rtl" || locale === "ar";
});

const thumbsDir = computed(() => (isRtl.value ? "rtl" : "ltr"));

const previousLabel = computed(
    () => page.props.translations?.["global.previous"] || "Previous",
);
const nextLabel = computed(
    () => page.props.translations?.["global.next"] || "Next",
);
const thumbsRegionLabel = computed(
    () =>
        page.props.translations?.["property_show.gallery_thumbnails"] ||
        "Gallery thumbnails",
);
const categoriesRegionLabel = computed(
    () =>
        page.props.translations?.["property_show.gallery_categories"] ||
        "Gallery categories",
);
const playVideoLabel = computed(
    () =>
        page.props.translations?.["property_show.play_video"] ||
        "Play property video",
);
const videoUnavailableLabel = computed(
    () =>
        page.props.translations?.["property_show.video_unavailable"] ||
        "Video is not available.",
);

const fallbackAlt = computed(
    () => props.thumbnailAlt || props.alt || "",
);

const categories = computed(() => {
    return (props.slideCategories ?? []).filter(
        (category) =>
            category &&
            Array.isArray(category.assets) &&
            category.assets.length > 0,
    );
});

const useCategories = computed(() => categories.value.length > 0);

const activeCategory = computed(() => {
    if (!useCategories.value) {
        return null;
    }
    return (
        categories.value.find((c) => c.id === activeCategoryId.value) ??
        categories.value[0] ??
        null
    );
});

const videoPosterUrl = computed(() => {
    const categoryAssets = activeCategory.value?.assets ?? [];
    const firstImage = categoryAssets.find(
        (asset) => asset.type === "image" && typeof asset.url === "string",
    );
    if (firstImage?.url) {
        return firstImage.url;
    }
    const thumb = props.thumbnailUrl?.trim() ?? "";
    if (thumb && !thumb.includes("/images/blank.png")) {
        return thumb;
    }
    return thumb || "";
});

const legacyImages = computed(() => {
    const rows = [];
    const seen = new Set();
    const thumb = props.thumbnailUrl?.trim() ?? "";
    const isPlaceholderThumb =
        thumb === "" || thumb.includes("/images/blank.png");
    const alt = fallbackAlt.value;

    if (thumb && !isPlaceholderThumb) {
        seen.add(thumb);
        rows.push({
            key: "thumbnail",
            type: "image",
            url: thumb,
            alt,
        });
    }

    for (const slide of props.slides ?? []) {
        const url = slide?.image_url;
        if (typeof url !== "string" || url === "" || seen.has(url)) {
            continue;
        }
        seen.add(url);
        rows.push({
            key: `slide-${slide.id ?? rows.length}`,
            type: "image",
            url,
            alt: slide?.alt || alt,
            title: slide?.title || "",
        });
    }

    return rows;
});

const assets = computed(() => {
    if (useCategories.value && activeCategory.value) {
        return (activeCategory.value.assets ?? []).map((asset, index) => ({
            key: `${asset.type}-${asset.id ?? index}`,
            type: asset.type === "video" ? "video" : "image",
            url: asset.url,
            alt: asset.alt || fallbackAlt.value,
            title: asset.title || "",
        }));
    }
    return legacyImages.value;
});

function setThumbRef(el, index) {
    if (el) {
        thumbItemRefs.value[index] = el;
    }
}

function prefersReducedMotion() {
    return (
        typeof window !== "undefined" &&
        window.matchMedia("(prefers-reduced-motion: reduce)").matches
    );
}

function scrollBehavior() {
    return prefersReducedMotion() ? "auto" : "smooth";
}

/**
 * Scroll the thumb strip using getBoundingClientRect + scrollBy so LTR and RTL
 * (including negative scrollLeft browsers) stay in sync with the active image.
 */
async function scrollActiveThumbIntoView() {
    await nextTick();
    const viewport = thumbsViewportRef.value;
    if (!viewport) {
        return;
    }

    const thumbEl = thumbItemRefs.value[activeIndex.value];
    if (!thumbEl) {
        return;
    }

    const behavior = scrollBehavior();
    const vpRect = viewport.getBoundingClientRect();
    const thumbRect = thumbEl.getBoundingClientRect();
    const pad = 2;

    if (activeIndex.value === 0) {
        const delta = isRtl.value
            ? thumbRect.right - vpRect.right
            : thumbRect.left - vpRect.left;
        if (Math.abs(delta) > pad) {
            viewport.scrollBy({ left: delta, behavior });
        }
        return;
    }

    if (thumbRect.left < vpRect.left - pad) {
        viewport.scrollBy({
            left: thumbRect.left - vpRect.left,
            behavior,
        });
        return;
    }

    if (thumbRect.right > vpRect.right + pad) {
        viewport.scrollBy({
            left: thumbRect.right - vpRect.right,
            behavior,
        });
    }
}

function selectCategory(categoryId, event) {
    if (event?.currentTarget instanceof HTMLElement) {
        event.currentTarget.blur();
    }
    if (activeCategoryId.value === categoryId) {
        return;
    }
    activeCategoryId.value = categoryId;
    activeIndex.value = 0;
    thumbItemRefs.value = [];
}

function selectThumb(index) {
    activeIndex.value = index;
}

function goPrev() {
    const n = assets.value.length;
    if (n <= 1) {
        return;
    }
    activeIndex.value = (activeIndex.value - 1 + n) % n;
}

function goNext() {
    const n = assets.value.length;
    if (n <= 1) {
        return;
    }
    activeIndex.value = (activeIndex.value + 1) % n;
}

function openVideoLightbox(url) {
    lightboxVideoUrl.value = url;
    lightboxOpen.value = true;
}

watch(
    () => categories.value.map((c) => c.id).join("|"),
    () => {
        if (!useCategories.value) {
            activeCategoryId.value = null;
            return;
        }
        const ids = categories.value.map((c) => c.id);
        if (
            activeCategoryId.value === null ||
            !ids.includes(activeCategoryId.value)
        ) {
            activeCategoryId.value = ids[0];
            activeIndex.value = 0;
        }
    },
    { immediate: true },
);

watch(
    () => assets.value.map((asset) => asset.key).join("|"),
    () => {
        thumbItemRefs.value = [];
        const length = assets.value.length;
        if (length === 0) {
            activeIndex.value = 0;
            return;
        }
        if (activeIndex.value >= length) {
            activeIndex.value = length - 1;
        }
    },
);

watch(activeIndex, () => {
    scrollActiveThumbIntoView();
});
</script>

<style scoped lang="scss">
/* Clip anything that escapes the gallery column (e.g. theme thumb widths). */
.imas-property-gallery {
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

/* Fixed main stage — all slides share the same box; images use contain (no stretch). */
.imas-gallery-main {
    position: relative;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.imas-gallery-main__inner {
    width: 100%;
}

.imas-property-gallery .carousel-item {
    display: none;
    float: none;
}

.imas-property-gallery .carousel-item.active {
    display: block;
}

.imas-gallery-main__frame {
    position: relative;
    width: 100%;
    height: 500px;
    max-height: min(500px, 70vh);
    background: var(--surface-2);
    border: 1px solid color-mix(in srgb, var(--brand-navy) 10%, #e8eaed);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.imas-gallery-main__img {
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    object-position: center;
    display: block;
}

.imas-gallery-video-play {
    position: absolute;
    inset: 0;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 0;
    border: 0;
    background: transparent;
    cursor: pointer;
}

.imas-gallery-video-play:focus-visible {
    outline: none;
    box-shadow: inset var(--ring);
}

.imas-gallery-video-play__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    border-radius: 100%;
    background: var(--brand-gold);
    color: var(--text-on-gold);
    font-size: 1.25rem;
    box-shadow: var(--shadow-md);
    transition: background 0.2s ease, transform 0.2s ease;
}

.imas-gallery-video-play:hover .imas-gallery-video-play__btn {
    background: var(--brand-gold-hover);
    transform: scale(1.05);
}

.imas-gallery-toolbar {
    display: flex;
    flex-direction: column;
    flex-wrap: nowrap;
    align-items: flex-start;
    gap: 0.65rem;
    margin-top: 10px;
}

.imas-gallery-counter {
    z-index: 40;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 4.75rem;
    margin: 0;
    padding: 0.45rem 0.8rem;
    border-radius: 4px;
    background: rgba(26, 42, 74, 0.78);
    color: #fff;
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
    pointer-events: none;
}

.imas-gallery-counter__icon {
    margin: 0 !important;
    flex: 0 0 auto;
    font-size: 0.85rem;
    line-height: 1;
    opacity: 0.92;
}

.imas-gallery-counter__text {
    flex: 0 0 auto;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.02em;
}

.imas-gallery-categories {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: flex-start;
    gap: 0;
    margin: 0;
    background: transparent;
}

.imas-gallery-categories__sep {
    margin: 0 0.65rem;
    color: var(--text-muted);
    font-weight: 400;
    line-height: 1;
    user-select: none;
}

.imas-gallery-categories__btn {
    display: inline-flex;
    align-items: baseline;
    gap: 0.3rem;
    margin: 0;
    padding: 0;
    border: 0;
    background: transparent;
    color: var(--text-dim);
    font-family: inherit;
    font-size: var(--text-sm);
    font-weight: 500;
    line-height: 1.4;
    cursor: pointer;
    transition: color 0.2s ease;
}

.imas-gallery-categories__btn:hover {
    color: var(--brand-gold);
}

.imas-gallery-categories__btn.is-active {
    color: var(--brand-gold);
    font-weight: 600;
}

.imas-gallery-categories__btn:focus,
.imas-gallery-categories__btn:focus-visible,
.imas-gallery-categories__btn:active {
    outline: none;
    box-shadow: none;
}

.imas-gallery-control {
    top: 50%;
    transform: translateY(-50%);
    margin-top: 0 !important;
}

.imas-gallery-thumbs-outer {
    width: 100%;
    max-width: 100%;
    margin-top: 15px;
    overflow-x: auto;
    overflow-y: hidden;
    unicode-bidi: isolate;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x;
    overscroll-behavior-x: contain;
}

.imas-gallery-thumbs {
    display: flex;
    flex-wrap: nowrap;
    align-items: flex-start;
    width: max-content !important;
    max-width: none;
    margin: 0 !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    padding: 0;
    white-space: nowrap;
}

.imas-property-gallery.listing-details-sliders
    :deep(.list-inline.imas-gallery-thumbs) {
    width: max-content !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
}

.imas-property-gallery :deep(.smail-listing .list-inline-item),
.imas-property-gallery :deep(.imas-gallery-thumbs .list-inline-item),
.imas-property-gallery :deep(.carousel-indicators > li) {
    width: auto !important;
    height: auto;
    max-width: none;
    flex: 0 0 auto;
    float: none;
    margin-inline-end: 10px;
    text-indent: 0;
}

.imas-property-gallery :deep(.carousel-indicators > li:last-child) {
    margin-inline-end: 0;
}

.imas-property-gallery :deep(.carousel-indicators a) {
    display: block;
    padding: 0;
    border: 2px solid transparent;
    border-radius: 4px;
    transition: border-color 0.2s ease;
}

.imas-property-gallery :deep(.carousel-indicators > li.active a),
.imas-property-gallery :deep(.carousel-indicators a.selected) {
    border-color: var(--brand-gold);
}

.imas-gallery-thumb__frame {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 127px;
    height: 90px;
    background: var(--surface-2);
    overflow: hidden;
    border-radius: 2px;
}

.imas-gallery-thumb__img {
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    object-position: center;
    display: block;
}

.imas-gallery-thumb__play {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(10, 21, 38, 0.35);
    color: var(--brand-gold);
    font-size: 0.85rem;
    pointer-events: none;
}

/* Theme sets thumb imgs to 600px — override so row stays inside the gallery. */
.imas-property-gallery :deep(.smail-listing .list-inline-item a img),
.imas-property-gallery :deep(.carousel-indicators a img) {
    width: 100% !important;
    max-width: 100% !important;
    height: 100% !important;
    max-height: 100% !important;
}

@media (max-width: 767.98px) {
    .imas-gallery-main__frame {
        height: 280px;
        max-height: 55vh;
    }

    .imas-gallery-thumb__frame {
        width: 88px;
        height: 64px;
    }

    .imas-gallery-video-play__btn {
        width: 52px;
        height: 52px;
        font-size: 1rem;
    }
}
.fa-angle-right:before,
.fa-angle-left:before {
    color: #fff;
}

/* Mirror chevrons in RTL so arrow direction matches swapped prev/next handlers.
   Parent show.vue already mirrors control positions (left↔right). */
html[dir="rtl"] .imas-property-gallery .imas-gallery-control .fa {
    transform: rotate(180deg);
}

.blog .blog-pots .imas-gallery-control .fa {
    margin: 6px 35% !important;
}
html[dir="rtl"] .blog .blog-pots .imas-gallery-control .fa {
    transform: rotate(180deg);
}
h5:after {
    margin-bottom: 0 !important;
}
</style>
