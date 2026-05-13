<template>
    <Head :title="documentTitle">
        <meta
            v-if="metaDescription"
            head-key="description"
            name="description"
            :content="metaDescription"
        />
        <meta
            v-if="metaKeywords"
            head-key="keywords"
            name="keywords"
            :content="metaKeywords"
        />
        <link
            v-if="canonicalUrl"
            head-key="canonical"
            rel="canonical"
            :href="canonicalUrl"
        />
        <meta
            v-if="ogTitle"
            head-key="og:title"
            property="og:title"
            :content="ogTitle"
        />
        <meta
            v-if="ogDescription"
            head-key="og:description"
            property="og:description"
            :content="ogDescription"
        />
        <meta
            v-if="ogImage"
            head-key="og:image"
            property="og:image"
            :content="ogImage"
        />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta
            v-if="ogUrl"
            head-key="og:url"
            property="og:url"
            :content="ogUrl"
        />
        <meta
            head-key="twitter:card"
            name="twitter:card"
            :content="twitterCard"
        />
        <meta
            v-if="ogTitle"
            head-key="twitter:title"
            name="twitter:title"
            :content="ogTitle"
        />
        <meta
            v-if="ogDescription"
            head-key="twitter:description"
            name="twitter:description"
            :content="ogDescription"
        />
        <meta
            v-if="ogImage"
            head-key="twitter:image"
            name="twitter:image"
            :content="ogImage"
        />
    </Head>

    <AppLayout>
        <div class="inner-pages imas-blog-section-anchor" ref="blogSectionRef">
            <section class="headings">
                <div class="text-heading text-center">
                    <div class="container">
                        <h1>{{ title }}</h1>
                        <h2>
                            <Link :href="route('home')"
                                >{{ trans("navBar.Home") }}
                            </Link>
                            &nbsp;/&nbsp; {{ trans("navBar.Blogs") }}
                        </h2>
                    </div>
                </div>
            </section>
            <!-- END SECTION HEADINGS -->
            <!-- START SECTION BLOG -->
            <section class="blog blog-section">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-9 col-md-12 col-xs-12">
                            <template
                                v-for="(row, rowIdx) in blogRows"
                                :key="'row-' + rowIdx"
                            >
                                <div :class="row.rowClass">
                                    <div
                                        v-for="(post, idx) in row.pair"
                                        :key="post.id"
                                        class="col-md-6 col-xs-12"
                                        :class="row.colExtraClass"
                                    >
                                        <ArticleCard
                                            :article="post"
                                            :is-last="false"
                                            :theme-root-class="
                                                articleThemeRootClass(
                                                    rowIdx,
                                                    idx,
                                                )
                                            "
                                            :read-more-label="
                                                trans('articles.read_more')
                                            "
                                        />
                                    </div>
                                </div>
                            </template>
                            <div v-if="blogs.data.length === 0" class="row">
                                <div class="col-12">
                                    <p class="text-muted py-4">
                                        {{ trans("blogs.no_posts") }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <aside class="col-lg-3 col-md-12">
                            <div class="widget">
                                <h5 class="font-weight-bold mb-4 text-start">
                                    {{ trans("blogs.search") }}
                                </h5>
                                <form
                                    :action="route('blog.index')"
                                    method="get"
                                >
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
                                            :placeholder="
                                                trans(
                                                    'blogs.search_placeholder',
                                                )
                                            "
                                            :value="filters.q ?? ''"
                                            autocomplete="off"
                                        />
                                        <span class="input-group-btn mx-1">
                                            <button
                                                class="btn btn-primary"
                                                type="submit"
                                            >
                                                <i
                                                    class="fa fa-search"
                                                    aria-hidden="true"
                                                ></i>
                                            </button>
                                        </span>
                                    </div>
                                </form>
                                <div class="recent-post py-5">
                                    <h5 class="font-weight-bold text-start">
                                        {{ trans("blogs.categories") }}
                                    </h5>
                                    <ul>
                                        <li>
                                            <Link
                                                :href="categoryIndexUrl(null)"
                                            >
                                                <i
                                                    class="fa fa-caret-right"
                                                    aria-hidden="true"
                                                ></i
                                                >{{
                                                    trans(
                                                        "blogs.all_categories",
                                                    )
                                                }}
                                            </Link>
                                        </li>
                                        <li v-for="c in categories" :key="c.id">
                                            <Link
                                                :href="categoryIndexUrl(c.id)"
                                            >
                                                <i
                                                    class="fa fa-caret-right"
                                                    aria-hidden="true"
                                                ></i
                                                >{{ c.name }}
                                            </Link>
                                        </li>
                                    </ul>
                                </div>
                                <div
                                    class="widget-boxed mt-5 imas-recent-blogs-sidebar"
                                >
                                    <div
                                        class="widget-boxed-header d-flex justify-content-between align-items-center"
                                    >
                                        <h4>
                                            {{ trans("blogs.recent_posts") }}
                                        </h4>
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
                                                        <img
                                                            :src="r.image"
                                                            :alt="r.title"
                                                        />
                                                    </a>
                                                </div>
                                                <div class="info-img">
                                                    <a :href="r.url">
                                                        <h6>{{ r.title }}</h6>
                                                    </a>
                                                    <p class="mt-1">
                                                        {{ r.date }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                    <nav
                        v-if="paginationLinks.length > 0"
                        aria-label="..."
                        class="pt-5"
                    >
                        <ul class="pagination">
                            <li
                                v-for="(link, idx) in paginationLinks"
                                :key="idx"
                                class="page-item"
                                :class="{
                                    active: link.active,
                                    disabled: !link.url,
                                }"
                            >
                                <Link
                                    v-if="link.url"
                                    class="page-link"
                                    :href="link.url"
                                    :preserve-scroll="false"
                                    @click="scrollToBlogTop"
                                >
                                    <span v-html="link.displayLabel" />
                                </Link>
                                <span v-else class="page-link">
                                    <span v-html="link.displayLabel" />
                                </span>
                            </li>
                        </ul>
                    </nav>
                </div>
            </section>
            <!-- END SECTION BLOG -->
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import ArticleCard from "@/components/articles/ArticleCard.vue";

const props = defineProps({
    title: { type: String, required: true },
    blogs: { type: Object, required: true },
    recentBlogs: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, required: true },
});

