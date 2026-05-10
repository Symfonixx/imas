<template>
    <div class="item col-lg-4 col-md-6 col-xs-12">
        <div class="project-single">
            <div class="project-inner project-head">
                <div class="homes">
                    <a :href="property.url" class="homes-img">
                        <div
                            v-if="property.is_featured"
                            class="homes-tag button alt featured"
                        >
                            Featured
                        </div>
                        <div
                            v-if="property.is_sold_out"
                            class="homes-tag button alt imas-sold-out-badge"
                        >
                            Sold Out
                        </div>
                        <div class="homes-price">{{ priceLabel }}</div>
                        <img
                            :src="property.thumbnail_url"
                            :alt="displayTitle"
                            class="img-responsive"
                        >
                    </a>
                </div>
                <div class="button-effect">
                    <a :href="property.url" class="btn"><i class="fa fa-link"></i></a>
                    <a
                        v-if="property.youtube_video_url"
                        :href="property.youtube_video_url"
                        class="btn popup-video popup-youtube"
                        target="_blank"
                        rel="noopener noreferrer"
                    ><i class="fas fa-video"></i></a>
                    <a
                        :href="property.thumbnail_url"
                        class="img-poppu btn"
                        target="_blank"
                        rel="noopener noreferrer"
                    ><i class="fa fa-photo"></i></a>
                </div>
            </div>
            <div class="homes-content">
                <h3><a :href="property.url">{{ displayTitle }}</a></h3>
                <p class="homes-address mb-3">
                    <a :href="property.url">
                        <i class="fa fa-map-marker"></i><span>{{ addressLine }}</span>
                    </a>
                </p>
                <ul
                    v-if="hasHomesList"
                    class="homes-list clearfix pb-3"
                >
                    <li
                        v-for="(attr, idx) in homesAttributes"
                        :key="`${attr.code}-${idx}`"
                        class="the-icons"
                    >
                        <i
                            :class="[attributeIconClass(attr.code), 'mr-2']"
                            aria-hidden="true"
                        ></i>
                        <span>{{ attr.display }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import {computed} from 'vue';
import {usePage} from '@inertiajs/vue3';

const props = defineProps({
    property: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const locale = computed(() => page.props.locale || 'en');

const displayTitle = computed(() => {
    const t = props.property.title;

    return typeof t === 'string' && t.trim() !== '' ? t : props.property.project_name || props.property.project_code || 'Property';
});

const addressLine = computed(() => {
    const loc = props.property.location?.name;

    return typeof loc === 'string' && loc.trim() !== '' ? loc : '—';
});

function formatMoney(amount) {
    const n = Number(amount);
    if (! Number.isFinite(n)) {
        return '—';
    }

    return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(n);
}

const priceLabel = computed(() => formatMoney(props.property.price));

const homesAttributes = computed(() =>
    Array.isArray(props.property.attributes) ? props.property.attributes : []
);

const hasHomesList = computed(() => homesAttributes.value.length > 0);

const ATTRIBUTE_ICON_CLASS = {
    built_in_area: 'flaticon-square',
    bedrooms: 'flaticon-bed',
    bedroom: 'flaticon-bed',
    bathrooms: 'flaticon-bathtub',
    bathroom: 'flaticon-bathtub',
    garage: 'flaticon-car',
    garages: 'flaticon-car',
    parking: 'flaticon-car',
};

function attributeIconClass(code) {
    if (! code || typeof code !== 'string') {
        return 'flaticon-square';
    }

    return ATTRIBUTE_ICON_CLASS[code.toLowerCase()] || 'flaticon-square';
}

</script>

<style scoped>
.imas-sold-out-badge {
    background-color: #dc3545 !important;
    color: #fff !important;
    border-color: #dc3545 !important;
}

.homes-img .imas-sold-out-badge:hover {
    color: #fff !important;
}
</style>
