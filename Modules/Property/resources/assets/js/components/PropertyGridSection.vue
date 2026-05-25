<template>
    <div class="row">
        <PropertyCard
            v-for="item in items"
            :key="item.id"
            :property="item"
            column-class="col-12 col-md-6 col-lg-6"
        />
    </div>
</template>

<script setup>
import { computed } from "vue";
import PropertyCard from "./PropertyCard.vue";

const props = defineProps({
    properties: { type: Object, required: true },
});

const items = computed(() => props.properties?.data ?? []);
</script>
