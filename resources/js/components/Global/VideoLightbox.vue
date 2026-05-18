<template>
    <Teleport to="body">
        <Transition name="imas-video-lightbox-fade">
            <div
                v-if="modelValue"
                class="imas-video-lightbox"
                role="dialog"
                aria-modal="true"
                :aria-label="ariaLabel"
                @keydown.esc="close"
            >
                <button
                    type="button"
                    class="imas-video-lightbox__backdrop"
                    :aria-label="closeLabel"
                    @click="close"
                />
                <div class="imas-video-lightbox__dialog">
                    <button
                        type="button"
                        class="imas-video-lightbox__close"
                        :aria-label="closeLabel"
                        @click="close"
                    >
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                    <div class="imas-video-lightbox__content">
                        <iframe
                            v-if="playback?.type === 'iframe' && activeSrc"
                            :key="activeSrc"
                            :src="activeSrc"
                            class="imas-video-lightbox__iframe"
                            :title="ariaLabel"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                        />
                        <video
                            v-else-if="playback?.type === 'file' && activeSrc"
                            :key="activeSrc"
                            class="imas-video-lightbox__video"
                            :src="activeSrc"
                            controls
                            playsinline
                        />
                        <p
                            v-else
                            class="imas-video-lightbox__error text-center mb-0"
                        >
                            {{ invalidMessage }}
                        </p>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, onBeforeUnmount, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import {
    resolveVideoPlayback,
    withVideoAutoplay,
} from "@/utils/videoEmbed.js";

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    videoUrl: { type: String, default: "" },
    ariaLabel: { type: String, default: "Video player" },
    invalidMessage: { type: String, default: "Video is not available." },
});

const emit = defineEmits(["update:modelValue"]);

const page = usePage();

const closeLabel = computed(
    () => page.props.translations?.Close || page.props.translations?.close || "Close",
);

const playback = computed(() => resolveVideoPlayback(props.videoUrl));

const activeSrc = computed(() => {
    if (!props.modelValue || !playback.value) {
        return "";
    }

    if (playback.value.type === "iframe") {
        return withVideoAutoplay(playback.value.src);
    }

    return playback.value.src;
});

function close() {
    emit("update:modelValue", false);
}

function lockBodyScroll(lock) {
    if (typeof document === "undefined") {
        return;
    }
    document.body.style.overflow = lock ? "hidden" : "";
}

watch(
    () => props.modelValue,
    (open) => {
        lockBodyScroll(open);
    },
);

onBeforeUnmount(() => {
    lockBodyScroll(false);
});
</script>

<style scoped lang="scss">
.imas-video-lightbox {
    position: fixed;
    inset: 0;
    z-index: var(--z-video-lightbox, 100100);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
}

.imas-video-lightbox__backdrop {
    position: absolute;
    inset: 0;
    border: 0;
    padding: 0;
    margin: 0;
    background: rgba(26, 42, 74, 0.72);
    cursor: pointer;
}

.imas-video-lightbox__dialog {
    position: relative;
    z-index: 1;
    width: min(960px, 100%);
    max-height: calc(100vh - 2.5rem);
    background: #000;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
}

.imas-video-lightbox__close {
    position: absolute;
    top: 10px;
    inset-inline-end: 10px;
    z-index: 2;
    width: 40px;
    height: 40px;
    border: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.92);
    color: var(--brand-navy, #1a2a4a);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
}

.imas-video-lightbox__close:hover,
.imas-video-lightbox__close:focus {
    background: #fff;
    color: var(--brand-gold, #d9a800);
}

.imas-video-lightbox__content {
    width: 100%;
    aspect-ratio: 16 / 9;
    background: #000;
}

.imas-video-lightbox__iframe,
.imas-video-lightbox__video {
    width: 100%;
    height: 100%;
    display: block;
    border: 0;
    background: #000;
}

.imas-video-lightbox__error {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 1.5rem;
    color: #fff;
}

.imas-video-lightbox-fade-enter-active,
.imas-video-lightbox-fade-leave-active {
    transition: opacity 0.2s ease;
}

.imas-video-lightbox-fade-enter-from,
.imas-video-lightbox-fade-leave-to {
    opacity: 0;
}
</style>
