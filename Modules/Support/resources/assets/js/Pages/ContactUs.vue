<template>
    <Head :title="documentTitle" />

    <AppLayout>
        <div ref="pageRef" class="inner-pages imas-contact-page">
            <InnerPageHeadingHero
                :page-title="trans('contact_us.title')"
                :items="blogHeadingItems"
                :banner-image-url="contactUsBannerUrl"
            />
            <section class="contact-us">
                <div class="container">
                    <!-- <div v-if="mapEmbedHtml" class="property-location mb-5">
                        <h3>{{ trans("contact_us.our_location") }}</h3>
                        <div class="divider-fade"></div>
                        <div
                            class="contact-map imas-contact-map"
                            v-html="mapEmbedHtml"
                        />
                    </div>
                    <div v-else class="property-location mb-5">
                        <h3>{{ trans("contact_us.our_location") }}</h3>
                        <div class="divider-fade"></div>
                        <p class="text-muted mb-0">
                            {{ trans("contact_us.map_not_configured") }}
                        </p>
                    </div> -->

                    <div class="row">
                        <div class="col-lg-8 col-md-12">
                            <ContactForm :contact-store-url="contactStoreUrl" />
                        </div>
                        <div class="col-lg-4 col-md-12 ContactDetails py-4">
                            <ContactDetails />
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
const contact = computed(() => globals.value.contact ?? {});
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

const mapEmbedHtml = computed(() => {
    const raw = contact.value.map;
    if (typeof raw !== "string") {
        return "";
    }
    return raw.trim();
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
        title: trans("contact_us.title"),
        href: null,
    });
    return rows;
});
</script>

<style scoped>
.imas-contact-map :deep(iframe) {
    width: 100%;
    max-width: 100%;
    min-height: 360px;
    border: 0;
}
.contact-map {
    height: 365px !important;
}
.ContactDetails {
    border-radius: 5px;

    background-color: var(--brand-navy-light);
}
</style>
