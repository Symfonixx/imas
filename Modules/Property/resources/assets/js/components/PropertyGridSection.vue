<template>
    <div class="row">
        <PropertyCard
            v-for="item in items"
            :key="item.id"
            :property="item"
            column-class="col-lg-6 col-md-6 col-xs-12"
        />
    </div>
</template>

<script setup>
import { computed } from "vue";
import PropertyCard from "@/components/PropertyCard.vue";

const props = defineProps({
    properties: { type: Object, required: true },
});

const items = computed(() => props.properties?.data ?? []);
</script>
