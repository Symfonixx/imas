<template>
    <section
        v-if="properties.length > 0"
        ref="sectionRef"
        class="featured portfolio bg-white-2"
    >
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
            <ReadMore
                :href="viewMoreHref"
                :text="trans('global.view_more')"
            />
        </div>
    </section>
</template>

<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import ReadMore from "@/components/buttons/ReadMore.vue";
import { useScrollReveal } from "@/composables/useScrollReveal";

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}

const viewMoreHref = computed(() => {
    try {
        if (typeof route === "function" && route().has?.("property.index")) {
            return route("property.index");
        }
    } catch {
        /* ignore */
    }

    return "/property";
});

const props = defineProps({
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

const sectionRef = ref(null);

useScrollReveal(sectionRef, {
    preset: "home",
    variant: "cards",
    when: computed(() => props.properties.length > 0),
});
</script>
