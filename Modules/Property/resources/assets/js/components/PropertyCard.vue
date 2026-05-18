<template>
    <div class="imas-property-card item user-select-none" :class="columnClass">
        <div class="project-single">
            <div class="project-inner project-head">
                <div class="homes">
                    <a :href="property.url" class="homes-img">
                        <div
                            v-if="property.is_featured"
                            class="homes-tag button alt featured"
                        >
                            {{ trans("properties.featured") }}
                        </div>
                        <div
                            v-if="property.is_sold_out"
                            class="homes-tag button alt imas-sold-out-badge"
                        >
                            {{ trans("properties.sold_out") }}
                        </div>
                        <img
                            :src="property.thumbnail_url"
                            :alt="displayTitle"
                            class="img-responsive"
                        />
                    </a>
                </div>
                <div class="imas-card-actions">
                    <div class="homes-price imas-start-price">
                        <span class="imas-start-price__from">{{
                            trans("properties.price_from")
                        }}</span>
                        <span class="imas-start-price__amount">{{
                            priceAmount
                        }}</span>
                    </div>
                    <div class="button-effect">
                        <a
                        v-if="property.youtube_video_url"
                        :href="property.youtube_video_url"
                        class="btn popup-video popup-youtube"
                        target="_blank"
                        rel="noopener noreferrer"
                        ><i class="fas fa-video"></i
                        ></a>
                        <a
                            :href="property.thumbnail_url"
                            class="img-poppu btn"
                            target="_blank"
                            rel="noopener noreferrer"
                            ><i class="fa fa-photo"></i
                        ></a>
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
                            :class="localFavorited ? 'fa-heart' : 'fa-heart-o'"
                            aria-hidden="true"
                        ></i>
                    </button>
                    </div>
                </div>
            </div>
            <div class="homes-content">
                <h3 class="imas-property-title">
                    <Link :href="showUrl">{{ displayTitle }}</Link>
                </h3>
                <p
                    v-if="overviewText"
                    class="imas-property-overview mb-3"
                >
                    {{ overviewText }}
                </p>
                <p class="homes-address mb-3">
                    <a :href="property.url">
                        <i
                            class="fa fa-map-marker imas-address-marker"
                            aria-hidden="true"
                        ></i>
                        <span>{{ addressLine }}</span>
                    </a>
                </p>
                <PropertyCardUnitTypesBar
                    :unit-types="property.unit_types ?? []"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from "axios";
import { computed, ref, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import {
    localizedLocationName,
    propertyLocationLine,
} from "../utils/propertyLocation.js";
import { propertyStartPrice } from "../utils/propertyPrice.js";
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

const trans = (key) => page.props.translations[key] || key;

const locale = computed(() => page.props.locale || "en");

const isAuthenticated = computed(() => page.props.auth != null);

const localFavorited = ref(Boolean(props.property.is_favorited));

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

const displayTitle = computed(() => {
    const t = props.property.title;

    return typeof t === "string" && t.trim() !== ""
        ? t
        : props.property.project_name ||
              props.property.project_code ||
              "Property";
});

const showUrl = computed(() => {
    if (typeof props.property.url === "string" && props.property.url.trim() !== "") {
        return props.property.url;
    }

    try {
        if (typeof route === "function" && route().has?.("property.show")) {
            return route("property.show", props.property.id);
        }
    } catch {
        /* ignore */
    }

    return `/property/${props.property.id}`;
});

const addressLine = computed(() => {
    const line = propertyLocationLine(
        props.property.location,
        locale.value,
    );

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
    const n = Number(amount);
    if (!Number.isFinite(n)) {
        return "—";
    }

    return new Intl.NumberFormat(locale.value, {
        style: "currency",
        currency: "USD",
        maximumFractionDigits: 0,
    }).format(n);
}

const priceAmount = computed(() =>
    formatMoney(propertyStartPrice(props.property)),
);

async function onFavoriteClick(e) {
    e.preventDefault();
    e.stopPropagation();

    if (!isAuthenticated.value) {
        router.visit(route("login"));
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
/* Uniform image frame; thumbnails use cover (no stretch). Badges stay above (z-index). */
.imas-property-card .homes-img {
    aspect-ratio: 4 / 3;
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

/* Reserve two lines so short titles align; long titles clamp with ellipsis. */
.imas-property-title {
    margin-top: 0;
    margin-bottom: 0.5rem;
    min-height: calc(1.35em * 2);
    line-height: 1.35;
    text-align: start;
}

.imas-property-title a {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
}

.imas-property-overview {
    margin-top: 0;
    margin-bottom: 0.75rem;
    line-height: 1.45;
    font-size: 0.9rem;
    color: var(--color-text-muted, #666);
    text-align: start;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
}

/* Price + action buttons share one bottom row (theme buttons are 31×31px). */
.imas-property-card .imas-card-actions {
    position: absolute;
    inset-inline: 15px;
    bottom: 0.7rem;
    z-index: 33;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    pointer-events: none;
}

.imas-property-card .imas-card-actions > * {
    pointer-events: auto;
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
    margin: 0 !important;
    flex-shrink: 0;
}

.imas-property-card .homes-price.imas-start-price {
    position: static !important;
    bottom: auto !important;
    left: auto !important;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    height: 31px;
    min-height: 31px;
    max-height: 31px;
    padding: 0 10px;
    background: var(--brand-gold) !important;
    border-radius: 5px;
    color: #fff !important;
    font-size: 12px !important;
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
    flex-shrink: 0;
}

.imas-start-price__from {
    font-weight: 500;
    opacity: 0.92;
    text-transform: capitalize;
}

.imas-start-price__amount {
    font-weight: 700;
}

.imas-sold-out-badge {
    background-color: #dc3545 !important;
    color: #fff !important;
    border-color: #dc3545 !important;
    top: 0;
    margin-top: 15px;
    right: 15px;
    left: auto;
}

:global(html[dir="rtl"]) .imas-sold-out-badge {
    right: auto !important;
    left: 15px !important;
}

.homes-img .imas-sold-out-badge:hover {
    color: #fff !important;
}

.homes-address {
    text-align: start;
}

.homes-address .imas-address-marker {
    margin-inline-end: 10px;
}

.imas-favorite-btn:not(.is-favorited) i {
    color: #fff !important;
}

.imas-favorite-btn.is-favorited i {
    color: #d9a800;
}
</style>
