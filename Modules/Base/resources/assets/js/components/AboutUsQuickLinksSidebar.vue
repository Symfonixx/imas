<template>
    <aside
        v-if="showSidebar"
        class="imas-blog-v2-sidebar imas-about-page__quick-links"
    >
        <div v-if="quickLinks.length" class="imas-blog-v2-sidebar__box">
            <h4 class="imas-blog-v2-sidebar__heading">
                {{ trans("aboutUs.explore_more") }}
            </h4>
            <div class="imas-blog-v2-sidebar__recent">
                <Link
                    v-for="link in quickLinks"
                    :key="link.id"
                    :href="link.url"
                    class="imas-blog-v2-sidebar__recent-item"
                >
                    <img
                        v-if="link.image"
                        :src="link.image"
                        :alt="link.title"
                        loading="lazy"
                    />
                    <div>
                        <div class="imas-blog-v2-sidebar__recent-title">
                            {{ link.title }}
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <FeaturedPropertiesSidebar
            v-if="featuredProperties.length > 0"
            :featured-properties="featuredProperties"
            :heading="featuredPropertiesHeading"
        />
    </aside>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { localizedRoute } from "@/utils/localizedRoute.js";
import FeaturedPropertiesSidebar from "../../../../../../Modules/Property/resources/assets/js/components/FeaturedPropertiesSidebar.vue";

const props = defineProps({
    featuredProperties: { type: Array, default: () => [] },
});

const page = usePage();
const globals = computed(() => page.props.globals ?? {});
const media = computed(() => globals.value.media ?? {});
const activeLocale = computed(() => page.props.locale || "en");

function trans(key) {
    return page.props.translations[key] || key;
}

const featuredPropertiesHeading = computed(() =>
    trans("aboutUs.featured_properties"),
);

const showSidebar = computed(
    () => quickLinks.value.length > 0 || props.featuredProperties.length > 0,
);

function resolveMediaBanner(url) {
    if (typeof url !== "string" || url.trim() === "") {
        return "";
    }
    const trimmed = url.trim();
    if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) {
        return "";
    }
    return trimmed;
}

function mediaFallback() {
    const meta = media.value.meta_img;
    return typeof meta === "string" && meta.trim() !== "" ? meta.trim() : "";
}

function resolveRouteUrl(name, fallbackPath) {
    try {
        if (typeof route === "function" && route().has?.(name)) {
            return route(name);
        }
    } catch {
        /* Ziggy may be unavailable */
    }
    return localizedRoute(name, {}, activeLocale.value, fallbackPath);
}

const quickLinks = computed(() => {
    const fallback = mediaFallback();
    const rows = [
        {
            id: "turkish-citizenship",
            title: trans("navBar.Turkish Citizenship"),
            url: resolveRouteUrl(
                "turkish-citizenship",
                "/turkish-citizenship",
            ),
            image:
                resolveMediaBanner(media.value.turkish_citizenship_banner) ||
                fallback,
        },
        {
            id: "blog",
            title: trans("navBar.Blogs"),
            url: resolveRouteUrl("blog.index", "/blog"),
            image:
                resolveMediaBanner(media.value.blog_show_banner) || fallback,
        },
    ];

    return rows.filter((row) => row.url);
});
</script>
