<template>
    <nav v-if="displayLinks.length > 0" aria-label="Pagination" class="agents pt-55">
        <ul class="pagination">
            <li
                v-for="(link, idx) in displayLinks"
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
                    <span v-html="link.displayLabel" />
                </Link>
                <span v-else class="page-link">
                    <span v-html="link.displayLabel" />
                </span>
            </li>
        </ul>
    </nav>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const props = defineProps({
    properties: { type: Object, required: true },
});

const page = usePage();

function trans(key) {
    return page.props.translations[key] ?? key;
}

/**
 * Laravel paginator `links`: first item is "Previous", last is "Next"
 * (see LengthAwarePaginator JSON). Replace with `global.*` from Base lang JSON.
 */
const displayLinks = computed(() => {
    const raw = props.properties?.links ?? [];
    const n = raw.length;
    if (n < 2) {
        return raw.map((link) => ({ ...link, displayLabel: link.label }));
    }
    return raw.map((link, idx) => {
        let displayLabel = link.label;
        if (idx === 0) {
            displayLabel = trans("global.previous");
        } else if (idx === n - 1) {
            displayLabel = trans("global.next");
        }
        return { ...link, displayLabel };
    });
});
</script>
