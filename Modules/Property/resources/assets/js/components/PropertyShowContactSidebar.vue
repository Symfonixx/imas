<template>
    <div class="widget-boxed mt-33 mt-5 imas-property-show-contact">
        <div class="widget-boxed-header">
            <h4 class="text-start">{{ trans("property_show.contact_info") }}</h4>
        </div>
        <div class="widget-boxed-body">
            <div class="sidebar-widget author-widget2">
                <ul v-if="hasContactRows" class="author__contact text-start">
                    <li v-if="contact.address">
                        <span class="la la-map-marker"
                            ><i class="fa fa-map-marker" aria-hidden="true"></i
                        ></span>
                        {{ contact.address }}
                    </li>
                    <li v-if="contact.phone">
                        <span class="la la-phone"
                            ><i class="fa fa-phone" aria-hidden="true"></i
                        ></span>
                        <a v-if="phoneTel" :href="'tel:' + phoneTel">{{
                            contact.phone
                        }}</a>
                        <template v-else>{{ contact.phone }}</template>
                    </li>
                    <li v-if="contact.email">
                        <span class="la la-envelope-o"
                            ><i class="fa fa-envelope" aria-hidden="true"></i
                        ></span>
                        <a :href="'mailto:' + contact.email">{{
                            contact.email
                        }}</a>
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

const hasContactRows = computed(
    () =>
        Boolean(contact.value.address) ||
        Boolean(contact.value.phone) ||
        Boolean(contact.value.email),
);

function trans(key) {
    return page.props.translations[key] || key;
}
</script>

<style scoped>
.imas-property-show-contact .author__contact {
    list-style: none;
    padding: 0;
    margin: 0 0 1.25rem;
}

.imas-property-show-contact .author__contact li {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    text-align: start;
}

html[dir="rtl"] .imas-property-show-contact .author__contact li {
    flex-direction: row-reverse;
    text-align: end;
}
</style>
