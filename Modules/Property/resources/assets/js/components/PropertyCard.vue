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
                        <div class="homes-price">{{ priceLabel }}</div>
                        <img
                            :src="property.thumbnail_url"
                            :alt="displayTitle"
                            class="img-responsive"
                        />
                    </a>
                </div>
                <div class="button-effect">
                    <a :href="property.url" class="btn"
                        ><i class="fa fa-link"></i
                    ></a>
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
            <div class="homes-content">
                <h3 class="imas-property-title">
                    <a :href="property.url">{{ displayTitle }}</a>
                </h3>
                <p class="homes-address mb-3">
                    <a :href="property.url">
                        <i
                            class="fa fa-map-marker imas-address-marker"
                            aria-hidden="true"
                        ></i>
                        <span>{{ addressLine }}</span>
                    </a>
                </p>
                <ul
                    v-if="hasHomesList"
                    class="homes-list imas-homes-attrs pb-3"
                >
                    <li
                        v-for="(attr, idx) in homesAttributes"
                        :key="`${attr.code}-${idx}`"
                        class="the-icons imas-homes-attr"
                    >
                        <i
                            :class="attributeIconClass(attr.code)"
                            class="imas-homes-attr__icon"
                            aria-hidden="true"
                        ></i>
                        <span class="imas-homes-attr__text" dir="auto">{{
                            attr.display
                        }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from "axios";
import { computed, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";

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

const addressLine = computed(() => {
    const loc = props.property.location?.name;

    return typeof loc === "string" && loc.trim() !== "" ? loc : "—";
});

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

const priceLabel = computed(() => formatMoney(props.property.price));

const homesAttributes = computed(() =>
    Array.isArray(props.property.highlights) ? props.property.highlights : [],
);

const hasHomesList = computed(() => homesAttributes.value.length > 0);

const ATTRIBUTE_ICON_CLASS = {
    built_in_area: "flaticon-square",
    bedrooms: "flaticon-bed",
    bedroom: "flaticon-bed",
    bathrooms: "flaticon-bathtub",
    bathroom: "flaticon-bathtub",
    garage: "flaticon-car",
    garages: "flaticon-car",
    parking: "flaticon-car",
};

function attributeIconClass(code) {
    if (!code || typeof code !== "string") {
        return "flaticon-square";
    }

    return ATTRIBUTE_ICON_CLASS[code.toLowerCase()] || "flaticon-square";
}

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

.portfolio .imas-property-card .homes-content ul.imas-homes-attrs,
.imas-property-card .homes-content ul.imas-homes-attrs {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 0.75rem;
    row-gap: 0.25rem;
    padding-left: 0 !important;
    padding-inline-start: 0 !important;
    margin: 0;
    list-style: none;
}

.portfolio
    .imas-property-card
    .homes-content
    ul.imas-homes-attrs
    li.imas-homes-attr,
.imas-property-card .homes-content ul.imas-homes-attrs li.imas-homes-attr {
    float: none !important;
    width: 100% !important;
    min-width: 0;
    max-width: 100%;
    line-height: 1.35 !important;
    padding-top: 0.35rem !important;
    padding-bottom: 0.35rem !important;
    display: flex !important;
    align-items: center;
}

.portfolio
    .imas-property-card
    .homes-content
    ul.imas-homes-attrs
    li
    i.imas-homes-attr__icon,
.imas-property-card
    .homes-content
    ul.imas-homes-attrs
    li
    i.imas-homes-attr__icon {
    margin-right: 0 !important;
    margin-left: 0 !important;
    margin-inline-end: 0.35rem !important;
    flex-shrink: 0;
}

.imas-property-card .homes-content ul.imas-homes-attrs .imas-homes-attr__text {
    min-width: 0;
    text-align: start;
}

.imas-favorite-btn:not(.is-favorited) i {
    color: #fff !important;
}

.imas-favorite-btn.is-favorited i {
    color: #d9a800;
}
</style>
