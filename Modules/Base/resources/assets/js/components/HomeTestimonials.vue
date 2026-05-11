<template>
    <section
        v-if="testimonials.length"
        class="home-testimonials testimonials bg-white-2 rec-pro"
    >
        <div class="container-fluid">
            <div class="sec-title">
                <h2>
                    <span> {{ trans("testimonials.title") }}</span>
                   
                </h2>
                <p>{{ trans("testimonials.description") }}</p>
            </div>
            <div
                ref="testimonialsCarousel"
                class="owl-carousel job_clientSlide"
            >
                <div
                    v-for="item in testimonials"
                    :key="item.id"
                    class="singleJobClinet"
                >
                    <p  class="quote" v-html="item.quote"></p>
                    <div class="detailJC">
                        <span
                            ><img :src="item.avatar" :alt="item.name"
                        /></span>
                        <h5>
                            <a
                                v-if="item.link"
                                :href="item.link"
                                rel="noopener noreferrer"
                                target="_blank"
                                >{{ item.name }}</a
                            >
                            <template v-else>{{ item.name }}</template>
                        </h5>
                        <p>{{ subtitleLine(item) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import {
    onBeforeUnmount,
    onMounted,
    ref,
    nextTick,
    watch,
} from "vue";
import { usePage } from "@inertiajs/vue3";
const page = usePage();
function trans(key) {
    return page.props.translations[key] || key;
}

const props = defineProps({
    testimonials: {
        type: Array,
        default: () => [],
    },
});

const testimonialsCarousel = ref(null);

function subtitleLine(item) {
    const position = String(item.position ?? "").trim();
    const client = String(item.client ?? "").trim();
    return position || client || "";
}

function jquery() {
    if (typeof window === "undefined") return null;
    return window.jQuery ?? window.$ ?? null;
}

function initOwl() {
    const $ = jquery();
    const el = testimonialsCarousel.value;
    if (!$ || !el || !props.testimonials.length) return;
    const $el = $(el);
    if ($el.data("owl.carousel")) return;
    const rtl =
        String(page.props.text_direction || "").toLowerCase() === "rtl";
    $el.owlCarousel({
        rtl,
        items: 2,
        loop: props.testimonials.length > 1,
        margin: 30,
        autoplay: false,
        nav: true,
        smartSpeed: 1000,
        slideSpeed: 1000,
        navText: [
            "<i class='fa fa-chevron-left'></i>",
            "<i class='fa fa-chevron-right'></i>",
        ],
        dots: false,
        responsive: {
            0: { items: 1 },
            991: { items: 3 },
        },
    });
}

function destroyOwl() {
    const $ = jquery();
    const el = testimonialsCarousel.value;
    if (!$ || !el) return;
    const $el = $(el);
    if ($el.data("owl.carousel")) {
        $el.owlCarousel("destroy");
    }
}

async function refreshOwl() {
    destroyOwl();
    await nextTick();
    initOwl();
}

onMounted(() => {
    nextTick(() => {
        nextTick(() => initOwl());
    });
});

watch(
    () => props.testimonials.map((t) => t.id).join(","),
    () => {
        refreshOwl();
    },
);

onBeforeUnmount(() => {
    destroyOwl();
});
</script>

<style scoped lang="scss">
.quote{
    // font-size: 1.2rem;
    // font-weight: 400;
    text-align: start;
    // margin: 0.3rem auto 0;
    // max-width: 500px;
  
}
</style>
