<template>
    <section
        v-if="isVisible"
        ref="sectionRef"
        class="imas-about-overview"
        :aria-label="sectionTitle"
    >
        <div class="container imas-about-overview__inner">
            <div class="imas-about-overview__panel">
                <h2 class="imas-about-overview__title">
                    <span class="imas-about-overview__title-primary">{{
                        titlePrimary
                    }}</span>
                    <span
                        v-if="titleAccent"
                        class="imas-about-overview__title-accent"
                        >{{ titleAccent }}</span
                    >
                </h2>
                <hr class="imas-about-overview__divider" />
                <p
                    class="imas-about-overview__text"
                    :title="summaryText"
                >
                    {{ summaryText }}
                </p>
                <a :href="aboutHref" class="imas-about-overview__cta">
                    <span>{{ exploreLabel }}</span>
                </a>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { cmsPageUrl } from "@/utils/cmsPageUrl.js";
import { useScrollReveal } from "@/composables/useScrollReveal";

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
const about = computed(() => globals.value.about ?? {});

const summaryText = computed(() => {
    const raw = about.value.summary ?? seo.value.about_us ?? "";
    if (typeof raw !== "string") {
        return "";
    }
    return raw.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
});

const isVisible = computed(() => summaryText.value !== "");

const titlePrimary = computed(() =>
    pickTranslation("aboutUs.overview_title_primary", "About"),
);

const titleAccent = computed(() => {
    const key = "aboutUs.overview_title_accent";
    const value = trans(key);
    if (value !== key) {
        return typeof value === "string" ? value.trim() : "";
    }
    return "Us";
});

const sectionTitle = computed(
    () => `${titlePrimary.value} ${titleAccent.value}`.trim(),
);

const exploreLabel = computed(() =>
    pickTranslation("aboutUs.explore_more", "Explore More"),
);

const aboutHref = computed(() => {
    try {
        if (typeof route === "function" && route().has?.("about-us")) {
            return route("about-us");
        }
    } catch {
        /* ignore */
    }
    return cmsPageUrl("about-us");
});

const sectionRef = ref(null);

useScrollReveal(sectionRef, {
    preset: "home",
    variant: "panel",
    when: isVisible,
});
</script>

<style scoped>
.imas-about-overview {
    padding: 3.5rem 0;
    background: var(--color-surface-muted, #f4f6f9);
}

.imas-about-overview__inner {
    display: flex;
    justify-content: center;
}

.imas-about-overview__panel {
    width: 100%;
    max-width: 720px;
    padding: 0 1rem;
    text-align: center;
}

.imas-about-overview__title {
    margin: 0;
    font-size: clamp(1.35rem, 2.2vw, 1.75rem);
    font-weight: 700;
    line-height: 1.4;
}

.imas-about-overview__title-primary {
    color: var(--text);
}

.imas-about-overview__title-accent {
    color: var(--brand-gold);
}

.imas-about-overview__title-accent::before {
    content: " ";
}

.imas-about-overview__divider {
    margin: 1rem auto 1.15rem;
    border: 0;
    height: 3px;
    width: 72px;
    background: var(--brand-gold);
}

.imas-about-overview__text {
    margin: 0 auto 1.75rem;
    max-width: 640px;
    font-size: 16px;
    line-height: 1.75;
    color: var(--color-text-secondary);
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 4;
    line-clamp: 4;
    overflow: hidden;
    text-overflow: ellipsis;
}

.imas-about-overview__cta {
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

.imas-about-overview__cta span {
    display: inline-block;
    transition: transform 0.25s ease;
}

.imas-about-overview__cta:hover,
.imas-about-overview__cta:focus-visible {
    background: var(--brand-gold-hover);
    color: var(--text-on-gold);
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(217, 168, 0, 0.45);
}

.imas-about-overview__cta:hover span,
.imas-about-overview__cta:focus-visible span {
    transform: scale(1.02);
}

.imas-about-overview__cta:active {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(217, 168, 0, 0.38);
}

@media (max-width: 768px) {
    .imas-about-overview {
        padding: 2.5rem 0;
    }

    .imas-about-overview__cta {
        width: 100%;
        max-width: 320px;
    }
}
</style>
