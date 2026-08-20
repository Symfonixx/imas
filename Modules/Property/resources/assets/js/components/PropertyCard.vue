<template>
    <div
        class="imas-property-card imas-property-card--media-overlay item user-select-none"
        :class="[columnClass, { 'imas-property-card--sold-out': isSoldOut }]"
    >
        <div class="project-single imas-card__surface">
            <Link
                v-if="!isSoldOut"
                :href="showUrl"
                class="imas-property-card__stretched-link"
                :aria-label="displayTitle"
            />
            <div class="project-inner project-head imas-card__media">
                <div class="homes">
                    <div class="homes-img">
                        <div
                            v-if="propertyTypeLabel || property.is_featured"
                            class="homes-tag button alt imas-badge--type"
                        >
                            <i
                                v-if="property.is_featured"
                                class="fa fa-star imas-featured-star"
                                aria-hidden="true"
                            ></i>
                            <span
                                v-if="property.is_featured"
                                class="visually-hidden"
                                >{{ trans("properties.featured") }}</span
                            >
                            <span v-if="propertyTypeLabel">{{
                                propertyTypeLabel
                            }}</span>
                        </div>
                        <div
                            v-if="property.is_sold_out"
                            class="homes-tag button alt imas-sold-out-badge imas-badge--danger"
                        >
                            {{ trans("properties.sold_out") }}
                        </div>
                        <img
                            :src="property.thumbnail_url"
                            :alt="property.thumbnail_alt || displayTitle"
                            :title="property.thumbnail_title || undefined"
                            class="img-responsive"
                            width="800"
                            height="600"
                            loading="lazy"
                            decoding="async"
                        />
                    </div>
                </div>
                <div class="imas-card-actions">
                    <div class="homes-price imas-start-price imas-chip">
                        <span class="imas-start-price__from">{{
                            trans("properties.price_from")
                        }}</span>
                        <span class="imas-start-price__amount">{{
                            priceAmount
                        }}</span>
                    </div>
                    <div v-if="!isSoldOut" class="button-effect">
                        <button
                            v-if="property.youtube_video_url"
                            type="button"
                            class="btn imas-card-video-btn"
                            :aria-label="playVideoLabel"
                            @click="openVideoLightbox"
                        >
                            <i class="fas fa-video" aria-hidden="true"></i>
                        </button>
                        <button
                            type="button"
                            class="btn imas-favorite-btn"
                            :class="{ 'is-favorited': localFavorited }"
                            :aria-label="favoriteAriaLabel"
                            :aria-pressed="localFavorited"
                            @click="onFavoriteClick"
                        >
                            <i
                                class="fa favorite-icon"
                                :class="
                                    localFavorited ? 'fa-heart' : 'fa-heart-o'
                                "
                                aria-hidden="true"
                            ></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="homes-content imas-card__body">
                <h3 class="imas-property-title imas-card__title">
                    <span class="imas-card__title-text">{{
                        displayTitle
                    }}</span>
                </h3>
                <p
                    v-if="overviewText"
                    class="imas-property-overview imas-card__excerpt text-card-excerpt mb-3"
                >
                    {{ overviewText }}
                </p>
                <p class="homes-address imas-card__meta text-base mb-3">
                    <span class="imas-card__address-line">
                        <i
                            class="fa fa-map-marker imas-address-marker"
                            aria-hidden="true"
                        ></i>
                        <span>{{ addressLine }}</span>
                    </span>
                </p>
                <PropertyCardUnitTypesBar
                    :unit-types="property.unit_types ?? []"
                />
            </div>
        </div>

        <VideoLightbox
            v-if="property.youtube_video_url && !isSoldOut"
            v-model="videoLightboxOpen"
            :video-url="property.youtube_video_url"
            :aria-label="videoLightboxAria"
            :invalid-message="videoInvalidMessage"
        />
    </div>
</template>

