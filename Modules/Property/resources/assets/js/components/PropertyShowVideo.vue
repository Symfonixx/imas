<template>
    <div
        class="property wprt-image-video w50 pro imas-property-video imas-property-show-panel mb-30"
    >
        <h5 class="imas-section-title mb-4">
            {{ title }}
        </h5>

        <div class="imas-property-video__stage">
            <img
                :src="posterUrl"
                :alt="posterAlt"
                class="imas-property-video__poster"
            />
            <button
                type="button"
                class="imas-property-video__play"
                :aria-label="playLabel"
                @click="lightboxOpen = true"
            >
                <span class="imas-property-video__play-btn" aria-hidden="true">
                    <i class="fa fa-play"></i>
                </span>
                <span class="imas-property-video__ripple" aria-hidden="true">
                    <span class="imas-property-video__ripple-ring"></span>
                    <span class="imas-property-video__ripple-ring"></span>
                    <span class="imas-property-video__ripple-ring"></span>
                </span>
            </button>
        </div>

        <VideoLightbox
            v-model="lightboxOpen"
            :video-url="videoUrl"
            :aria-label="title"
            :invalid-message="invalidMessageText"
        />
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import VideoLightbox from "@/components/Global/VideoLightbox.vue";

const props = defineProps({
    videoUrl: { type: String, required: true },
    posterUrl: { type: String, required: true },
    posterAlt: { type: String, default: "" },
    title: { type: String, required: true },
    invalidMessage: { type: String, default: "" },
});

const page = usePage();
const lightboxOpen = ref(false);

const playLabel = computed(
    () =>
        page.props.translations?.["property_show.play_video"] ||
        "Play property video",
);

const invalidMessageText = computed(
    () =>
        props.invalidMessage ||
        page.props.translations?.["property_show.video_unavailable"] ||
        "Video is not available.",
);
</script>

<style scoped lang="scss">
.imas-property-video {
    position: relative;
    text-align: start;
}

.imas-property-video .imas-section-title {
    font-size: var(--text-md);
    font-weight: 700;
    color: var(--text);
}

.imas-property-video__stage {
    position: relative;
    width: 100%;
    height: 360px;
    max-height: min(360px, 55vh);
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--card-radius);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.imas-property-video__poster {
    width: 100%;
    height: 100% !important;
    max-height: none;
    object-fit: contain;
    object-position: center;
    display: block;
}

.imas-property-video__play {
    position: absolute;
    inset: 0;
    z-index: 3;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 0;
    border: 0;
    background: transparent;
    cursor: pointer;
}

.imas-property-video__play-btn {
    position: relative;
    z-index: 2;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: var(--brand-gold);
    color: var(--text-on-gold);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-md);
    transition:
        transform 0.2s ease,
        background 0.2s ease,
        box-shadow 0.2s ease;
}

.imas-property-video__play-btn i {
    font-size: 28px;
    margin-inline-start: 4px;
    line-height: 1;
    color: var(--text-on-gold) !important;
}

.imas-property-video__play:hover .imas-property-video__play-btn,
.imas-property-video__play:focus-visible .imas-property-video__play-btn {
    transform: scale(1.05);
    background: var(--brand-gold-hover);
    box-shadow: var(--shadow-lg);
}

.imas-property-video__play:focus-visible .imas-property-video__play-btn {
    box-shadow: var(--ring), var(--shadow-md);
}

.imas-property-video__ripple {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    z-index: 1;
}

.imas-property-video__ripple-ring {
    position: absolute;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 2px solid color-mix(in srgb, var(--brand-gold) 55%, transparent);
    animation: imas-video-ripple 2.4s ease-out infinite;
}

.imas-property-video__ripple-ring:nth-child(2) {
    animation-delay: 0.8s;
}

.imas-property-video__ripple-ring:nth-child(3) {
    animation-delay: 1.6s;
}

@keyframes imas-video-ripple {
    0% {
        transform: scale(1);
        opacity: 0.65;
    }
    100% {
        transform: scale(2.4);
        opacity: 0;
    }
}

@media (max-width: 767.98px) {
    .imas-property-video__stage {
        height: 240px;
        max-height: 50vh;
    }

    .imas-property-video__play-btn {
        width: 58px;
        height: 58px;
    }

    .imas-property-video__play-btn i {
        font-size: 22px;
    }

    .imas-property-video__ripple-ring {
        width: 58px;
        height: 58px;
    }
}
</style>

<style lang="scss">
/* Override Find Houses white card on .wprt-image-video.w50.pro */
.imas-property-show-page .imas-property-video.imas-property-show-panel {
    padding: 1.5rem !important;
    background: var(--surface) !important;
    border: 1px solid var(--border) !important;
    border-radius: var(--card-radius);
    box-shadow: var(--shadow-sm) !important;
}

.imas-property-show-page .imas-property-video.imas-property-show-panel h5.imas-section-title {
    color: var(--text) !important;
    text-transform: none !important;
}

.imas-property-show-page
    .imas-property-video.imas-property-show-panel
    h5.imas-section-title::after {
    display: block !important;
    content: "" !important;
    width: 3.5rem !important;
    height: 2px !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    background: var(--brand-gold) !important;
}
</style>
