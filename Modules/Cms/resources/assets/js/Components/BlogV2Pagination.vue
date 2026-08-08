<template>
    <nav
        v-if="links.length > 0"
        class="imas-blog-v2-pagination"
        aria-label="Blog pagination"
    >
        <template v-for="(link, idx) in links" :key="idx">
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
import { Link } from "@inertiajs/vue3";

defineProps({
    links: { type: Array, default: () => [] },
});

const emit = defineEmits(["navigate"]);

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