<script setup>
import axios from "axios";
import { computed, ref, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { useOpenAuthModal } from "@/composables/useOpenAuthModal";
import VideoLightbox from "@/components/Global/VideoLightbox.vue";
import { localizedField } from "../utils/propertyLocalized.js";
import {
    localizedLocationName,
    propertyLocationLine,
} from "../utils/propertyLocation.js";
import { formatPropertyMoney, propertyStartPrice } from "../utils/propertyPrice.js";
import PropertyCardUnitTypesBar from "./PropertyCardUnitTypesBar.vue";

const props = defineProps({
    property: {
        type: Object,
        required: true,
    },
    columnClass: {
        type: String,
        default: "col-lg-4 col-md-6 col-xs-12",
    },
});

const page = usePage();
const { openAuthModal } = useOpenAuthModal();

const trans = (key) => page.props.translations[key] || key;

const locale = computed(() => page.props.locale || "en");

const isAuthenticated = computed(() => page.props.auth != null);

const isSoldOut = computed(() => Boolean(props.property.is_sold_out));

const localFavorited = ref(Boolean(props.property.is_favorited));
const videoLightboxOpen = ref(false);

watch(
    () => props.property.is_favorited,
    (v) => {
        localFavorited.value = Boolean(v);
    },
);

const favoriteAriaLabel = computed(() =>
    localFavorited.value
        ? trans("properties.remove_favorite")
        : trans("properties.add_favorite"),
);

const propertyTypeLabel = computed(() => {
    const type = props.property.property_type;
    if (!type) {
        return "";
    }

    return localizedField(type.name, locale.value);
});

const displayTitle = computed(() => {
    const t = props.property.title;

    return typeof t === "string" && t.trim() !== ""
        ? t
        : props.property.project_name ||
              props.property.project_code ||
              "Property";
});

const showUrl = computed(() => {
    if (
        typeof props.property.url === "string" &&
        props.property.url.trim() !== ""
    ) {
        return props.property.url;
    }

    try {
        if (typeof route === "function" && route().has?.("property.show")) {
            const slug =
                props.property.url_key ||
                props.property.slug ||
                props.property.project_code;
            if (slug) {
                return route("property.show", slug);
            }
        }
    } catch {
        /* ignore */
    }

    const slug =
        props.property.url_key ||
        props.property.slug ||
        props.property.project_code;
    return slug ? `/property/${encodeURIComponent(slug)}` : "#";
});

const addressLine = computed(() => {
    const line = propertyLocationLine(props.property.location, locale.value);

    return line !== "" ? line : "—";
});

function stripHtml(value) {
    if (typeof value !== "string") {
        return "";
    }

    return value
        .replace(/<[^>]*>/g, " ")
        .replace(/\s+/g, " ")
        .trim();
}

const overviewText = computed(() =>
    stripHtml(localizedLocationName(props.property.overview, locale.value)),
);

function formatMoney(amount) {
    return formatPropertyMoney(amount, locale.value);
}

const priceAmount = computed(() =>
    formatMoney(propertyStartPrice(props.property)),
);

const playVideoLabel = computed(() => trans("property_show.play_video"));

const videoInvalidMessage = computed(() =>
    trans("property_show.video_unavailable"),
);

const videoLightboxAria = computed(
    () => `${playVideoLabel.value} – ${displayTitle.value}`,
);

function openVideoLightbox(e) {
    e.preventDefault();
    e.stopPropagation();
    if (isSoldOut.value) {
        return;
    }
    videoLightboxOpen.value = true;
}

async function onFavoriteClick(e) {
    e.preventDefault();
    e.stopPropagation();

    if (isSoldOut.value) {
        return;
    }

    if (!isAuthenticated.value) {
        openAuthModal("login");
        return;
    }

    const next = !localFavorited.value;
    const prev = localFavorited.value;
    localFavorited.value = next;

    try {
        const headers = {
            "X-CSRF-TOKEN": page.props.csrf,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        };
        if (next) {
            await axios.post(
                "/api/favorites",
                { property_id: props.property.id },
                { headers },
            );
        } else {
            await axios.delete(`/api/favorites/${props.property.id}`, {
                headers,
            });
        }
    } catch (err) {
        localFavorited.value = prev;
        const msg =
            (err.response?.data?.message &&
                String(err.response.data.message)) ||
            trans("properties.favorite_error");
        if (typeof window !== "undefined" && window.toastr) {
            window.toastr.error(msg);
        }
    }
}
</script>

<style scoped lang="scss">
/* Property card shell — self-contained (no .portfolio / .imas-card global dependency). */

.imas-property-card {
    width: 100%;
}

.imas-property-card .imas-card__surface,
.imas-property-card .project-single {
    position: relative;
    background: var(--surface) !important;
    border: none !important;
    border-radius: var(--card-radius) !important;
    box-shadow: var(--shadow-sm) !important;
    overflow: hidden;
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;
}

.imas-property-card:not(.imas-property-card--sold-out) .project-single {
    cursor: pointer;
}

.imas-property-card__stretched-link {
    position: absolute;
    inset: 0;
    z-index: 2;
    border-radius: inherit;
    text-decoration: none;
    color: inherit;
}

.imas-property-card__stretched-link:focus-visible {
    outline: none;
    box-shadow: var(--ring);
}

@media (prefers-reduced-motion: no-preference) {
    .imas-property-card:not(.imas-property-card--sold-out):hover
        .imas-card__surface,
    .imas-property-card:not(.imas-property-card--sold-out):hover
        .project-single {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg) !important;
    }
}

