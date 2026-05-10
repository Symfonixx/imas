<template>
    <Head :title="`Home | ${appName}`"/>

    <AppLayout>
        <HomeHero
            :welcome-title="welcomeTitle"
            :welcome-subtitle="welcomeSubtitle"
            :slides="slides"
            :property-types="propertyTypes"
            :cities="cities"
      
        />

        <FeaturedPropertiesSection :properties="featuredProperties"/>
    </AppLayout>
</template>

<script setup>
import {computed} from 'vue';
import {Head, usePage} from '@inertiajs/vue3';
import AppLayout from '@/Layouts/App.vue';
import FeaturedPropertiesSection from '../components/FeaturedPropertiesSection.vue';
import HomeHero from '../components/HomeHero.vue';

defineProps({
    welcomeTitle: {type: String, required: true},
    welcomeSubtitle: {type: String, required: true},
    slides: {type: Array, default: () => []},
    propertyTypes: {type: Array, default: () => []},
    cities: {type: Array, default: () => []},
    featuredProperties: {type: Array, default: () => []},
    recommendedProperties: {type: Array, default: () => []},
});

const page = usePage();

const appName = computed(() => page.props.appName);
</script>
