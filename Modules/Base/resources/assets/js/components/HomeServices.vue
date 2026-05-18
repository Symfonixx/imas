<template>
    <div class="container">
        <section
            v-if="services.length"
            ref="sectionRef"
            class="how-it-works bg-white rec-pro"
        >
            <div class="container-fluid">
                <div class="sec-title">
                    <h2 v-html="sectionTitleHtml"></h2>
                    <p>{{ trans("services.description") }}</p>
                </div>
                <div class="row service-1">
                    <article
                        v-for="(service, index) in services"
                        :key="service.id"
                        class="col-lg-4 col-md-6 col-xs-12 serv"
                        :class="{ 'mb-0 pt': index === services.length - 1 }"
                    >
                        <div class="serv-flex">
                            <div class="art-1 img-13 corporate-service-art">
                                <img
                                    v-if="service.image"
                                    class="corporate-service-img"
                                    :src="service.image"
                                    :alt="service.title"
                                    loading="lazy"
                                />
                                <h3>{{ service.title }}</h3>
                            </div>
                            <div class="service-text-p">
                                <p class="text-center">
                                    {{ service.description }}
                                </p>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
// Do not use data-aos here: app.blade.php loads AOS CSS but not aos.js, so [data-aos] stays hidden.
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useScrollReveal } from "@/composables/useScrollReveal";

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}

const props = defineProps({
    services: { type: Array, default: () => [] },
});

const sectionRef = ref(null);

useScrollReveal(sectionRef, {
    preset: "home",
    variant: "cards",
    when: computed(() => props.services.length > 0),
});

const sectionTitleHtml = computed(() => {
    const raw = trans("services.title");
    if (raw.includes("<")) {
        return raw;
    }
    const parts = raw.split(/\s+/);
    if (parts.length < 2) {
        return raw;
    }
    const first = parts[0];
    const rest = parts.slice(1).join(" ");
    return `<span>${first} </span>${rest}`;
});
</script>

<style scoped>
.corporate-service-art {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.corporate-service-img {
    width: 100%;
    max-height: 180px;
    object-fit: contain;
    border-radius: 4px;
    margin-bottom: 0.75rem;
}
</style>
