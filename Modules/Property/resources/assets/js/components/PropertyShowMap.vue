<template>
    <div v-if="embedUrl" class="property-location map imas-property-map mb-30">
        <h5 class="imas-section-title">{{ title }}</h5>
        <div class="divider-fade"></div>
        <div class="contact-map imas-property-map__frame">
            <iframe
                :src="embedUrl"
                :title="title"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
        </div>
    </div>
    <p
        v-else-if="unavailableText"
        class="text-muted imas-property-map-unavailable mb-30"
    >
        {{ unavailableText }}
    </p>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    lat: { type: [Number, String], default: null },
    lng: { type: [Number, String], default: null },
    title: { type: String, default: "Location" },
    unavailableText: { type: String, default: "" },
});

const embedUrl = computed(() => {
    const lat = Number(props.lat);
    const lng = Number(props.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        return "";
    }

    const pad = 0.02;
    const bbox = [lng - pad, lat - pad, lng + pad, lat + pad].join(",");

    return `https://www.openstreetmap.org/export/embed.html?bbox=${encodeURIComponent(bbox)}&layer=mapnik&marker=${lat}%2C${lng}`;
});
</script>

<style scoped>
.imas-property-map__frame {
    height: 365px;
    overflow: hidden;
    border-radius: 4px;
}

.imas-property-map__frame iframe {
    width: 100%;
    height: 100%;
    border: 0;
}
.imas-property-map h5:after{
margin-bottom:0 !important;
}
</style>
