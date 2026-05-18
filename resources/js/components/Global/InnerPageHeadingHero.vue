<template>
    <section
        class="headings imas-inner-page-heading-hero"
        :style="sectionStyle"
    >
        <div class="text-heading text-center">
            <div class="container">
                <h1>{{ pageTitle }}</h1>
                <h2 v-if="items.length" class="imas-inner-page-heading-hero__crumbs">
                    <template v-for="(item, idx) in items" :key="idx">
                        <Link v-if="item.href" :href="item.href">{{ item.title }}</Link>
                        <span v-else>{{ item.title }}</span>
                        <template v-if="idx < items.length - 1">&nbsp;/&nbsp;</template>
                    </template>
                </h2>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

/**
 * @typedef {{ title: string, href?: string | null }} InnerPageHeadingCrumb
 */

const props = defineProps({
    /** Main heading (Find Houses theme uses this as the large title). */
    pageTitle: {
        type: String,
        required: true,
    },
    /**
     * Breadcrumb-style segments under the title. Pass `href` for links; omit or null
     * for the current segment (plain text).
     * @type {InnerPageHeadingCrumb[]}
     */
    items: {
        type: Array,
        default: () => [],
    },
    /** Optional hero background image URL (theme default used when empty). */
    bannerImageUrl: {
        type: String,
        default: "",
    },
});

const sectionStyle = computed(() => {
    const url =
        typeof props.bannerImageUrl === "string"
            ? props.bannerImageUrl.trim()
            : "";
    if (!url || /\/default\.jpg(?:\?.*)?$/i.test(url)) {
        return undefined;
    }

    return {
        backgroundImage: `linear-gradient(rgba(18, 27, 34, 0.6), rgba(18, 27, 34, 0.6)), url("${url}")`,
        backgroundRepeat: "no-repeat",
        backgroundPosition: "center center",
        backgroundSize: "cover",
        backgroundAttachment: "fixed",
    };
});
</script>

<style scoped lang="scss">
section.imas-inner-page-heading-hero.headings {
    height: 25vh;
    background-attachment: fixed;

    @media (min-width: 992px) {
        height: 45vh;
    }
}
</style>
