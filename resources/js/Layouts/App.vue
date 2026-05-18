<template>
    <div id="wrapper">
        <UserTopBar />
        <UserNavbar
            :nav-links="navLinks"
            :transparent-navbar="navbarTransparent"
        />
        <div class="clearfix"></div>
        <slot />
        <UserFooter :nav-links="navLinks" />
        <FloatingWhatsAppButton />
    </div>
</template>

<script setup>
import { computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import UserNavbar from "@/Layouts/Findhouses/UserNavbar.vue";
import UserTopBar from "@/Layouts/Findhouses/UserTopBar.vue";
import UserFooter from "@/Layouts/Findhouses/UserFooter.vue";
import FloatingWhatsAppButton from "@/components/Global/FloatingWhatsAppButton.vue";
import { cmsPageUrl } from "@/utils/cmsPageUrl.js";

const page = usePage();

/** Home hero uses overlay header (white logo / light links). Inner pages need solid bar + in-flow height. */
const navbarTransparent = computed(() => {
    try {
        if (typeof route === "function" && route().current?.("home")) {
            return true;
        }
    } catch {
        /* Ziggy may be unavailable during SSR/tests */
    }
    const c = String(page.component || "");
    // Match Inertia name from `Inertia::render('Base::Index', …)` (also tolerate `Base/Index`).
    return /^Base(::|\/)Index$/i.test(c);
});

function safeRoute(name, fallbackHref = "#") {
    try {
        // Ziggy exposes a callable `route()` and a `route().has()` helper.
        if (typeof route === "function" && route().has?.(name)) {
            return route(name);
        }
    } catch {
        // ignore
    }
    return fallbackHref;
}

function blogCategoryUrl(categoryId) {
    try {
        if (typeof route === "function" && route().has?.("blog.index")) {
            return route("blog.index", { category_id: categoryId });
        }
    } catch {
        // ignore
    }
    const base = safeRoute("blog.index", "/blog");
    const sep = base.includes("?") ? "&" : "?";
    return `${base}${sep}category_id=${categoryId}`;
}

const blogNavCategories = computed(
    () => page.props.globals?.blog_categories ?? [],
);

const navbarPages = computed(() => page.props.globals?.pages?.navbar ?? []);

const navLinks = computed(() => {
    const blogCategoryChildren = blogNavCategories.value.map((c) => ({
        key: `blog-category-${c.id}`,
        label: c.name,
        href: blogCategoryUrl(c.id),
    }));

    const blogsNav = {
        key: "navBar.Blogs",
        href: safeRoute("blog.index"),
        ...(blogCategoryChildren.length > 0
            ? { children: blogCategoryChildren }
            : {}),
    };

    const pageNavChildren = navbarPages.value.map((p) => ({
        key: `page-${p.id}`,
        label: p.title,
        href: cmsPageUrl(p.slug),
    }));

    const links = [
        { key: "navBar.Home", href: safeRoute("home", "/") },
        { key: "navBar.Buy Real Estate", href: safeRoute("property.index") },
        {
            key: "navBar.Turkish Citizenship",
            href: safeRoute("turkish-citizenship", "/turkish-citizenship"),
        },
        blogsNav,
    ];

    if (pageNavChildren.length > 0) {
        links.push({
            key: "navBar.Pages",
            children: pageNavChildren,
        });
    }
    links.push({
        key: "navBar.Contact us",
        href: safeRoute("support.contact-us", "/contact-us"),
    });

    return links;
});

function syncDocumentTextDirection() {
    const locale = page.props.locale || "en";
    const dir = page.props.text_direction || (locale === "ar" ? "rtl" : "ltr");
    document.documentElement.setAttribute("lang", String(locale));
    document.documentElement.setAttribute("dir", String(dir));
}

watch(
    () => [page.props.locale, page.props.text_direction],
    () => syncDocumentTextDirection(),
    { immediate: true },
);
</script>