.imas-property-card--sold-out {
    cursor: not-allowed;
}

.imas-property-card--sold-out .imas-card__surface,
.imas-property-card--sold-out .project-single {
    opacity: 0.82;
    box-shadow: none !important;
}

.imas-property-card--sold-out .homes-img img.img-responsive {
    filter: grayscale(0.45);
    opacity: 0.9;
}

.imas-property-card--sold-out.imas-property-card--media-overlay
    .homes-img::after,
.imas-property-card--sold-out.imas-property-card--media-overlay
    .imas-card__media::after {
    background: linear-gradient(
        to top,
        color-mix(in srgb, var(--brand-navy-hover) 72%, transparent) 0%,
        transparent 58%
    );
}

.imas-property-card--sold-out .imas-card-actions .homes-price.imas-start-price {
    background: color-mix(in srgb, var(--text-dim) 12%, transparent) !important;
    color: var(--text-dim) !important;
}

.imas-property-card--sold-out .imas-card__title-text,
.imas-property-card--sold-out .imas-property-title .imas-card__title-text,
.imas-property-card--sold-out .homes-content h3 .imas-card__title-text {
    color: var(--text-dim) !important;
    cursor: not-allowed;
}

.imas-property-card--sold-out .imas-card__excerpt,
.imas-property-card--sold-out .imas-property-overview,
.imas-property-card--sold-out .imas-card__meta,
.imas-property-card--sold-out .homes-address,
.imas-property-card--sold-out .homes-address span {
    color: color-mix(in srgb, var(--text-dim) 88%, transparent) !important;
}

.imas-property-card--sold-out .homes-address .fa-map-marker,
.imas-property-card--sold-out .imas-address-marker {
    color: color-mix(in srgb, var(--text-dim) 75%, transparent) !important;
}

.imas-property-card--sold-out .imas-unit-types-bar {
    opacity: 0.75;
}

.imas-property-card .imas-card__media,
.imas-property-card .project-head,
.imas-property-card .project-inner.project-head {
    position: relative;
    overflow: hidden;
    background: var(--surface-2) !important;
    border-radius: var(--card-radius) var(--card-radius) 0 0;
}

.imas-property-card--media-overlay .homes-img::after,
.imas-property-card--media-overlay .imas-card__media::after {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    background: linear-gradient(
        to top,
        color-mix(in srgb, var(--brand-navy-hover) 88%, transparent) 0%,
        transparent 58%
    );
}

.imas-property-card .homes,
.imas-property-card .homes-img {
    position: relative;
    overflow: hidden;
}

.imas-property-card .homes-img {
    display: block;
    aspect-ratio: var(--card-media-ratio);
    background: var(--surface-2);
    text-decoration: none;
    color: inherit;
}

.imas-property-card .homes-img img.img-responsive {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    max-height: none;
    object-fit: cover;
    object-position: center;
    z-index: 0;
}

.imas-property-card .homes-tag {
    position: absolute;
    z-index: 2;
}

.imas-property-card .homes-tag.imas-badge--type {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center;
    gap: 0.35rem;
    line-height: 1.2 !important;
    background: var(--brand-gold) !important;
    color: var(--text-on-gold) !important;
    border-color: var(--brand-gold) !important;
    font-size: var(--text-card-chip) !important;
    font-weight: 700 !important;
    letter-spacing: 0.03em;
    text-transform: capitalize;
    padding: 6px 12px !important;
    border-radius: 4px !important;
    inset-inline-start: 15px;
    inset-inline-end: auto;
    margin-top: 15px;
    top: 0;
    width: auto !important;
    min-width: 0;
    max-width: calc(100% - 2rem);
    height: auto !important;
    white-space: nowrap;
    overflow: visible;
}

.imas-property-card .imas-badge--type > span {
    display: inline-flex;
    align-items: center;
    font-size: inherit !important;
    font-weight: inherit !important;
    line-height: 1.2;
}

.imas-property-card .imas-badge--type .imas-featured-star {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    align-self: center;
    flex-shrink: 0;
    width: 1em;
    height: 1em;
    margin: 0 !important;
    padding: 0 !important;
    font-size: var(--text-card-chip) !important;
    line-height: 1 !important;
    color: var(--text) !important;
}

.imas-property-card .imas-badge--type .imas-featured-star::before {
    display: block;
    line-height: 1;
}

