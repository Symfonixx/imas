<template>
    <section
        v-if="isVisible"
        ref="sectionRef"
        class="imas-tc-overview"
        :aria-label="sectionTitle"
    >
        <div
            class="imas-tc-overview__bg"
            :style="backgroundStyle"
            aria-hidden="true"
        />
        <div class="imas-tc-overview__overlay" aria-hidden="true" />
        <div class="container imas-tc-overview__inner">
            <div ref="panelRef" class="imas-tc-overview__panel">
                <TurkishCitizenshipSplitTitle
                    :primary="titlePrimary"
                    :accent="titleAccent"
                    align="center"
                />
                <p
                    v-if="summaryText"
                    class="imas-tc-overview__text"
                    :title="summaryText"
                >
                    <span class="imas-tc-overview__text-flow">
                        <span
                            v-for="(word, index) in summaryWords"
                            :key="`${word}-${index}`"
                            class="imas-tc-overview__word"
                            >{{ word }}&nbsp;</span
                        >
                        <span
                            v-if="summaryWords.length"
                            class="imas-tc-overview__cursor"
                            aria-hidden="true"
                            >|</span
                        >
                    </span>
                </p>
                <a :href="citizenshipHref" class="imas-tc-overview__cta">
                    <span>{{ discoverLabel }}</span>
                </a>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, nextTick, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useGsap } from "@/composables/useGsap";
import TurkishCitizenshipSplitTitle from "./TurkishCitizenshipSplitTitle.vue";

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}

function pickTranslation(key, fallback) {
    const value = trans(key);
    if (value && value !== key) {
        return value;
    }
    return fallback;
}

const globals = computed(() => page.props.globals ?? {});
const seo = computed(() => globals.value.seo ?? {});
const turkishCitizenship = computed(
    () => globals.value.turkish_citizenship ?? {},
);
const media = computed(() => globals.value.media ?? {});

const summaryText = computed(() => {
    const raw =
        turkishCitizenship.value.summary ?? seo.value.turkish_citizenship ?? "";
    if (typeof raw !== "string") {
        return "";
    }
    return raw
        .replace(/<[^>]*>/g, " ")
        .replace(/\s+/g, " ")
        .trim();
});

const summaryWords = computed(() => {
    if (!summaryText.value) {
        return [];
    }
    return summaryText.value.split(/\s+/).filter(Boolean);
});

const bannerUrl = computed(() => {
    const url =
        turkishCitizenship.value.banner_url ||
        media.value.turkish_citizenship_banner ||
        "";
    if (typeof url !== "string" || url.trim() === "") {
        return "";
    }
    return url.trim();
});

const hasRealBanner = computed(() => {
    const url = bannerUrl.value;
    if (!url) {
        return false;
    }
    return !/\/default\.jpg(?:\?.*)?$/i.test(url);
});

const isVisible = computed(
    () => summaryText.value !== "" || hasRealBanner.value,
);

const backgroundStyle = computed(() => {
    if (!bannerUrl.value) {
        return {
            backgroundImage:
                "linear-gradient(135deg, var(--brand-navy) 0%, #2f3d5c 100%)",
        };
    }
    return { backgroundImage: `url("${bannerUrl.value}")` };
});

const titlePrimary = computed(() =>
    pickTranslation(
        "turkishCitizenship.overview_title_primary",
        "Turkish Citizenship",
    ),
);

const titleAccent = computed(() =>
    pickTranslation(
        "turkishCitizenship.overview_title_accent",
        "by Investment Programme",
    ),
);

const sectionTitle = computed(() =>
    `${titlePrimary.value} ${titleAccent.value}`.trim(),
);

const discoverLabel = computed(() =>
    pickTranslation("turkishCitizenship.discover_more", "Discover More"),
);

const citizenshipHref = computed(() => {
    try {
        if (
            typeof route === "function" &&
            route().has?.("turkish-citizenship")
        ) {
            return route("turkish-citizenship");
        }
    } catch {
        /* ignore */
    }
    return "/turkish-citizenship";
});

const sectionRef = ref(null);
const panelRef = ref(null);

const { gsap, context, prefersReducedMotion, refreshScrollTrigger } = useGsap();

let hasAnimated = false;

