<template>
    <section
        v-if="isVisible"
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
            <div class="imas-tc-overview__panel">
                <h2 class="imas-tc-overview__title">
                    <span class="imas-tc-overview__title-primary">{{
                        titlePrimary
                    }}</span>
                    <span class="imas-tc-overview__title-accent">{{
                        titleAccent
                    }}</span>
                </h2>
                <hr class="imas-tc-overview__divider" />
                <p
                    v-if="summaryText"
                    class="imas-tc-overview__text"
                    :title="summaryText"
                >
                    {{ summaryText }}
                </p>
                <a :href="citizenshipHref" class="imas-tc-overview__cta">
                    <span>{{ discoverLabel }}</span>
                </a>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

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
        turkishCitizenship.value.summary ??
        seo.value.turkish_citizenship ??
        "";
    if (typeof raw !== "string") {
        return "";
    }
    return raw.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
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

const sectionTitle = computed(
    () => `${titlePrimary.value} ${titleAccent.value}`.trim(),
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
}
/* border-radius: 10px; */

.imas-tc-overview__panel {
    border-radius: 10px;
    width: 100%;
    max-width: 640px;
    background: #fff;
    padding: 2.5rem 2.25rem 2.35rem;
    box-shadow: 0 16px 48px rgba(26, 42, 74, 0.22);
    text-align: center;
}

.imas-tc-overview__title {
    margin: 0;
    font-size: clamp(1.35rem, 2.2vw, 1.75rem);
    font-weight: 700;
    line-height: 1.4;
}

.imas-tc-overview__title-primary {
    display: block;
    color: var(--brand-navy);
}

.imas-tc-overview__title-accent {
    display: block;
    color: var(--brand-gold);
}

.imas-tc-overview__divider {
    margin: 1rem auto 1.15rem;
    border: 0;
    height: 3px;
    width: 72px;
    background: var(--brand-gold);
}

.imas-tc-overview__text {
    margin: 0 auto 1.75rem;
    max-width: 560px;
    font-size: 0.98rem;
    line-height: 1.75;
    color: var(--color-text-secondary);
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 4;
    line-clamp: 4;
    overflow: hidden;
    text-overflow: ellipsis;
}

.imas-tc-overview__cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 2.25rem;
    border: none;
    border-radius: 8px;
    background: var(--brand-gold);
    color: #fff;
    font-size: 0.95rem;
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
    color: #fff;
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
