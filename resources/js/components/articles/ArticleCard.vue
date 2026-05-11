<template>
    <div
        class="news-item imas-article-card w-100 h-100"
        :class="{ 'no-mb': isLast }"
    >
        <a :href="article.url" class="news-img-link imas-article-card__img-link">
            <div class="news-item-img imas-article-card__img">
                <img
                    :src="article.image"
                    :alt="article.title"
                />
            </div>
        </a>
        <div class="news-item-text">
            <a :href="article.url">
                <h3 >{{ article.title }}</h3>
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
            <div class="news-item-descr big-news">
                <p>{{ article.excerpt }}</p>
            </div>
            <div class="news-item-bottom">
                <a :href="article.url" class="news-link">{{
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
});
</script>

<style scoped lang="scss">
/* Equal-height row: parent col is `d-flex`; card fills height and stacks image + body. */
.imas-article-card {
    display: flex;
    flex-direction: column;
}

.imas-article-card__img-link {
    display: block;
    flex-shrink: 0;
}

.imas-article-card__img {
    height: 220px;
    overflow: hidden;
    background: #eee;
}

.imas-article-card__img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}

.imas-article-card .news-item-text {
    // flex: 1 1 auto;
    display: flex;
    text-align: start;
    flex-direction: column;
    min-height: 0;
}

.imas-article-card .news-item-bottom {
    margin-top: auto;
}

.action-item  i{
    color: var( --color-action-primary) !important;
    font-size: 1rem;
    margin-right: 0.5rem;
}
html[dir="rtl"] .action-item i{
    margin-right: 0;
    margin-left: 0.5rem;
}

.action-list{
    padding-inline-start: 5px !important;

}
</style>
