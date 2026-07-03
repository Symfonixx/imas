<template>
    <header
        class="imas-inner-page-heading-hero"
        :class="{ 'imas-inner-page-heading-hero--video': hasVideoBackground }"
    >
        <div
            class="imas-inner-page-heading-hero__bg"
            ref="heroBgRef"
            :class="{
                'imas-inner-page-heading-hero__bg--video': hasVideoBackground,
            }"
            :style="bgStyle"
        >
            <iframe
                v-if="heroVideoSrc"
                ref="heroVideoIframeRef"
                class="imas-inner-page-heading-hero__video"
                :src="heroVideoSrc"
                title=""
                tabindex="-1"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                aria-hidden="true"
            />
        </div>
        <div class="imas-inner-page-heading-hero__inner">
            <h1
                class="imas-inner-page-heading-hero__title"
                :class="{
                    'imas-inner-page-heading-hero__title--connected':
                        titleUsesConnectedTitle,
                }"
                :aria-label="pageTitle"
            >
                <span
                    v-if="titleUsesConnectedTitle"
                    class="imas-inner-page-heading-hero__title-text"
                    >{{ displayTitle }}</span
                >
                <template v-else>
                    <span
                        v-for="(ch, i) in titleLetters"
                        :key="i"
                        class="imas-inner-page-heading-hero__letter"
                        :style="{ animationDelay: `${120 + i * 90}ms` }"
                        >{{ ch === " " ? "\u00A0" : ch }}</span
                    >
                </template>
            </h1>
            <nav
                v-if="items.length"
                class="imas-inner-page-heading-hero__crumbs"
                aria-label="Breadcrumb"
            >
                <template v-for="(item, idx) in items" :key="idx">
                    <Link
                        v-if="item.href"
                        :href="item.href"
                        class="imas-inner-page-heading-hero__crumb-link"
                        >{{ item.title }}</Link
                    >
                    <span
                        v-else
                        class="imas-inner-page-heading-hero__crumb-active"
                        >{{ item.title }}</span
                    >
                    <span
                        v-if="idx < items.length - 1"
                        class="imas-inner-page-heading-hero__crumb-sep"
                        aria-hidden="true"
                        >/</span
                    >
                </template>
            </nav>
        </div>
    </header>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { useYoutubeHeroPlayer } from "@/composables/useYoutubeHeroPlayer.js";
import { resolveYoutubeHeroBackgroundSrc } from "@/utils/videoEmbed.js";

/** Arabic and related scripts need cursive joining — do not split per character. */
const CONNECTED_SCRIPT_RE =
    /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/u;

/**
 * @typedef {{ title: string, href?: string | null }} InnerPageHeadingCrumb
 */

const props = defineProps({
    pageTitle: { type: String, required: true },
    /** @type {InnerPageHeadingCrumb[]} */
    items: { type: Array, default: () => [] },
    bannerImageUrl: { type: String, default: "" },
    /** YouTube iframe HTML, watch URL, or embed URL for a muted autoplay hero background. */
    bannerVideoEmbed: { type: String, default: "" },
    /** Title casing for Latin letter-by-letter / compact hero (connected scripts stay unchanged). */
    capitalizeTitle: { type: Boolean, default: true },
});

const page = usePage();
const heroBgRef = ref(null);
const heroVideoIframeRef = ref(null);
const prefersCompactHeroTitle = ref(false);

let compactTitleMq = null;

function capitalizeHeroTitle(text) {
    const locale = String(page.props.locale ?? "en");
    return String(text || "")
        .toLocaleLowerCase(locale)
        .replace(/(\p{L})(\p{L}*)/gu, (_, first, rest) =>
            first.toLocaleUpperCase(locale) + rest,
        );
}

function usesConnectedScript(text) {
    return CONNECTED_SCRIPT_RE.test(String(text || ""));
}

const titleUsesConnectedScript = computed(() => {
    if (usesConnectedScript(props.pageTitle)) {
        return true;
    }
    const locale = String(page.props.locale ?? "");
    const dir = String(page.props.text_direction ?? "");
    return locale === "ar" || dir === "rtl";
});

/** Narrow viewports: avoid per-letter flex wrap (e.g. "PROPERTY LIS" / "TINGS"). */
const titleUsesConnectedTitle = computed(
    () =>
        titleUsesConnectedScript.value || prefersCompactHeroTitle.value,
);

const displayTitle = computed(() => {
    const raw = String(props.pageTitle || "");
    if (titleUsesConnectedTitle.value) {
        return titleUsesConnectedScript.value
            ? raw
            : props.capitalizeTitle
              ? capitalizeHeroTitle(raw)
              : raw;
    }
    return props.capitalizeTitle ? capitalizeHeroTitle(raw) : raw;
});

const titleLetters = computed(() => displayTitle.value.split(""));

const heroVideoSrc = computed(() =>
    resolveYoutubeHeroBackgroundSrc(props.bannerVideoEmbed),
);

const hasVideoBackground = computed(() => Boolean(heroVideoSrc.value));

useYoutubeHeroPlayer(heroVideoIframeRef, hasVideoBackground);

const bgStyle = computed(() => {
    if (hasVideoBackground.value) {
        return undefined;
    }
    const url =
        typeof props.bannerImageUrl === "string"
            ? props.bannerImageUrl.trim()
            : "";
    if (!url || /\/default\.jpg(?:\?.*)?$/i.test(url)) {
        return undefined;
    }
    return {
        backgroundImage: `linear-gradient(
            color-mix(in srgb, var(--brand-navy-hover) 72%, transparent),
            color-mix(in srgb, var(--bg) 88%, transparent)
        ), url("${url}")`,
        backgroundSize: "cover",
        backgroundPosition: "center",
    };
});

function syncCompactHeroTitle() {
    if (typeof window === "undefined" || !window.matchMedia) {
        prefersCompactHeroTitle.value = false;
        return;
    }
    prefersCompactHeroTitle.value = window.matchMedia(
        "(max-width: 640px)",
    ).matches;
}

function onScroll() {
    if (prefersCompactHeroTitle.value || hasVideoBackground.value) {
        if (heroBgRef.value) {
            heroBgRef.value.style.transform = "";
        }
        return;
    }
    const y = window.scrollY;
    if (heroBgRef.value && y < 700) {
        heroBgRef.value.style.transform = `translateY(${y * 0.35}px)`;
    }
}

function onCompactTitleMqChange() {
    syncCompactHeroTitle();
    onScroll();
}

onMounted(() => {
    syncCompactHeroTitle();
    if (typeof window !== "undefined" && window.matchMedia) {
        compactTitleMq = window.matchMedia("(max-width: 640px)");
        compactTitleMq.addEventListener("change", onCompactTitleMqChange);
    }
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
});

onBeforeUnmount(() => {
    compactTitleMq?.removeEventListener("change", onCompactTitleMqChange);
    window.removeEventListener("scroll", onScroll);
});
</script>
