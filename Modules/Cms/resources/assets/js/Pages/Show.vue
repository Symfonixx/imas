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
    </Head>

    <AppLayout>
        <div class="inner-pages">
            <InnerPageHeadingHero
                :page-title="trans('blogs.blog_details')"
                :items="blogHeadingItems"
            />
            <!-- START SECTION BLOG -->
            <section class="blog blog-section bg-white">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-9 col-md-12 blog-pots">
                            <div class="row">
                                <div class="col-md-12 col-xs-12">
                                    <div class="news-item details no-mb2">
                                        <a :href="blog.url" class="news-img-link">
                                            <div class="news-item-img">
                                                <img
                                                    class="img-responsive"
                                                    :src="blog.image"
                                                    :alt="blog.title"
                                                />
                                            </div>
                                        </a>
                                        <div class="news-item-text details pb-0 text-start">
                                            <h3>{{ blog.title }}</h3>
                                            <div class="dates">
                                                <span
                                                    v-if="blog.date"
                                                    class="date"
                                                    >{{ blog.date }} &nbsp;/</span
                                                >
                                                <ul class="action-list px-2">
                                                    <li class="action-item pl-2">
                                                        <i class="fa fa-eye mx-1"></i>
                                                        <span>{{ blog.visits }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <p
                                                v-if="blog.category"
                                                class="text-muted small mb-2"
                                            >
                                                {{ blog.category.name }}
                                            </p>
                                            <div
                                                class="news-item-descr big-news details visib mb-0 imas-blog-show-body"
                                                v-html="blog.content"
                                            />
                                        </div>
                                    </div>
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
                </div>
            </section>
            <!-- END SECTION BLOG -->
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import BlogListingSidebar from "../Components/BlogListingSidebar.vue";
import InnerPageHeadingHero from "@/components/global/InnerPageHeadingHero.vue";

const props = defineProps({
    title: { type: String, required: true },
    blog: { type: Object, required: true },
    recentBlogs: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, required: true },
});

const page = usePage();

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
                href: route("home"),
            });
        }
        if (typeof route === "function" && route().has?.("blog.index")) {
            rows.push({
                title: trans("navBar.Blogs"),
                href: route("blog.index"),
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
    return typeof fallback === "string" && fallback.trim() !== ""
        ? fallback.trim()
        : "";
});

const ogUrl = computed(() => canonicalUrl.value);

const twitterCard = computed(() =>
    ogImage.value ? "summary_large_image" : "summary",
);
</script>

<style scoped lang="scss">
/* Theme body copy is HTML from CMS; keep typography readable inside Find Houses card */
.imas-blog-show-body :deep(p) {
    margin-bottom: 1rem;
}

.imas-blog-show-body :deep(img) {
    max-width: 100%;
    height: auto;
}
.blog-section .news-item {
    border: none !important;
}
</style>
