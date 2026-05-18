<template>
    <div class="widget-boxed mt-33 mt-5 imas-property-show-contact">
        <div class="widget-boxed-header">
            <h4 class="text-start">{{ trans("property_show.contact_info") }}</h4>
        </div>
        <div class="widget-boxed-body">
            <div class="sidebar-widget author-widget2">
                <ul v-if="contactItems.length" class="author__contact imas-contact-list">
                    <li
                        v-for="item in contactItems"
                        :key="item.key"
                        class="imas-contact-list__item"
                        :class="{ 'imas-contact-list__item--rtl': isRtl }"
                    >
                        <template v-if="isRtl">
                            <span class="imas-contact-list__label">
                                <a
                                    v-if="item.href"
                                    class="imas-contact-list__link"
                                    :href="item.href"
                                    >{{ item.text }}</a
                                >
                                <template v-else>{{ item.text }}</template>
                            </span>
                            <span
                                class="imas-contact-list__icon la"
                                :class="item.iconClass"
                            >
                                <i
                                    :class="['fa', item.icon]"
                                    aria-hidden="true"
                                ></i>
                            </span>
                        </template>
                        <template v-else>
                            <span
                                class="imas-contact-list__icon la"
                                :class="item.iconClass"
                            >
                                <i
                                    :class="['fa', item.icon]"
                                    aria-hidden="true"
                                ></i>
                            </span>
                            <span class="imas-contact-list__label">
                                <a
                                    v-if="item.href"
                                    class="imas-contact-list__link"
                                    :href="item.href"
                                    >{{ item.text }}</a
                                >
                                <template v-else>{{ item.text }}</template>
                            </span>
                        </template>
                    </li>
                </ul>
                <div
                    v-if="showSuccessFlash"
                    class="alert alert-success text-start"
                    role="status"
                >
                    {{ trans("contact_us.message_sent") }}
                </div>
                <div class="agent-contact-form-sidebar">
                    <h4 class="text-start">
                        {{ trans("property_show.request_inquiry") }}
                    </h4>
                    <ContactForm
                        :contact-store-url="contactStoreUrl"
                        variant="sidebar"
                        hide-title
                        :default-subject="defaultSubject"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import ContactForm from "../../../../../Support/resources/assets/js/Components/ContactForm.vue";

defineProps({
    contactStoreUrl: { type: String, required: true },
    defaultSubject: { type: String, default: "" },
});

const page = usePage();

const isRtl = computed(() => {
    const dir = page.props.text_direction;
    if (dir === "rtl" || dir === "ltr") {
        return dir === "rtl";
    }
    return (page.props.locale || "en") === "ar";
});

const showSuccessFlash = computed(() =>
    Boolean((page.props.flash ?? {}).contact_sent),
);

const globals = computed(() => page.props.globals ?? {});
const contact = computed(() => globals.value.contact ?? {});

const phoneTel = computed(() => {
    const p = contact.value.phone;
    if (typeof p !== "string") {
        return "";
    }
    return p.replace(/[^\d+]/g, "");
});

const contactItems = computed(() => {
    const c = contact.value;
    const items = [];

    if (c.address) {
        items.push({
            key: "address",
            icon: "fa-map-marker",
            iconClass: "la-map-marker",
            text: c.address,
            href: null,
        });
    }

    if (c.phone) {
        items.push({
            key: "phone",
            icon: "fa-phone",
            iconClass: "la-phone",
            text: c.phone,
            href: phoneTel.value ? `tel:${phoneTel.value}` : null,
        });
    }

    if (c.email) {
        items.push({
            key: "email",
            icon: "fa-envelope",
            iconClass: "la-envelope-o",
            text: c.email,
            href: `mailto:${c.email}`,
        });
    }

    return items;
});

function trans(key) {
    return page.props.translations[key] || key;
}
</script>

<style scoped>
.imas-property-show-contact .imas-contact-list {
    list-style: none;
    padding: 0;
    margin: 0 0 1.25rem;
}

.imas-property-show-contact .imas-contact-list__item {
    display: flex !important;
    flex-direction: row;
    align-items: flex-start;
    gap: 0.625rem;
    margin-bottom: 0.75rem;
    color: #666;
}

.imas-property-show-contact .imas-contact-list__item--rtl {
    flex-direction: row;
    justify-content: flex-end;
    direction: ltr;
}

.imas-property-show-contact .imas-contact-list__label {
    flex: 1;
    min-width: 0;
    text-align: start;
}

.imas-property-show-contact .imas-contact-list__item--rtl .imas-contact-list__label {
    flex: 0 1 auto;
    text-align: end;
}

.imas-property-show-contact .imas-contact-list__icon {
    flex-shrink: 0;
}

.imas-property-show-contact .imas-contact-list__link {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s ease;
}

.imas-property-show-contact .imas-contact-list__link:hover {
    color: var(--brand-gold);
}

.imas-property-show-contact :deep(.imas-contact-list__icon i) {
    margin-right: 0 !important;
    margin-left: 0 !important;
}
</style>
