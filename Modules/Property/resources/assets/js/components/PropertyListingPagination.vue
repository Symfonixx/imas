<template>
    <nav
        v-if="displayLinks.length > 0"
        class="imas-blog-v2-pagination"
        aria-label="Property listings pagination"
    >
        <template v-for="(link, idx) in displayLinks" :key="idx">
            <Link
                v-if="link.url"
                :href="link.url"
                class="imas-blog-v2-pagination__btn"
                :class="{ 'is-active': link.active }"
                :preserve-scroll="false"
                @click="onNavigate"
            >
                <span v-html="link.displayLabel" />
            </Link>
            <span
                v-else
                class="imas-blog-v2-pagination__btn is-disabled"
            >
                <span v-html="link.displayLabel" />
            </span>
        </template>
    </nav>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const COMPACT_MQ = "(max-width: 640px)";
const COMPACT_MAX_PAGE_BUTTONS = 4;

const props = defineProps({
    properties: { type: Object, required: true },
});

const emit = defineEmits(["navigate"]);

const page = usePage();
const isCompact = ref(false);
let compactMq = null;

function trans(key) {
    return page.props.translations[key] ?? key;
}

function stripHtml(value) {
    return String(value ?? "")
        .replace(/<[^>]*>/g, "")
        .trim();
}

function isNumericPageLabel(label) {
    return /^\d+$/.test(stripHtml(label));
}

function withDisplayLabels(raw) {
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
}

function compactPageLinks(links, maxPages = COMPACT_MAX_PAGE_BUTTONS) {
    const n = links.length;
    if (n < 2) {
        return links;
    }

    const prev = links[0];
    const next = links[n - 1];
    const pageLinks = links.slice(1, -1).filter((link) => isNumericPageLabel(link.label));

    if (pageLinks.length <= maxPages) {
        return links;
    }

    const activeLink = pageLinks.find((link) => link.active);
    const currentPage = activeLink
        ? parseInt(stripHtml(activeLink.label), 10)
        : 1;
    const totalPages = parseInt(
        stripHtml(pageLinks[pageLinks.length - 1].label),
        10,
    );

    let start = Math.max(1, currentPage - Math.floor(maxPages / 2));
    let end = start + maxPages - 1;
    if (end > totalPages) {
        end = totalPages;
        start = Math.max(1, end - maxPages + 1);
    }

    const visiblePages = pageLinks.filter((link) => {
        const pageNum = parseInt(stripHtml(link.label), 10);
        return pageNum >= start && pageNum <= end;
    });

    return [prev, ...visiblePages, next];
}

function updateCompact() {
    isCompact.value =
        typeof window !== "undefined" &&
        window.matchMedia(COMPACT_MQ).matches;
}

onMounted(() => {
    updateCompact();
    if (typeof window === "undefined" || !window.matchMedia) {
        return;
    }
    compactMq = window.matchMedia(COMPACT_MQ);
    compactMq.addEventListener("change", updateCompact);
});

onBeforeUnmount(() => {
    compactMq?.removeEventListener("change", updateCompact);
});

const displayLinks = computed(() => {
    const raw = props.properties?.links ?? [];
    const labeled = withDisplayLabels(raw);
    return isCompact.value ? compactPageLinks(labeled) : labeled;
});

function onNavigate(event) {
    const btn = event.currentTarget;
    if (!btn || typeof btn.getBoundingClientRect !== "function") {
        emit("navigate");
        return;
    }

    const rect = btn.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const ripple = document.createElement("span");
    ripple.className = "imas-blog-v2-pagination__ripple";
    ripple.style.width = `${size}px`;
    ripple.style.height = `${size}px`;
    ripple.style.left = `${event.clientX - rect.left - size / 2}px`;
    ripple.style.top = `${event.clientY - rect.top - size / 2}px`;
    btn.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);

    emit("navigate");
}
</script>
