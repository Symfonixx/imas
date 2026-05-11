<template>
    <section v-if="properties.length > 0" class="featured portfolio bg-white-2">
        <div class="container">
            <div class="sec-title">
                <h2>{{ title }}</h2>
                <p>{{ subtitle }}</p>
            </div>
            <div class="row portfolio-items">
                <PropertyCard
                    v-for="property in properties"
                    :key="property.id"
                    :property="property"
                />
            </div>
            <div class="bg-all">
                <a
                    href="properties-full-grid-1.html"
                    class="btn btn-outline-light imas-featured-view-more"
                    >{{ trans("global.view_more") }}</a
                >
            </div>
        </div>
    </section>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}

defineProps({
    properties: {
        type: Array,
        default: () => [],
    },
    title: {
        type: String,
        default: "Featured properties",
    },
    subtitle: {
        type: String,
        default: "We provide full service at every step.",
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
