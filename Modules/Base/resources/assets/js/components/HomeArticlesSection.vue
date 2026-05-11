<template>
    <section v-if="articles.length" class="blog-section bg-white-2">
        <div class="container">
            <div class="sec-title">
                <h2>{{ trans("articles.title") }}</h2>
                <p>{{ trans("articles.description") }}</p>
            </div>
            <div class="news-wrap">
                <div class="row">
                    <div
                        v-for="(article, index) in articles"
                        :key="article.id"
                        class="col-xl-4 col-md-6 col-xs-12 d-flex"
                    >
                        <ArticleCard
                            :article="article"
                            :is-last="index === articles.length - 1"
                            :read-more-label="trans('articles.read_more')"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";
import ArticleCard from "@/components/articles/ArticleCard.vue";

defineProps({
    articles: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}
</script>

<style scoped lang="scss">

</style>
