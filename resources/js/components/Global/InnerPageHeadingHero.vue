<template>
    <section class="headings">
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
import { Link } from "@inertiajs/vue3";

/**
 * @typedef {{ title: string, href?: string | null }} InnerPageHeadingCrumb
 */

defineProps({
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
});
</script>

<style scoped lang="scss">

// .inner-pages .headings {
//     background-image: none !important;
//     background-color: var(--brand-navy-light) !important;
// }
</style>
