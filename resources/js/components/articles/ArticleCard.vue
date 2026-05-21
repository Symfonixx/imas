<template>
    <div
        class="news-item imas-article-card imas-card imas-card--article w-100 h-100"
        :class="[{ 'no-mb': isLast }, themeRootClass]"
    >
        <a
            :href="article.url"
            class="news-img-link imas-article-card__img-link imas-card__media"
        >
            <div class="news-item-img imas-article-card__img imas-card__img">
                <img
                    class="img-responsive"
                    :src="article.image"
                    :alt="article.title"
                />
            </div>
        </a>
        <div class="news-item-text imas-card__body">
            <a :href="article.url">
                <h3 class="imas-card__title">{{ article.title }}</h3>
            </a>
            <div class="dates">
                <span v-if="article.date" class="date"
                    >{{ article.date }} &nbsp;/</span
                >
                <ul class="action-list pl-0">
                    <li class="action-item pl-2">
                        <i class="fa fa-eye"></i>
                        <span>{{ article.visits }}</span>
                    </li>
                </ul>
            </div>
            <div class="news-item-descr big-news imas-card__excerpt text-card-excerpt">
                <p>{{ article.excerpt }}</p>
            </div>
            <div class="news-item-bottom">
                <a :href="article.url" class="news-link imas-card__cta">{{
                    readMoreLabel
                }}</a>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    article: {
        type: Object,
        required: true,
    },
    isLast: {
        type: Boolean,
        default: false,
    },
    readMoreLabel: {
        type: String,
        default: "Read more...",
    },
    themeRootClass: {
        type: String,
        default: "",
    },
});
</script>

<style scoped lang="scss">
/* Equal-height row: parent col is `d-flex`; shell/colors from global .imas-card in app.css */
.imas-article-card {
    display: flex;
    flex-direction: column;
}

.imas-article-card__img-link {
    display: block;
    flex-shrink: 0;
}

.imas-article-card .news-item-text {
    display: flex;
    flex-direction: column;
    min-height: 0;
}

.action-item i {
    color: var(--brand-gold) !important;
    font-size: 1rem;
    margin-inline-end: 0.5rem;
}

.action-list {
    padding-inline-start: 5px !important;
}
</style>
