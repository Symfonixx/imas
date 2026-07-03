<template>
    <Head>
        <meta
            v-if="siteName"
            head-key="og:site_name"
            property="og:site_name"
            :content="siteName"
        />
        <meta
            head-key="og:locale"
            property="og:locale"
            :content="ogLocale"
        />
        <meta
            v-for="alt in ogLocaleAlternates"
            :key="alt.key"
            :head-key="alt.key"
            property="og:locale:alternate"
            :content="alt.value"
        />
        <link
            v-for="alt in hreflangAlternates"
            :key="alt.key"
            :head-key="alt.key"
            rel="alternate"
            :hreflang="alt.hreflang"
            :href="alt.url"
        />
    </Head>

    <div id="wrapper" class="imas-theme-dark">
        <UserTopBar />
        <UserNavbar
            :nav-links="navLinks"
            :transparent-navbar="navbarTransparent"
        />
        <div class="clearfix"></div>
        <slot />
        <UserFooter :nav-links="navLinks" />
        <ClientOnly>
            <FloatingContactButton />
            <FloatingWhatsAppButton />
        </ClientOnly>
    </div>
</template>

<script setup>
import { computed, watch, onMounted } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import UserNavbar from "@/Layouts/Findhouses/UserNavbar.vue";
import UserTopBar from "@/Layouts/Findhouses/UserTopBar.vue";
import UserFooter from "@/Layouts/Findhouses/UserFooter.vue";
import FloatingContactButton from "@/components/Global/FloatingContactButton.vue";
import FloatingWhatsAppButton from "@/components/Global/FloatingWhatsAppButton.vue";
import { cmsPageUrl } from "@/utils/cmsPageUrl.js";
import { localizedRoute } from "@/utils/localizedRoute.js";
import { syncZiggy } from "@/utils/syncZiggy.js";
import { isBrowser } from "@/utils/isBrowser.js";
import ClientOnly from "@/components/Global/ClientOnly.vue";

const page = usePage();

const activeLocale = computed(() => page.props.locale || "en");

const siteName = computed(() => String(page.props.appName || "").trim());

/** Map short locale codes to Open Graph locale identifiers (e.g. `en` → `en_US`). */
const OG_LOCALE_MAP = {
    en: "en_US",
    tr: "tr_TR",
    ar: "ar_AR",
};

function toOgLocale(code) {
    const key = String(code || "").toLowerCase();
    return OG_LOCALE_MAP[key] || key;
}

const ogLocale = computed(() => toOgLocale(activeLocale.value));

/** Other supported locales as og:locale:alternate values. */
const ogLocaleAlternates = computed(() => {
    const switcher = page.props.locale_switcher ?? [];
    if (!Array.isArray(switcher)) {
        return [];
    }

    return switcher
        .map((item) => String(item?.code ?? ""))
        .filter((code) => code !== "" && code !== activeLocale.value)
        .map((code) => ({
            key: `og-locale-alt-${code}`,
            value: toOgLocale(code),
        }));
});

/** Multilingual SEO: alternate URLs for the current page (en / tr / ar + x-default). */
const hreflangAlternates = computed(() => {
    const switcher = page.props.locale_switcher ?? [];
    if (!Array.isArray(switcher) || switcher.length === 0) {
        return [];
    }

    const items = switcher
        .filter((item) => typeof item?.url === "string" && item.url.trim() !== "")
        .map((item) => ({
            hreflang: String(item.code ?? ""),
            url: item.url.trim(),
            key: `hreflang-${item.code}`,
        }));

    const en = switcher.find((item) => item.code === "en");
    if (en?.url && typeof en.url === "string" && en.url.trim() !== "") {
        items.push({
            hreflang: "x-default",
            url: en.url.trim(),
            key: "hreflang-x-default",
        });
    }

    return items;
});

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
    return localizedRoute(name, {}, activeLocale.value, fallbackHref);
}

function blogCategoryUrl(categoryId) {
    const base = localizedRoute(
        "blog.index",
        {},
        activeLocale.value,
        "/blog",
    );
    const sep = base.includes("?") ? "&" : "?";
    return `${base}${sep}category_id=${categoryId}`;
}

const blogNavCategories = computed(
    () => page.props.globals?.blog_categories ?? [],
);

const navbarPages = computed(() => page.props.globals?.pages?.navbar ?? []);

const navLinks = computed(() => {
    const loc = activeLocale.value;

    const blogCategoryChildren = blogNavCategories.value.map((c) => ({
        key: `blog-category-${c.id}`,
        label: c.name,
        href: blogCategoryUrl(c.id),
    }));

    const blogsNav = {
        key: "navBar.Blogs",
        href: safeRoute("blog.index", "/blog"),
        ...(blogCategoryChildren.length > 0
            ? { children: blogCategoryChildren }
            : {}),
    };

    const pageNavChildren = navbarPages.value.map((p) => ({
        key: `page-${p.id}`,
        label: p.title,
        href: cmsPageUrl(p.slug, loc),
    }));

    const links = [
        { key: "navBar.Home", href: safeRoute("home", "/") },
        { key: "navBar.Buy Real Estate", href: safeRoute("property.index", "/property") },
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
        key: "about_us.title",
        href: safeRoute("about-us", "/about-us"),
    });
    links.push({
        key: "navBar.Contact us",
        href: safeRoute("support.contact-us", "/contact-us"),
    });

    return links;
});

function syncDocumentTextDirection() {
    if (!isBrowser()) {
        return;
    }

    const locale = activeLocale.value;
    const dir = page.props.text_direction || (locale === "ar" ? "rtl" : "ltr");
    document.documentElement.setAttribute("lang", String(locale));
    document.documentElement.setAttribute("dir", String(dir));
}

onMounted(() => {
    syncDocumentTextDirection();
});

watch(
    () => [activeLocale.value, page.props.text_direction],
    () => syncDocumentTextDirection(),
    { immediate: false },
);

watch(
    () => page.props.ziggy,
    (ziggy) => syncZiggy(ziggy),
    { immediate: true, deep: true },
);
</script>
