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
        <meta head-key="og:type" property="og:type" content="article" />
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
        <JsonLdScript head-key="jsonld-article" :content="articleJsonLd" />
    </Head>

    <AppLayout>
        <div class="imas-blog-v2 imas-blog-section-anchor">
            <InnerPageHeadingHero
                :page-title="trans('blogs.blog_details')"
                :items="blogHeadingItems"
                :banner-image-url="blogShowBannerUrl"
            />

            <main class="imas-blog-v2__page container">
                <section class="imas-blog-v2__main">
                    <article ref="articleTextRef" class="imas-blog-show">
                        <div v-if="blog.image" class="imas-blog-show__media">
                            <img
                                :src="blog.image"
                                :alt="blog.title"
                                loading="eager"
                            />
                        </div>
                        <div class="imas-blog-show__content">
                            <header
                                class="imas-blog-show-article-text__header"
                            >
                                <h1
                                    class="imas-blog-show__title text-2xl font-bold text-start"
                                >
                                    {{ blog.title }}
                                </h1>
                                <div
                                    class="imas-blog-show__meta text-md text-dim"
                                >
                                    <span
                                        v-if="blog.date"
                                        class="imas-blog-show__date"
                                        >{{ blog.date }}</span
                                    >
                                    <span
                                        v-if="blog.date && blog.visits != null"
                                        class="imas-blog-show__meta-sep"
                                        aria-hidden="true"
                                        >/</span
                                    >
                                    <span
                                        v-if="blog.visits != null"
                                        class="imas-blog-show__views"
                                    >
                                        <i
                                            class="fa fa-eye"
                                            aria-hidden="true"
                                        ></i>
                                        {{ blog.visits }}
                                    </span>
                                </div>
                                <span
                                    v-if="blog.category"
                                    class="imas-blog-show__category-label mb-4"
                                >
                                    {{ blog.category.name }}
                                </span>
                            </header>
                            <div
                                class="imas-blog-show-body text-base text-start"
                                v-html="blog.content"
                            />
                        </div>
                    </article>
                </section>

                <BlogListingSidebar
                    :search-action="blogIndexUrl"
                    :filters="filters"
                    :categories="categories"
                    :recent-blogs="recentBlogs"
                    :category-url="categoryIndexUrl"
                />
            </main>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import JsonLdScript from "@/components/Seo/JsonLdScript.vue";
import BlogListingSidebar from "../Components/BlogListingSidebar.vue";
import InnerPageHeadingHero from "@/components/global/InnerPageHeadingHero.vue";
import { useScrollReveal } from "@/composables/useScrollReveal";
import { blogIndexLocalizedUrl } from "../utils/blogLocalizedRoute.js";
import { localizedRoute } from "@/utils/localizedRoute.js";
import { buildArticleSchema } from "@/utils/structuredData.js";

const props = defineProps({
    title: { type: String, required: true },
    blog: { type: Object, required: true },
    recentBlogs: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, required: true },
});

const page = usePage();
const activeLocale = computed(() => page.props.locale || "en");
const articleTextRef = ref(null);

useScrollReveal(articleTextRef, { variant: "blogShowArticle" });

const globals = computed(() => page.props.globals ?? {});
const media = computed(() => globals.value.media ?? {});

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

function trans(key) {
    return page.props.translations[key] || key;
}

function plainText(value) {
    if (typeof value !== "string") {
        return "";
    }
    const s = value.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
    return s;
}

const blogHeadingItems = computed(() => {
    const rows = [];
    try {
        if (typeof route === "function" && route().has?.("home")) {
            rows.push({
                title: trans("navBar.Home"),
                href: localizedRoute("home", {}, activeLocale.value, "/"),
            });
        }
        if (typeof route === "function" && route().has?.("blog.index")) {
            rows.push({
                title: trans("navBar.Blogs"),
                href: blogIndexLocalizedUrl(activeLocale.value),
            });
        }
    } catch {
        /* Ziggy may be unavailable */
    }
    rows.push({
        title: props.blog.title,
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

const meta = computed(() => props.blog.meta ?? {});

const documentTitle = computed(
    () => `${plainText(String(meta.value.title || props.title))} | ${page.props.appName}`,
);

const metaDescription = computed(() => {
    const d = meta.value.description;
    return typeof d === "string" && d.trim() !== "" ? plainText(d) : "";
});

const metaKeywords = computed(() => {
    const k = meta.value.keywords;
    if (Array.isArray(k)) {
        const s = k.filter(Boolean).join(", ").trim();
        return s !== "" ? s : "";
    }
    if (typeof k === "string" && k.trim() !== "") {
        return k.trim();
    }
    return "";
});

const canonicalUrl = computed(() => {
    const u = meta.value.canonical_url;
    return typeof u === "string" && u.trim() !== "" ? u.trim() : "";
});

const ogTitle = computed(() => documentTitle.value);

const ogDescription = computed(() => metaDescription.value);

const ogImage = computed(() => {
    const u = meta.value.image;
    if (typeof u === "string" && u.trim() !== "") {
        return u.trim();
    }
    const fallback = props.blog.image;
    if (typeof fallback === "string" && fallback.trim() !== "") {
        return fallback.trim();
    }
    const siteFallback = media.value.meta_img;
    return typeof siteFallback === "string" && siteFallback.trim() !== ""
        ? siteFallback.trim()
        : "";
});

const ogUrl = computed(() => canonicalUrl.value);

const twitterCard = computed(() =>
    ogImage.value ? "summary_large_image" : "summary",
);

const articleSchema = computed(() => {
    const publisherLogo =
        media.value.white_logo ||
        media.value.black_logo ||
        media.value.meta_img ||
        "";

    return buildArticleSchema({
        headline: plainText(String(meta.value.title || props.blog.title)),
        description: metaDescription.value,
        image: ogImage.value,
        datePublished: props.blog.created_at,
        url: canonicalUrl.value,
        publisherName: page.props.appName,
        publisherLogo,
    });
});

const articleJsonLd = computed(() => {
    const schema = articleSchema.value;
    return schema ? JSON.stringify(schema) : "";
});
</script>
