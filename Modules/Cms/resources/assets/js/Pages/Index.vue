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
            v-if="robots"
            head-key="robots"
            name="robots"
            :content="robots"
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
        <div class="imas-blog-v2 imas-blog-section-anchor" ref="pageRef">
            <InnerPageHeadingHero
                :page-title="trans('blogs.hub_title')"
                :items="blogHeadingItems"
                :banner-image-url="blogShowBannerUrl"
            />

            <div class="imas-blog-v2__page container">
                <section class="imas-blog-v2__main">
                    <div
                        v-if="blogs.data.length > 0"
                        class="imas-blog-v2__grid"
                    >
                        <BlogV2ArticleCard
                            v-for="(post, idx) in blogs.data"
                            :key="post.id"
                            :article="post"
                            :stagger-index="idx"
                            :read-more-label="trans('articles.read_more')"
                            :read-article-label="readArticleCta"
                        />
                    </div>
                    <p
                    v-else
                        class="imas-blog-v2__empty text-dim text-start"
                    >
                        {{ trans("blogs.no_posts") }}
                    </p>

                    <BlogV2Pagination
                        :links="paginationLinks"
                        @navigate="scrollToBlogTop"
                    />
                </section>

                <BlogListingSidebar
                    :search-action="blogIndexUrl"
                    :filters="filters"
                    :categories="categories"
                    :recent-blogs="recentBlogs"
                    :category-url="categoryIndexUrl"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import InnerPageHeadingHero from "@/components/global/InnerPageHeadingHero.vue";
import BlogV2ArticleCard from "../Components/BlogV2ArticleCard.vue";
import BlogV2Pagination from "../Components/BlogV2Pagination.vue";
import BlogListingSidebar from "../Components/BlogListingSidebar.vue";
import { blogIndexLocalizedUrl } from "../utils/blogLocalizedRoute.js";
import { localizedRoute } from "@/utils/localizedRoute.js";
import { useDocumentSeo } from "@/composables/useDocumentSeo.js";

const props = defineProps({
    title: { type: String, required: true },
    blogs: { type: Object, required: true },
    recentBlogs: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, required: true },
});

const page = usePage();
const activeLocale = computed(() => page.props.locale || "en");
const pageRef = ref(null);

function scrollToBlogTop() {
    pageRef.value?.scrollIntoView({ behavior: "smooth", block: "start" });
}

onMounted(() => {
    scrollToBlogTop();
});

const {
    media,
    title: documentTitle,
    description: metaDescription,
    keywords: metaKeywords,
    ogTitle,
    ogDescription,
    ogImage,
    canonical: canonicalUrl,
    ogUrl,
    twitterCard,
    robots,
} = useDocumentSeo({
    pageTitle: () => props.title,
    robots: () => {
        const f = props.filters ?? {};
        const hasQuery = typeof f.q === "string" && f.q.trim() !== "";
        const hasCategory =
            typeof f.category === "string" && f.category.trim() !== "";
        return hasQuery || hasCategory ? "noindex, follow" : "";
    },
    canonical: () => blogIndexLocalizedUrl(activeLocale.value),
});

function trans(key) {
    return page.props.translations[key] || key;
}

const readArticleCta = computed(() => {
    const base = trans("articles.read_more").replace(/\.\.\.$|…$/u, "").trim();
    return `${base} ›`;
});

const blogHeadingItems = computed(() => {
    const rows = [];
    try {
        if (typeof route === "function" && route().has?.("home")) {
            rows.push({
                title: trans("navBar.Home"),
                href: localizedRoute("home", {}, activeLocale.value, "/"),
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

const blogIndexUrl = computed(() =>
    blogIndexLocalizedUrl(activeLocale.value),
);

function categoryIndexUrl(categorySlug) {
    const params = {};
    if (props.filters.q) {
        params.q = props.filters.q;
    }
    if (categorySlug != null && categorySlug !== "") {
        params.category = categorySlug;
    }
    return blogIndexLocalizedUrl(activeLocale.value, params);
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
const blogShowBannerUrl = computed(() => {
    const url = media.value.blog_show_banner;
    if (typeof url !== "string" || url.trim() === "") {
        return "";
    }
    const trimmed = url.trim();
    if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) {
        return "";
    }
    return trimmed;
});
</script>