function setupPanelAnimation() {
    const section = sectionRef.value;
    const panel = panelRef.value;

    if (!section || !panel || hasAnimated || !isVisible.value) {
        return;
    }

    if (prefersReducedMotion()) {
        hasAnimated = true;
        return;
    }

    context(() => {
        const primary = panel.querySelector(".imas-tc-split-title__primary");
        const accent = panel.querySelector(".imas-tc-split-title__accent");
        const divider = panel.querySelector(".imas-tc-split-title__divider");
        const words = panel.querySelectorAll(".imas-tc-overview__word");
        const cursor = panel.querySelector(".imas-tc-overview__cursor");
        const cta = panel.querySelector(".imas-tc-overview__cta");

        gsap.set(panel, { opacity: 0, y: 36 });
        if (primary) {
            gsap.set(primary, { opacity: 0, y: 28 });
        }
        if (accent) {
            gsap.set(accent, { opacity: 0, y: 28 });
        }
        if (divider) {
            gsap.set(divider, {
                opacity: 0,
                scaleX: 0,
                transformOrigin: "center center",
            });
        }
        if (words.length) {
            gsap.set(words, { opacity: 0 });
        }
        if (cursor) {
            gsap.set(cursor, { opacity: 0 });
        }
        if (cta) {
            gsap.set(cta, { opacity: 0, y: 18 });
        }

        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: section,
                start: "top 88%",
                once: true,
                toggleActions: "play none none none",
            },
            defaults: { ease: "power2.out" },
        });

        tl.to(panel, { opacity: 1, y: 0, duration: 0.65 }, 0);

        if (primary) {
            tl.to(primary, { opacity: 1, y: 0, duration: 0.85 }, 0.08);
        }
        if (accent) {
            tl.to(accent, { opacity: 1, y: 0, duration: 0.85 }, 0.2);
        }

        if (divider) {
            tl.to(divider, { opacity: 1, scaleX: 1, duration: 0.5 }, 0.32);
        }

        const typingEnd =
            words.length > 0
                ? 0.1 + Math.max(0, words.length - 1) * 0.065 + 0.12
                : 0.45;

        if (words.length) {
            tl.to(
                words,
                {
                    opacity: 1,
                    duration: 0.12,
                    stagger: { each: 0.065, from: "start" },
                },
                0.1,
            );
        }

        if (cta) {
            tl.to(cta, { opacity: 1, y: 0, duration: 0.1 }, typingEnd + 0.1);
        }

        if (cursor) {
            tl.to(cursor, { opacity: 1, duration: 0.12 }, typingEnd);
            tl.to(
                cursor,
                {
                    opacity: 0,
                    duration: 0.35,
                    repeat: 2,
                    yoyo: true,
                    ease: "steps(1)",
                },
                typingEnd + 0.12,
            );
        }
    }, sectionRef);

    hasAnimated = true;
    refreshScrollTrigger();
}

function schedulePanelAnimation() {
    nextTick(() => {
        nextTick(setupPanelAnimation);
    });
}

watch(
    isVisible,
    (visible) => {
        if (visible) {
            schedulePanelAnimation();
        }
    },
    { immediate: true },
);
</script>

<style scoped>
.imas-tc-overview {
    position: relative;
    overflow: hidden;
    min-height: clamp(420px, 52vh, 560px);
    display: flex;
    align-items: center;
    padding: 3.5rem 0;
}

.imas-tc-overview__bg {
    position: absolute;
    inset: 0;
    z-index: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

.imas-tc-overview__overlay {
    position: absolute;
    inset: 0;
    z-index: 1;
    background: linear-gradient(
        180deg,
        rgba(26, 42, 74, 0.5) 0%,
        rgba(26, 42, 74, 0.35) 50%,
        rgba(26, 42, 74, 0.5) 100%
    );
}

.imas-tc-overview__inner {
    position: relative;
    z-index: 2;
    display: flex;
    justify-content: center;
    width: 100%;
    min-width: 0;
}
/* border-radius: 10px; */

.imas-tc-overview__panel {
    border-radius: 10px;
    width: 100%;
    min-width: 0;
    max-width: 640px;
    background: var(--surface);
    padding: 2.5rem 2.25rem 2.35rem;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border);
    text-align: center;
}

.imas-tc-overview__panel :deep(.imas-tc-split-title) {
    margin-bottom: 0;
}

.imas-tc-overview__panel :deep(.imas-tc-split-title__divider) {
    margin-bottom: 1.15rem;
}

.imas-tc-overview__text {
    margin: 0 auto 1.75rem;
    width: 100%;
    max-width: 560px;
    font-size: 16px;
    line-height: 1.75;
    color: var(--color-text-secondary);
    max-height: calc(1.75em * 4);
    overflow: hidden;
}

/* Constrains inline word spans so they wrap inside flex-centered panel */
.imas-tc-overview__text-flow {
    display: inline-block;
    width: 100%;
    max-width: 100%;
    text-align: center;
    overflow-wrap: break-word;
    word-break: break-word;
}

.imas-tc-overview__word {
    display: inline;
}

.imas-tc-overview__cursor {
    display: inline;
    margin-left: 1px;
    color: var(--brand-gold);
    font-weight: 600;
    vertical-align: baseline;
}

.imas-tc-overview__cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 2.25rem;
    border: none;
    border-radius: 8px;
    background: var(--brand-gold);
    color: var(--text);
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(217, 168, 0, 0.35);
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        background-color 0.25s ease;
}

.imas-tc-overview__cta span {
    display: inline-block;
    transition: transform 0.25s ease;
}

.imas-tc-overview__cta:hover,
.imas-tc-overview__cta:focus-visible {
    background: var(--brand-gold-hover);
    color: var(--text-on-gold);
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(217, 168, 0, 0.45);
}

.imas-tc-overview__cta:hover span,
.imas-tc-overview__cta:focus-visible span {
    transform: scale(1.02);
}

.imas-tc-overview__cta:active {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(217, 168, 0, 0.38);
}

@media (max-width: 768px) {
    .imas-tc-overview {
        min-height: auto;
        padding: 2.5rem 0;
    }

    .imas-tc-overview__bg {
        background-attachment: scroll;
    }

    .imas-tc-overview__panel {
        padding: 1.75rem 1.35rem 1.65rem;
    }

    .imas-tc-overview__cta {
        width: 100%;
        max-width: 320px;
    }
}
</style>
