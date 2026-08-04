<template>
  <slot v-if="mounted" />
  <slot v-else name="placeholder" />
</template>

<script setup>
import { ref, onMounted } from 'vue';

const mounted = ref(false);

onMounted(() => {
  mounted.value = true;
});
</script>
