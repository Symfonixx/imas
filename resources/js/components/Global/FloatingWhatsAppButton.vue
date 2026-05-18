<template>
    <a
        v-if="whatsappHref"
        :href="whatsappHref"
        class="imas-floating-whatsapp"
        target="_blank"
        rel="noopener noreferrer"
        :aria-label="ariaLabel"
    >
        <i class="fa fa-whatsapp" aria-hidden="true"></i>
    </a>
</template>

<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { resolveWhatsAppContactHref } from "@/utils/whatsappUrl.js";

const page = usePage();

const globals = computed(() => page.props.globals ?? {});
const settings = computed(() => page.props.settings ?? {});

const whatsappHref = computed(() => {
    const social = globals.value.social ?? {};
    const contact = globals.value.contact ?? {};

    return resolveWhatsAppContactHref({
        whatsapp: social.whatsapp,
        phone:
            contact.phone ||
            settings.value.contact_phone ||
            settings.value.phone,
    });
});

function trans(key) {
    return page.props.translations?.[key] || key;
}

const ariaLabel = computed(
    () => trans("floating_whatsapp.aria_label") || "Contact us on WhatsApp",
);
</script>

<style scoped lang="scss">
.imas-floating-whatsapp {
    position: fixed;
    /* Above theme `.go-up` (bottom ~1.5rem) */
    bottom: 5.75rem;
    inset-inline-end: 1.25rem;
    z-index: var(--z-floating-whatsapp, 100090);
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #f4f5f7;
    color: #25d366;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    line-height: 1;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(26, 42, 74, 0.18);
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background-color 0.2s ease;
}

.imas-floating-whatsapp:hover,
.imas-floating-whatsapp:focus {
    color: #20bd5a;
    background: #fff;
    transform: scale(1.06);
    box-shadow: 0 6px 20px rgba(26, 42, 74, 0.22);
    text-decoration: none;
}

@media (max-width: 575.98px) {
    .imas-floating-whatsapp {
        bottom: 5.25rem;
        inset-inline-end: 1rem;
        width: 52px;
        height: 52px;
        font-size: 28px;
    }
}
</style>
