<template>
    <section
        v-if="properties.length > 0"
        class="featured portfolio bg-white-2"
    >
        <div class="container">
            <div class="sec-title">
                <h2><span>Featured </span>Properties</h2>
                <p>{{ subtitle }}</p>
            </div>
            <div class="row portfolio-items">
                <PropertyCard
                    v-for="property in properties"
                    :key="property.id"
                    :property="property"
                />
            </div>
        </div>
    </section>
</template>

<script setup>
defineProps({
    properties: {
        type: Array,
        default: () => [],
    },
    subtitle: {
        type: String,
        default: 'We provide full service at every step.',
    },
});
</script>
