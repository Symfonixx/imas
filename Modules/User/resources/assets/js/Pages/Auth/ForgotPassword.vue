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
        <meta head-key="og:type" property="og:type" content="website" />
        <meta
            v-if="ogUrl"
            head-key="og:url"
            property="og:url"
            :content="ogUrl"
        />
        <meta
            v-if="robots"
            head-key="robots"
            name="robots"
            :content="robots"
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

    <app-layout>
        <div class="container mt-5 mb-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card imas-auth-page-card">
                        <div class="card-header text-center">
                            <h3 class="text-md font-semibold mb-0">
                                {{ trans("Forgot Password") }}
                            </h3>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-sm text-dim mb-3">
                                {{
                                    trans(
                                        "auth_modal.forgot_page_opening",
                                    )
                                }}
                            </p>
                            <button
                                type="button"
                                class="btn btn-primary"
                                @click="openForgotModal"
                            >
                                {{ trans("Forgot Password") }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script setup>
import { onMounted } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import { useOpenAuthModal } from "@/composables/useOpenAuthModal";
import { useDocumentSeo } from "@/composables/useDocumentSeo.js";

defineProps({
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const { openAuthModal } = useOpenAuthModal();

const trans = (key) => page.props.translations?.[key] || key;

const {
    title: documentTitle,
    description: metaDescription,
    keywords: metaKeywords,
    ogTitle,
    ogDescription,
    ogImage,
    canonical: canonicalUrl,
    ogUrl,
    twitterCard,
    robots,
} = useDocumentSeo({
    pageTitle: () => trans("Forgot Password"),
    robots: "noindex, nofollow",
    canonical: () => {
        try {
            if (typeof route === "function" && route().has?.("password.request")) {
                return route("password.request");
            }
        } catch {
            /* Ziggy may be unavailable */
        }
        return "";
    },
});

function openForgotModal() {
    openAuthModal("forgot");
}

onMounted(() => {
    openForgotModal();
    window.setTimeout(openForgotModal, 50);
});
</script>

<style scoped>
.imas-auth-page-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    color: var(--text);
}

.imas-auth-page-card .card-header {
    background: var(--surface-2);
    border-bottom: 1px solid var(--divider);
    color: var(--text);
}

.imas-auth-page-card .btn-primary {
    background: var(--brand-gold);
    border-color: var(--brand-gold);
    color: var(--text-on-gold);
    font-weight: 600;
}

.imas-auth-page-card .btn-primary:hover {
    background: var(--brand-gold-hover);
    border-color: var(--brand-gold-hover);
    color: var(--text-on-gold);
}
</style>