const page = usePage();
const blogSectionRef = ref(null);
const globals = computed(() => page.props.globals ?? {});
const seo = computed(() => globals.value.seo ?? {});
const media = computed(() => globals.value.media ?? {});
function scrollToBlogTop() {
    blogSectionRef.value?.scrollIntoView({ behavior: "smooth", block: "start" });
}
onMounted(() => {
    scrollToBlogTop();
});
function pickSeoString(...keys) {
    const s = seo.value;
    for (const key of keys) {
        const v = s[key];
        if (typeof v === "string" && v.trim() !== "") {
            return v.trim();
        }
    }
    return "";
}

const documentTitle = computed(() => `${props.title} | ${page.props.appName}`);

const metaDescription = computed(() =>
    pickSeoString("site_meta_description", "website_desc"),
);

const metaKeywords = computed(() =>
    pickSeoString("site_meta_keywords", "website_keywords"),
);

const ogTitle = computed(() => documentTitle.value);
const ogDescription = computed(() => metaDescription.value);

const ogImage = computed(() => {
    const url = media.value.meta_img;
    return typeof url === "string" && url.trim() !== "" ? url.trim() : "";
});

const canonicalUrl = computed(() => {
    if (typeof route !== "function" || !route().has?.("blog.index")) {
        return "";
    }
    try {
        return route("blog.index");
    } catch {
        return "";
    }
});

const ogUrl = computed(() => canonicalUrl.value);

const twitterCard = computed(() =>
    ogImage.value ? "summary_large_image" : "summary",
);

function trans(key) {
    return page.props.translations[key] || key;
}

/** Preserve text search when switching category (and vice versa). */
function categoryIndexUrl(categoryId) {
    const params = {};
    if (props.filters.q) {
        params.q = props.filters.q;
    }
    if (categoryId != null) {
        params.category_id = categoryId;
    }
    return route("blog.index", params);
}

const blogRows = computed(() => {
    const items = props.blogs?.data ?? [];
    const pairs = [];
    for (let i = 0; i < items.length; i += 2) {
        pairs.push(items.slice(i, i + 2));
    }
    const lastIdx = pairs.length - 1;
    return pairs.map((pair, rowIdx) => ({
        pair,
        rowClass: rowIdx === 1 && pairs.length > 1 ? "row space2 port" : "row",
        colExtraClass: rowIdx === lastIdx && lastIdx >= 0 ? "no-mb wpt-2" : "",
    }));
});

function articleThemeRootClass(rowIdx, idxInPair) {
    return rowIdx === 0 && idxInPair === 0 ? "nomb" : "";
}

const paginationLinks = computed(() => {
    const raw = props.blogs?.links ?? [];
    const n = raw.length;
    if (n < 2) {
        return raw.map((link) => ({ ...link, displayLabel: link.label }));
    }
    return raw.map((link, idx) => {
        let displayLabel = link.label;
        if (idx === 0) {
            displayLabel = trans("global.previous");
        } else if (idx === n - 1) {
            displayLabel = trans("global.next");
        }
        return { ...link, displayLabel };
    });
});
</script>

<style lang="scss">
/* Same layout as RecentPropertiesSidebar.vue; distinct root class from property sidebar. */
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
.inner-pages .recent-post ul li {
    text-align: start !important;

    html[dir="ltr"] & i {
        transform: rotate(180deg) !important;
    }
    i {
        margin-inline-end: 10px !important;
    }
}
</style>
