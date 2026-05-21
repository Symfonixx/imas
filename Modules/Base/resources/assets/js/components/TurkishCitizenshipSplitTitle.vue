<template>
    <header
        ref="rootRef"
        class="imas-tc-split-title"
        :class="{
            'imas-tc-split-title--center': align === 'center',
            'imas-tc-split-title--start': align === 'start',
        }"
    >
        <h2 class="imas-tc-split-title__heading">
            <span class="imas-tc-split-title__primary">{{ primary }}</span>
            <span class="imas-tc-split-title__accent">{{ accent }}</span>
        </h2>
        <hr v-if="showDivider" class="imas-tc-split-title__divider" />
    </header>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useGsap } from "@/composables/useGsap";

const props = defineProps({
    primary: {
        type: String,
        required: true,
    },
    accent: {
        type: String,
        required: true,
    },
    showDivider: {
        type: Boolean,
        default: true,
    },
    align: {
        type: String,
        default: "start",
        validator: (value) => value === "center" || value === "start",
    },
    /** Slide-in reveal for primary then accent (Turkish Citizenship page only). */
    reveal: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const rootRef = ref(null);
const { gsap, context, prefersReducedMotion, refreshScrollTrigger } = useGsap();

const isRtl = computed(() => {
    const dir = page.props.text_direction;
    if (dir === "rtl" || dir === "ltr") {
        return dir === "rtl";
    }
    return (page.props.locale || "en") === "ar";
});

const revealFromX = computed(() => (isRtl.value ? 56 : -56));

let hasRevealed = false;

function setupReveal() {
    const root = rootRef.value;
    if (!root || !props.reveal || hasRevealed) {
        return;
    }

    if (prefersReducedMotion()) {
        hasRevealed = true;
        return;
    }

    const primary = root.querySelector(".imas-tc-split-title__primary");
    const accent = root.querySelector(".imas-tc-split-title__accent");
    const divider = root.querySelector(".imas-tc-split-title__divider");
    const fromX = revealFromX.value;

    context(() => {
        const hidden = [primary, accent, divider].filter(Boolean);
        gsap.set(hidden, { opacity: 0, x: fromX });

        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: root,
                start: "top 88%",
                once: true,
                toggleActions: "play none none none",
            },
            defaults: { ease: "power2.out" },
        });

        if (primary) {
            tl.to(primary, { opacity: 1, x: 0, duration: 0.9 }, 0);
        }
        if (accent) {
            tl.to(
                accent,
                { opacity: 1, x: 0, duration: 0.9 },
                primary ? 0.14 : 0,
            );
        }
        if (divider) {
            tl.to(
                divider,
                { opacity: 1, x: 0, duration: 0.55 },
                accent ? 0.1 : primary ? 0.12 : 0,
            );
        }
    }, rootRef);

    hasRevealed = true;
    refreshScrollTrigger();
}

onMounted(() => {
    if (!props.reveal) {
        return;
    }
    nextTick(() => {
        nextTick(setupReveal);
    });
});
</script>

<style scoped lang="scss">
.imas-tc-split-title {
    margin-bottom: 1.5rem;
}

.imas-tc-split-title--center {
    text-align: center;
}

.imas-tc-split-title--start {
    text-align: start;
}

.imas-tc-split-title__heading {
    margin: 0;
    font-size: clamp(1.35rem, 2.2vw, 1.75rem);
    font-weight: 700;
    line-height: 1.4;
}

.imas-tc-split-title__primary {
    display: block;
    color: var(--text);
}

.imas-tc-split-title__accent {
    display: block;
    color: var(--brand-gold);
}

.imas-tc-split-title__divider {
    margin: 1rem 0 0;
    border: 0;
    height: 3px;
    width: 72px;
    background: var(--brand-gold);
}

.imas-tc-split-title--center .imas-tc-split-title__divider {
    margin-left: auto;
    margin-right: auto;
}
</style>
