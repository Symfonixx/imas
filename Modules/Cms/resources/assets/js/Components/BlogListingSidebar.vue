<template>
    <aside class="col-lg-3 col-md-12 imas-blog-listing-sidebar">
        <div class="widget">
            <template v-if="showSearch && searchAction">
                <h5 class="font-weight-bold mb-4 text-start">
                    {{ trans("blogs.search") }}
                </h5>
                <form :action="searchAction" method="get">
                    <input
                        v-if="filters.category_id"
                        type="hidden"
                        name="category_id"
                        :value="filters.category_id"
                    />
                    <div class="input-group">
                        <input
                            type="text"
                            name="q"
                            class="form-control"
                            :placeholder="trans('blogs.search_placeholder')"
                            :value="filters.q ?? ''"
                            autocomplete="off"
                        />
                        <span class="input-group-btn mx-1">
                            <button class="btn btn-primary" type="submit">
                                <i
                                    class="fa fa-search"
                                    aria-hidden="true"
                                ></i>
                            </button>
                        </span>
                    </div>
                </form>
            </template>

            <div v-if="showCategories" class="recent-post py-5">
                <h5 class="font-weight-bold text-start">
                    {{ trans("blogs.categories") }}
                </h5>
                <ul>
                    <li>
                        <Link :href="categoryUrl(null)">
                            <i
                                class="fa fa-caret-right"
                                aria-hidden="true"
                            ></i>{{ trans("blogs.all_categories") }}
                        </Link>
                    </li>
                    <li v-for="c in categories" :key="c.id">
                        <Link :href="categoryUrl(c.id)">
                            <i
                                class="fa fa-caret-right"
                                aria-hidden="true"
                            ></i>{{ c.name }}
                        </Link>
                    </li>
                </ul>
            </div>

            <div
                v-if="showRecentPosts"
                class="widget-boxed mt-5 imas-recent-blogs-sidebar"
            >
                <div
                    class="widget-boxed-header d-flex justify-content-between align-items-center"
                >
                    <h4>{{ trans("blogs.recent_posts") }}</h4>
                </div>
                <div class="widget-boxed-body">
                    <div class="recent-post">
                        <div
                            v-for="r in recentBlogs"
                            :key="r.id"
                            class="recent-main"
                        >
                            <div class="recent-img">
                                <a :href="r.url">
                                    <img :src="r.image" :alt="r.title" />
                                </a>
                            </div>
                            <div class="info-img">
                                <a :href="r.url">
                                    <h6>{{ r.title }}</h6>
                                </a>
                                <p class="mt-1">{{ r.date }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { Link, usePage } from "@inertiajs/vue3";

const props = defineProps({
    /** GET form action (e.g. `route('blog.index')`). */
    searchAction: {
        type: String,
        default: "",
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    categories: {
        type: Array,
        default: () => [],
    },
    recentBlogs: {
        type: Array,
        default: () => [],
    },
    /** `(categoryId: number | null) => string` — build blog index URL with filters. */
    categoryUrl: {
        type: Function,
        required: true,
    },
    showSearch: {
        type: Boolean,
        default: true,
    },
    showCategories: {
        type: Boolean,
        default: true,
    },
    showRecentPosts: {
        type: Boolean,
        default: true,
    },
});

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}
</script>

<style lang="scss">
.imas-recent-blogs-sidebar .recent-post .recent-main:not(:last-child) {
    margin-bottom: 1.5rem;
}

.imas-recent-blogs-sidebar .recent-main {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    gap: 0.875rem;
}

.imas-recent-blogs-sidebar .recent-img {
    flex: 0 0 80px;
    width: 80px;
    height: 70px;
    min-width: 80px;
    min-height: 70px;
    max-width: 80px;
    max-height: 70px;
    overflow: hidden;
    border-radius: 4px;
}

.imas-recent-blogs-sidebar .recent-img > a {
    display: block;
    width: 100%;
    height: 100%;
    line-height: 0;
}

.imas-recent-blogs-sidebar .recent-img img {
    display: block;
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    max-height: none !important;
    object-fit: cover;
    object-position: center;
    margin: 0 !important;
}

.imas-recent-blogs-sidebar .info-img {
    flex: 1 1 0;
    min-width: 0;
    text-align: start;
}

.imas-recent-blogs-sidebar .info-img a {
    display: block;
    text-align: start;
}

.imas-recent-blogs-sidebar .info-img p {
    text-align: start;
    margin-bottom: 0;
}

.imas-recent-blogs-sidebar .info-img h6 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
    overflow-wrap: anywhere;
    line-height: 1.35;
    margin: 0;
    text-align: start;
    min-height: calc(1.35em * 2);
}

@media screen and (max-width: 992px) {
    .inner-pages .imas-recent-blogs-sidebar .recent-main {
        flex-wrap: nowrap;
    }

    .inner-pages .imas-recent-blogs-sidebar .info-img {
        margin-top: 0;
    }
}

.inner-pages .imas-recent-blogs-sidebar .recent-img img {
    width: 100% !important;
    height: 100% !important;
    margin: 0 !important;
}
</style>

<style scoped lang="scss">
.imas-blog-listing-sidebar .recent-post ul li {
    text-align: start !important;

    html[dir="ltr"] & i {
        transform: rotate(180deg) !important;
    }

    i {
        margin-inline-end: 10px !important;
    }
}
</style>
