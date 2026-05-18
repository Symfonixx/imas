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
        <div class="inner-pages imas-blog-section-anchor" ref="pageRef">
        

            <!-- START SECTION BLOG -->
            <section class="blog blog-section">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-9 col-md-12 col-xs-12 blog-pots">
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
                        <BlogListingSidebar
                            :search-action="blogIndexUrl"
                            :filters="filters"
                            :categories="categories"
                            :recent-blogs="recentBlogs"
                            :category-url="categoryIndexUrl"
                        />
                    </div>
                    <nav
                        v-if="paginationLinks.length > 0"
                        aria-label="..."
                        class="pt-5 agents imas-blog-pagination"
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
import BlogListingSidebar from "../Components/BlogListingSidebar.vue";
import InnerPageHeadingHero from "@/components/global/InnerPageHeadingHero.vue";
import { useScrollReveal } from "@/composables/useScrollReveal";

const props = defineProps({
    title: { type: String, required: true },
    blogs: { type: Object, required: true },
    recentBlogs: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, required: true },
});

const page = usePage();
const pageRef = ref(null);
const globals = computed(() => page.props.globals ?? {});
const seo = computed(() => globals.value.seo ?? {});
const media = computed(() => globals.value.media ?? {});
function scrollToBlogTop() {
    pageRef.value?.scrollIntoView({ behavior: "smooth", block: "start" });
}

useScrollReveal(pageRef, { variant: "propertyListings" });

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

const blogHeadingItems = computed(() => {
    const rows = [];
    try {
        if (typeof route === "function" && route().has?.("home")) {
            rows.push({
                title: trans("navBar.Home"),
                href: route("home"),
            });
        }
    } catch {
        /* Ziggy may be unavailable */
    }
    rows.push({
        title: trans("navBar.Blogs"),
        href: null,
    });
    return rows;
});

const blogIndexUrl = computed(() => {
    try {
        if (typeof route === "function" && route().has?.("blog.index")) {
            return route("blog.index");
        }
    } catch {
        /* ignore */
    }
    return "";
});

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