.imas-property-card .imas-sold-out-badge {
    top: 0;
    margin-top: 15px;
    inset-inline-end: 15px;
    inset-inline-start: auto;
    background: var(--danger) !important;
    color: var(--text) !important;
    border-color: var(--danger) !important;
}

.imas-property-card .imas-card-actions {
    position: absolute;
    inset-inline: 15px;
    bottom: 0.7rem;
    z-index: 3;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    pointer-events: none;
}

.imas-property-card .imas-card-actions > * {
    pointer-events: auto;
}

.imas-property-card .imas-card-actions .homes-price.imas-start-price {
    position: static !important;
    bottom: auto !important;
    left: auto !important;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    height: auto;
    min-height: 34px;
    max-height: none;
    padding: 6px 12px;
    background: rgba(217, 168, 0, 0.1) !important;
    border-radius: 5px;
    color: var(--brand-gold) !important;
    font-size: var(--text-card-chip) !important;
    font-weight: 700 !important;
    line-height: 1.2;
    white-space: nowrap;
}

.imas-property-card .imas-start-price__from,
.imas-property-card .imas-start-price__amount {
    font-size: inherit !important;
    line-height: inherit;
}

.imas-property-card .imas-start-price__from {
    opacity: 0.92;
    text-transform: capitalize;
    font-weight: 500 !important;
}

.imas-property-card .imas-start-price__amount {
    font-weight: 700 !important;
}

.imas-property-card .imas-card-actions .button-effect {
    position: static !important;
    transform: none !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px;
    margin: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    border-radius: 0 !important;
}

.imas-property-card .imas-card-actions .button-effect .btn {
    display: inline-block !important;
    width: 31px !important;
    height: 31px !important;
    line-height: 31px !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    border-radius: 100% !important;
    background: var(--surface-2) !important;
    color: var(--text) !important;
    box-shadow: none !important;
    flex-shrink: 0;
    transition:
        background 0.2s ease,
        color 0.2s ease;
}

.imas-property-card .imas-card-actions .button-effect .btn:hover {
    background: var(--brand-gold) !important;
    color: var(--text-on-gold) !important;
}

.imas-property-card .imas-card-actions .button-effect .btn:focus-visible {
    outline: none;
    box-shadow: var(--ring) !important;
}

.imas-property-card .imas-favorite-btn:not(.is-favorited) i,
.imas-property-card .imas-card-video-btn i {
    color: var(--text) !important;
}

.imas-property-card .imas-card-actions .button-effect .btn:hover i {
    color: var(--text-on-gold) !important;
}

.imas-property-card .imas-favorite-btn.is-favorited i {
    color: var(--brand-gold) !important;
}

.imas-property-card .imas-favorite-btn.is-favorited:hover i {
    color: var(--text-on-gold) !important;
}

.imas-property-card .imas-card__body,
.imas-property-card .homes-content {
    padding: var(--card-body-padding) !important;
    background: var(--surface) !important;
    color: var(--text) !important;
    text-align: start;
}

.imas-property-card .imas-card__title,
.imas-property-card .imas-property-title,
.imas-property-card .homes-content h3 {
    margin-top: 0;
    margin-bottom: 0.5rem;
    min-height: calc(1.4em * 2);
    font-family: var(--font-body) !important;
    font-size: var(--text-lg) !important;
    font-weight: 600 !important;
    line-height: 1.4 !important;
    color: var(--text) !important;
    text-transform: none !important;
}

.imas-property-card .imas-card__title-text,
.imas-property-card .imas-property-title .imas-card__title-text,
.imas-property-card .homes-content h3 .imas-card__title-text {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
    font-family: var(--font-body) !important;
    color: var(--text) !important;
    text-transform: none !important;
    transition: color 0.2s ease;
}

.imas-property-card:not(.imas-property-card--sold-out):hover
    .imas-card__title-text {
    color: var(--brand-gold) !important;
}

.imas-property-card .imas-card__excerpt,
.imas-property-card .imas-property-overview {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
    color: var(--text-dim) !important;
    font-family: var(--font-body) !important;
    font-size: var(--text-card-excerpt) !important;
    line-height: 1.55;
}

.imas-property-card .imas-card__meta,
.imas-property-card .homes-address,
.imas-property-card .homes-address .imas-card__address-line,
.imas-property-card .homes-address .imas-card__address-line span {
    color: var(--text-dim) !important;
    font-size: var(--text-base) !important;
}

.imas-property-card .homes-address .fa-map-marker,
.imas-property-card .imas-address-marker {
    color: var(--brand-gold) !important;
    margin-inline-end: 10px;
}
.imas-badge--danger {
    padding: 6px 12px !important;
    border-radius: 4px !important;
}
</style>
