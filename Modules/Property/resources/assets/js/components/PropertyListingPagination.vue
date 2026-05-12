<template>
    <nav v-if="links.length > 0" aria-label="Pagination" class="agents pt-55">
        <ul class="pagination">
            <li
                v-for="(link, idx) in links"
                :key="idx"
                class="page-item"
                :class="{ active: link.active, disabled: !link.url }"
            >
                <Link
                    v-if="link.url"
                    class="page-link"
                    :href="link.url"
                    :preserve-scroll="true"
                >
                    <span v-html="link.label" />
                </Link>
                <span v-else class="page-link">
                    <span v-html="link.label" />
                </span>
            </li>
        </ul>
    </nav>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    properties: { type: Object, required: true },
});

const links = computed(() => props.properties?.links ?? []);
</script>
