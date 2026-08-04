<template>
    <div class="bg-all">
        <a
            :href="href"
            class="btn btn-outline-light imas-featured-view-more"
            >{{ text }}</a
        >
    </div>
</template>

<script setup>
/**
 * Theme “view more” CTA (Find Houses featured strip).
 * @prop {string} href — Target URL (pass `route('name')` from parent or any path).
 * @prop {string} text — Button label.
 */
defineProps({
    href: {
        type: String,
        required: true,
    },
    text: {
        type: String,
        required: true,
    },
});
</script>

<!-- Unscoped: document `dir` lives on <html>, outside the component subtree. -->
<style>
html[dir="rtl"] .imas-featured-view-more.btn.btn-outline-light:after {
    content: "\f0a8";
    left: auto;
    right: 0.5rem;
}

html[dir="rtl"] .imas-featured-view-more.btn.btn-outline-light:hover:after {
    left: auto;
    right: 1rem;
    color: #fff;
    -webkit-transform: rotateZ(360deg);
    transform: rotateZ(360deg);
}
</style>
