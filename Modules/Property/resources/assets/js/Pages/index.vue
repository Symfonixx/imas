<template>
    <Head :title="`${title} | ${appName}`" />

    <AppLayout>
        <section
            class="properties-right featured portfolio blog pt-5 inner-pages listing homepage-4 agents hd-white"
        >
            <div class="container">
                <section class="headings-2 pt-0 pb-55">
                    <div class="pro-wrapper">
                        <div class="detail-wrapper-body">
                            <div class="listing-title-bar">
                                <div class="text-heading text-left">
                                    <p class="pb-2">
                                        <Link :href="route('home')">{{
                                            trans("listing_page.breadcrumb_home")
                                        }}</Link>
                                        &nbsp;/&nbsp;
                                        <span>{{
                                            trans("listing_page.breadcrumb_listings")
                                        }}</span>
                                    </p>
                                </div>
                                <h3>{{ title }}</h3>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="row">
                    <div class="col-lg-8 col-md-12 blog-pots">
                        <PropertyListingToolbar
                            :properties="properties"
                            :filters="filters"
                            :sort="sort"
                        />
                        <PropertyGridSection :properties="properties" />
                    </div>
                    <aside class="col-lg-4 col-md-12 car">
                        <PropertyFilterSidebar
                            :filters="filters"
                            :sort="sort"
                            :cities="cities"
                            :property-types="propertyTypes"
                        />
                        <FeaturedPropertiesSidebar
                            :featured-properties="featuredProperties"
                        />
                        <RecentPropertiesSidebar
                            :recent-properties="recentProperties"
                        />
                    </aside>
                </div>
                <PropertyListingPagination :properties="properties" />
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import PropertyListingToolbar from "../components/PropertyListingToolbar.vue";
import PropertyGridSection from "../components/PropertyGridSection.vue";
import PropertyListingPagination from "../components/PropertyListingPagination.vue";
import PropertyFilterSidebar from "../components/PropertyFilterSidebar.vue";
import FeaturedPropertiesSidebar from "../components/FeaturedPropertiesSidebar.vue";
import RecentPropertiesSidebar from "../components/RecentPropertiesSidebar.vue";

defineProps({
    title: { type: String, required: true },
    properties: { type: Object, required: true },
    filters: { type: Object, required: true },
    sort: { type: String, required: true },
    propertyTypes: { type: Array, default: () => [] },
    cities: { type: Array, default: () => [] },
    recentProperties: { type: Array, default: () => [] },
    featuredProperties: { type: Array, default: () => [] },
});

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}

const appName = computed(() => page.props.appName);
</script>


<style scoped lang="scss">

section{
    background-color: #f6f9fe !important;
}
</style>