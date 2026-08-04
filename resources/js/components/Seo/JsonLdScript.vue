<script setup>
defineProps({
    headKey: { type: String, required: true },
    content: { type: String, default: "" },
});
</script>

<template>
    <component
        :is="'script'"
        v-if="content"
        :head-key="headKey"
        type="application/ld+json"
    >{{ content }}</component>
</template>
