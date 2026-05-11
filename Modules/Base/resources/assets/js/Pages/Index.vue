<template>
    <Head :title="`Home | ${appName}`" />

    <AppLayout>
        <HomeHero
            :welcome-title="welcomeTitle"
            :welcome-subtitle="welcomeSubtitle"
            :slides="slides"
            :property-types="propertyTypes"
            :cities="cities"
        />

        <FeaturedPropertiesSection
            :properties="featuredProperties"
            :title="trans('properties.featured_properties')"
            :subtitle="
                trans('properties.we_provide_full_service_at_every_step')
            "
        />

        
        <HomeServices :services="corporateServices" />
        <PopularPropertiesSection
            :properties="recommendedProperties"
                :title="trans('properties.title')"
            :subtitle="
                trans('properties.we_provide_full_service_at_every_step')
            "
        />
        <HomeTestimonials :testimonials="testimonials" />
        <HomeArticlesSection :articles="articles" />
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import FeaturedPropertiesSection from "../components/FeaturedPropertiesSection.vue";
import PopularPropertiesSection from "../components/PopularPropertiesSection.vue";
import HomeServices from "../components/HomeServices.vue";
import HomeTestimonials from "../components/HomeTestimonials.vue";
import HomeArticlesSection from "../components/HomeArticlesSection.vue";
import HomeHero from "../components/HomeHero.vue";
const page = usePage();
function trans(key) {
    return page.props.translations[key] || key;
}
defineProps({
    welcomeTitle: { type: String, required: true },
    welcomeSubtitle: { type: String, required: true },
    slides: { type: Array, default: () => [] },
    propertyTypes: { type: Array, default: () => [] },
    cities: { type: Array, default: () => [] },
    featuredProperties: { type: Array, default: () => [] },
    recommendedProperties: { type: Array, default: () => [] },
    corporateServices: { type: Array, default: () => [] },
    testimonials: { type: Array, default: () => [] },
    articles: { type: Array, default: () => [] },
});

const appName = computed(() => page.props.appName);
</script>
