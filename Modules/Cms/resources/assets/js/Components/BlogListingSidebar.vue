<template>
    <aside
        class="imas-blog-v2-sidebar"
        :class="{ 'col-lg-3 col-md-12': asColumn }"
    >
        <div v-if="showSearch && searchAction" class="imas-blog-v2-sidebar__box">
            <h4 class="imas-blog-v2-sidebar__heading text-start">
                {{ trans("blogs.search") }}
            </h4>
            <form
                :action="searchAction"
                method="get"
                class="imas-blog-v2-sidebar__search"
            >
                <input
                    v-if="filters.category"
                    type="hidden"
                    name="category"
                    :value="filters.category"
                />
                <input
                    type="text"
                    name="q"
                    class="imas-blog-v2-sidebar__search-input"
                    :placeholder="trans('blogs.search_placeholder')"
                    :value="filters.q ?? ''"
                    autocomplete="off"
                />
                <button
                    type="submit"
                    class="imas-blog-v2-sidebar__search-btn"
                    :aria-label="trans('blogs.search')"
                >
                    <i class="fa fa-search" aria-hidden="true"></i>
                </button>
            </form>
        </div>

        <div v-if="showCategories" class="imas-blog-v2-sidebar__box">
            <h4 class="imas-blog-v2-sidebar__heading text-start">
                {{ trans("blogs.categories") }}
            </h4>
            <ul class="imas-blog-v2-sidebar__cat-list">
                <li>
                    <Link
                        :href="categoryUrl(null)"
                        class="imas-blog-v2-sidebar__cat-link"
                        :class="{ 'is-active': !filters.category }"
                    >
                        {{ trans("blogs.all_categories") }}
                    </Link>
                </li>
                <li v-for="c in categories" :key="c.id">
                    <Link
                        :href="categoryUrl(c.slug)"
                        class="imas-blog-v2-sidebar__cat-link"
                        :class="{
                            'is-active':
                                filters.category != null &&
                                filters.category === c.slug,
                        }"
                    >
                        {{ c.name }}
                    </Link>
                </li>
            </ul>
        </div>

        <div v-if="showRecentPosts" class="imas-blog-v2-sidebar__box">
            <h4 class="imas-blog-v2-sidebar__heading text-start">
                {{ trans("blogs.recent_posts") }}
            </h4>
            <div class="imas-blog-v2-sidebar__recent">
                <a
                    v-for="r in recentBlogs"
                    :key="r.id"
                    :href="r.url"
                    class="imas-blog-v2-sidebar__recent-item"
                >
                    <img :src="r.image" :alt="r.title" loading="lazy" />
                    <div>
                        <div class="imas-blog-v2-sidebar__recent-title">
                            {{ r.title }}
                        </div>
                        <div
                            v-if="r.date"
                            class="imas-blog-v2-sidebar__recent-date text-dim text-start"
                        >
                            {{ r.date }}
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { Link, usePage } from "@inertiajs/vue3";

defineProps({
    /** When true, adds Bootstrap column classes (blog detail page row layout). */
    asColumn: { type: Boolean, default: false },
    searchAction: { type: String, default: "" },
    filters: { type: Object, default: () => ({}) },
    categories: { type: Array, default: () => [] },
    recentBlogs: { type: Array, default: () => [] },
    categoryUrl: { type: Function, required: true },
    showSearch: { type: Boolean, default: true },
    showCategories: { type: Boolean, default: true },
    showRecentPosts: { type: Boolean, default: true },
});

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}
</script>
