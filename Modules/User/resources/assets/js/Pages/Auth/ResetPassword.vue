<template>
    <Head :title="`${trans('Reset Password')} | ${appName}`">
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <app-layout>
        <div class="container mt-5 mb-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card imas-auth-page-card">
                        <div class="card-header text-center">
                            <h3 class="text-md font-semibold mb-0">
                                {{ trans("Reset Password") }}
                            </h3>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-sm text-dim mb-3">
                                {{
                                    trans(
                                        "auth_modal.reset_page_opening",
                                    )
                                }}
                            </p>
                            <button
                                type="button"
                                class="btn btn-primary"
                                @click="openResetModal"
                            >
                                {{ trans("Reset Password") }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/App.vue";
import { useOpenAuthModal } from "@/composables/useOpenAuthModal";

defineProps({
    email: {
        type: String,
        default: "",
    },
    token: {
        type: String,
        default: "",
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const { openAuthModal } = useOpenAuthModal();

const appName = computed(() => page.props.appName);
const trans = (key) => page.props.translations?.[key] || key;

function openResetModal() {
    openAuthModal("reset");
}

onMounted(() => {
    // Navbar may still be mounting listeners on first paint; path-based open
    // in UserNavbar is the primary path. Retry shortly for Inertia revisits.
    openResetModal();
    window.setTimeout(openResetModal, 50);
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
