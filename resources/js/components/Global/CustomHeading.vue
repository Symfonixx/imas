<template>
    <div v-if="hasContent" class="imas-custom-heading call-info">
        <h3
            v-if="title.trim()"
            class="text-start imas-custom-heading__title"
            :style="textStyle"
        >
            {{ title }}
        </h3>
        
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    title: {
        type: String,
        default: "",
    },
  
    textColor: {
        type: String,
        default: "#333333",
    },
});

const hasContent = computed(
    () => props.title.trim() !== "" || props.subtitle.trim() !== "",
);

const textStyle = computed(() => ({
    color: props.textColor?.trim() || "#333333",
}));
</script>

<style scoped lang="scss">
.inner-pages .call-info h3::after{
    width: 150px !important;
}

@media (max-width: 768px) {
    .inner-pages .call-info h3::after{
        width: 100px !important;
    }
}   
</style>
