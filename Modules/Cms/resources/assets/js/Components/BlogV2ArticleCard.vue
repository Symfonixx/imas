<template>
    <article
        ref="cardRef"
        class="imas-blog-v2-card"
        :class="{ 'is-visible': isVisible }"
        :style="staggerStyle"
    >
        <a
            :href="article.url"
            class="imas-blog-v2-card__link"
            :aria-label="article.title"
        />
        <div class="imas-blog-v2-card__thumb">
            <img :src="article.image" :alt="article.image_alt || article.title" loading="lazy" />
        </div>
        <span
            v-if="categoryName"
            class="imas-blog-show__category-label imas-blog-v2-card__category-label"
        >
            {{ categoryName }}
        </span>
        <div
            class="imas-blog-v2-card__body"
            :class="{ 'imas-blog-v2-card__body--has-category': categoryName }"
        >
            <h3 class="imas-blog-v2-card__title text-md font-semibold text-start">
                {{ article.title }}
            </h3>
            <div class="imas-blog-v2-card__meta text-md text-dim">
                <span v-if="article.date">{{ article.date }}</span>
                <span
                    v-if="article.date && article.visits != null"
                    class="imas-blog-v2-card__dot"
                    aria-hidden="true"
                    >/</span
                >
                <span
                    v-if="article.visits != null"
                    class="imas-blog-v2-card__views"
                >
                    <i class="fa fa-eye" aria-hidden="true"></i>
                    {{ article.visits }}
                </span>
            </div>
            <p
                v-if="article.excerpt"
                class="imas-blog-v2-card__excerpt text-card-excerpt text-dim"
            >
                {{ article.excerpt }}
            </p>
            <div class="imas-blog-v2-card__cta-wrap">
                <span class="imas-blog-v2-card__cta-text">{{
                    readMoreLabel
                }}</span>
                <span class="imas-blog-v2-card__pill">
                    {{ readArticleLabel }}
                </span>
            </div>
        </div>
    </article>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";

const props = defineProps({
    article: { type: Object, required: true },
    staggerIndex: { type: Number, default: 0 },
    readMoreLabel: { type: String, default: "Read more" },
    readArticleLabel: { type: String, default: "Read article ›" },
});

const cardRef = ref(null);
const isVisible = ref(false);
let observer = null;

const staggerStyle = computed(() => ({
    transitionDelay: `${props.staggerIndex * 100}ms`,
}));

const categoryName = computed(() => {
    const name = props.article?.category?.name;
    return typeof name === "string" && name.trim() !== "" ? name.trim() : "";
});

onMounted(() => {
    const el = cardRef.value;
    if (!el) return;

    const prefersReduced = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;
    if (prefersReduced) {
        isVisible.value = true;
        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    isVisible.value = true;
                    observer?.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12 },
    );
    observer.observe(el);
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
});
</script>
