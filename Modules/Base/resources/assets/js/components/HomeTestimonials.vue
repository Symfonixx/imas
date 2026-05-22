<template>
    <section
        v-if="testimonials.length"
        ref="sectionRef"
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
                    class="singleJobClinet bg-gray"
                >
                    <p class="quote" v-html="item.quote"></p>
                    <div class="detailJC">
                        <span><img :src="item.avatar" :alt="item.name" /></span>
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
import { computed, onBeforeUnmount, onMounted, ref, nextTick, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useScrollReveal } from "@/composables/useScrollReveal";
import { refreshScrollTrigger } from "@/plugins/gsap";
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
const sectionRef = ref(null);

useScrollReveal(sectionRef, {
    preset: "home",
    variant: "carousel",
    when: computed(() => props.testimonials.length > 0),
});

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
    const rtl = String(page.props.text_direction || "").toLowerCase() === "rtl";
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
        nextTick(() => {
            initOwl();
            refreshScrollTrigger();
        });
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
/* Equal-height cards in Owl carousel */
.home-testimonials :deep(.owl-stage) {
    display: flex;
    align-items: stretch;
}

.home-testimonials :deep(.owl-item) {
    display: flex;
    height: auto;
}

.home-testimonials .singleJobClinet {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    width: 100%;
    height: 100%;
    margin-bottom: 0;
}

.quote {
    flex: 1 1 auto;
    text-align: start;
    font-size: 16px;
    line-height: 1.5;
    margin-bottom: 25px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    text-overflow: ellipsis;
    word-break: break-word;
}

.quote :deep(p) {
    margin: 0;
    display: inline;
}

.quote :deep(p + p) {
    display: block;
    margin-top: 0.5em;
}

.detailJC {
    flex-shrink: 0;
    margin-top: auto;
}

.homepage-1 .detailJC span {
    background: var(--brand-gold);
}
</style>
