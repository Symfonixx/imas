<template>
    <Head :title="documentTitle" />

    <AppLayout>
        <div ref="pageRef" class="inner-pages imas-contact-page">
            <InnerPageHeadingHero
                :page-title="trans('contact_us.title')"
                :items="blogHeadingItems"
                :banner-image-url="contactUsBannerUrl"
            />
            <section class="contact-us imas-contact-page__section">
                <div class="container">
                    <div class="row g-4">
                        <div class="col-lg-8 col-md-12">
                            <div class="imas-contact-page__panel imas-contact-page__panel--form">
                                <ContactForm
                                    :contact-store-url="contactStoreUrl"
                                />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <div
                                class="imas-contact-page__panel imas-contact-page__panel--details"
                            >
                                <ContactDetails />
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import InnerPageHeadingHero from "@/components/global/InnerPageHeadingHero.vue";
import { useScrollReveal } from "@/composables/useScrollReveal";
import ContactForm from "../Components/ContactForm.vue";
import ContactDetails from "../Components/ContactDetails.vue";

defineProps({
    contactStoreUrl: {
        type: String,
        required: true,
    },
});

const page = usePage();
const pageRef = ref(null);

useScrollReveal(pageRef, { variant: "propertyListings" });

const globals = computed(() => page.props.globals ?? {});
const media = computed(() => globals.value.media ?? {});

const contactUsBannerUrl = computed(() => {
    const url = media.value.contact_us_banner;
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

const documentTitle = computed(
    () => `${trans("contact_us.title")} | ${page.props.appName}`,
);

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
        title: trans("navBar.Contact us"),
        href: null,
    });
    return rows;
});
</script>
