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
    </div>
</template>

<script setup>
import { computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import UserNavbar from "@/Layouts/Findhouses/UserNavbar.vue";
import UserTopBar from "@/Layouts/Findhouses/UserTopBar.vue";
import UserFooter from "@/Layouts/Findhouses/UserFooter.vue";

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

const navLinks = computed(() => [
    { key: "Home", href: safeRoute("home", "/") },
    { key: "Buy Real Estate", href: safeRoute("property.index") },
    {
        key: "Turkish Citizenship",
        href: safeRoute("turkish-citizenship", "/turkish-citizenship"),
    },
    { key: "Contact us", href: "/contact-us" },
    {
        key: "Pages",
        children: [
            { key: "News & Laws", href: "/news-laws" },
            { key: "Property Management", href: "/property-management" },
            { key: "About Turkey", href: "/about-turkey" },
            { key: "Services", href: "/services" },
        ],
    },
    // { key: "About us", href: "/about-us" },
]);

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
