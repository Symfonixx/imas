<template>
    <Head :title="documentTitle">
        <meta
            v-if="metaDescription"
            head-key="description"
            name="description"
            :content="metaDescription"
        />
        <meta
            v-if="robots"
            head-key="robots"
            name="robots"
            :content="robots"
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
        <meta head-key="og:type" property="og:type" content="website" />
    </Head>

    <AppLayout>
        <section
            ref="pageRef"
            class="inner-pages notfound imas-notfound"
            aria-labelledby="imas-notfound-title"
        >
            <div class="container">
                <div class="imas-notfound__panel" data-imas-reveal="up">
                    <div class="imas-notfound__visual" aria-hidden="true">
                        <span class="imas-notfound__glow" />
                        <span class="imas-notfound__code">404</span>
                        <span class="imas-notfound__mark">
                            <i class="fa fa-map-marker-alt" />
                        </span>
                    </div>

                    <div class="top-headings text-center imas-notfound__copy">
                        <p class="imas-notfound__eyebrow text-xs font-semibold text-gold">
                            {{ trans("errors.not_found.eyebrow") }}
                        </p>
                        <h1
                            id="imas-notfound-title"
                            class="imas-notfound__title text-2xl font-bold"
                        >
                            {{ trans("errors.not_found.heading") }}
                        </h1>
                        <p class="imas-notfound__message text-base text-dim">
                            {{ trans("errors.not_found.message") }}
                        </p>
                    </div>

                    <div class="port-info imas-notfound__actions">
                        <Link
                            :href="homeUrl"
                            class="btn btn-primary btn-lg imas-notfound__btn imas-notfound__btn--primary"
                        >
                            {{ trans("errors.not_found.go_home") }}
                        </Link>
                        <Link
                            :href="propertiesUrl"
                            class="btn btn-lg imas-notfound__btn imas-notfound__btn--outline"
                        >
                            {{ trans("errors.not_found.browse_properties") }}
                        </Link>
                    </div>

                    <nav
                        class="imas-notfound__shortcuts"
                        :aria-label="trans('errors.not_found.shortcuts_label')"
                    >
                        <p class="imas-notfound__shortcuts-label text-sm text-muted">
                            {{ trans("errors.not_found.try_these") }}
                        </p>
                        <ul class="imas-notfound__shortcut-list">
                            <li v-for="item in shortcuts" :key="item.href">
                                <Link
                                    :href="item.href"
                                    class="imas-notfound__shortcut text-sm"
                                >
                                    {{ item.label }}
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import { useDocumentSeo } from "@/composables/useDocumentSeo.js";
import { useScrollReveal } from "@/composables/useScrollReveal";
import { localizedRoute } from "@/utils/localizedRoute.js";

const page = usePage();
const pageRef = ref(null);
useScrollReveal(pageRef, { variant: "sections" });

const activeLocale = computed(() => page.props.locale || "en");

function trans(key, fallback = key) {
    return page.props.translations?.[key] || fallback;
}

const homeUrl = computed(() =>
    localizedRoute("home", {}, activeLocale.value, "/"),
);
const propertiesUrl = computed(() =>
    localizedRoute("property.index", {}, activeLocale.value, "/property"),
);
const blogUrl = computed(() =>
    localizedRoute("blog.index", {}, activeLocale.value, "/blog"),
);
const contactUrl = computed(() =>
    localizedRoute(
        "support.contact-us",
        {},
        activeLocale.value,
        "/contact-us",
    ),
);

const shortcuts = computed(() => [
    {
        href: homeUrl.value,
        label: trans("errors.not_found.link_home", "Home"),
    },
    {
        href: propertiesUrl.value,
        label: trans(
            "errors.not_found.link_properties",
            "Buy Real Estate",
        ),
    },
    {
        href: blogUrl.value,
        label: trans("errors.not_found.link_blog", "Blog"),
    },
    {
        href: contactUrl.value,
        label: trans("errors.not_found.link_contact", "Contact us"),
    },
]);

const {
    title: documentTitle,
    description: metaDescription,
    ogTitle,
    ogDescription,
    robots,
} = useDocumentSeo({
    pageTitle: () => trans("errors.not_found.title", "Page not found"),
    description: () =>
        trans(
            "errors.not_found.message",
            "The page you are looking for could not be found.",
        ),
    robots: "noindex, nofollow",
});
</script>
