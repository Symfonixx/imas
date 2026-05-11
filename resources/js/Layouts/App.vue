<template>
    <div id="wrapper">
        <UserNavbar :nav-links="navLinks" />
        <div class="clearfix"></div>
        <slot />
        <UserFooter :nav-links="navLinks" />
    </div>
</template>

<script setup>
import { computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import UserNavbar from "@/Layouts/Findhouses/UserNavbar.vue";
import UserFooter from "@/Layouts/Findhouses/UserFooter.vue";

const page = usePage();

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
