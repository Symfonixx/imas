<template>
    <div
        class="imas-property-card imas-card imas-card--property imas-card--media-overlay item user-select-none"
        :class="columnClass"
    >
        <div class="project-single imas-card__surface">
            <div class="project-inner project-head imas-card__media">
                <div class="homes">
                    <a :href="property.url" class="homes-img">
                        <div
                            v-if="propertyTypeLabel || property.is_featured"
                            class="homes-tag button alt imas-badge--type"
                        >
                            <i
                                v-if="property.is_featured"
                                class="fa fa-star imas-featured-star"
                                :aria-label="trans('properties.featured')"
                            ></i>
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
                            :alt="displayTitle"
                            class="img-responsive"
                        />
                    </a>
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
                    <div class="button-effect">
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
                    <Link :href="showUrl">{{ displayTitle }}</Link>
                </h3>
                <p
                    v-if="overviewText"
                    class="imas-property-overview imas-card__excerpt text-card-excerpt mb-3"
                >
                    {{ overviewText }}
                </p>
                <p class="homes-address imas-card__meta text-base mb-3">
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

        <VideoLightbox
            v-if="property.youtube_video_url"
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
const { openAuthModal } = useOpenAuthModal();

const trans = (key) => page.props.translations[key] || key;

const locale = computed(() => page.props.locale || "en");

const isAuthenticated = computed(() => page.props.auth != null);

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
    videoLightboxOpen.value = true;
}

async function onFavoriteClick(e) {
    e.preventDefault();
    e.stopPropagation();

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
/* Property-specific layout; shell/colors from global .imas-card in app.css */

.imas-property-card .imas-card-actions .homes-price.imas-start-price {
    position: static !important;
    bottom: auto !important;
    left: auto !important;
    flex-shrink: 0;
}

.imas-start-price__from {
    opacity: 0.92;
    text-transform: capitalize;
}

.imas-property-card .imas-property-title {
    min-height: calc(1.4em * 2);
}

.imas-property-card .imas-property-title a {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
}

.imas-property-card .imas-property-overview {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
}

.imas-property-card .imas-sold-out-badge {
    top: 0;
    margin-top: 15px;
    right: 15px;
    left: auto;
}

:global(html[dir="rtl"]) .imas-property-card .imas-sold-out-badge {
    right: auto !important;
    left: 15px !important;
}

.imas-property-card .homes-address .imas-address-marker {
    margin-inline-end: 10px;
}
</style>
