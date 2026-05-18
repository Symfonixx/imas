<template>
    <div>
        <h3 v-if="!hideTitle" class="mb-4 text-start">
            {{ trans("contact_us.title") }}
        </h3>

        <div
            v-if="showSuccessFlash"
            class="alert alert-success"
            role="status"
        >
            {{ trans("contact_us.message_sent") }}
        </div>

        <form
            ref="contactFormEl"
            class="contact-form imas-contact-form"
            :class="{ 'imas-contact-form--sidebar': variant === 'sidebar' }"
            @submit.prevent="submit"
        >
            <div class="form-group">
                <input
                    v-model="form.first_name"
                    type="text"
                    required
                    maxlength="120"
                    class="form-control input-custom input-full"
                    :class="{ 'is-invalid': form.errors.first_name }"
                    :placeholder="trans('contact_us.first_name')"
                    autocomplete="given-name"
                />
                <div
                    v-if="form.errors.first_name"
                    class="invalid-feedback d-block"
                >
                    {{ form.errors.first_name }}
                </div>
            </div>
            <div class="form-group">
                <input
                    v-model="form.last_name"
                    type="text"
                    required
                    maxlength="120"
                    class="form-control input-custom input-full"
                    :class="{ 'is-invalid': form.errors.last_name }"
                    :placeholder="trans('contact_us.last_name')"
                    autocomplete="family-name"
                />
                <div
                    v-if="form.errors.last_name"
                    class="invalid-feedback d-block"
                >
                    {{ form.errors.last_name }}
                </div>
            </div>
            <div class="form-group">
                <input
                    v-model="form.email"
                    type="email"
                    required
                    maxlength="255"
                    class="form-control input-custom input-full"
                    :class="{ 'is-invalid': form.errors.email }"
                    :placeholder="trans('contact_us.email')"
                    autocomplete="email"
                />
                <div
                    v-if="form.errors.email"
                    class="invalid-feedback d-block"
                >
                    {{ form.errors.email }}
                </div>
            </div>
            <div class="form-group">
                <input
                    v-model="form.mobile"
                    type="text"
                    class="form-control input-custom input-full"
                    :class="{ 'is-invalid': form.errors.mobile }"
                    :placeholder="trans('contact_us.phone_optional')"
                    autocomplete="tel"
                />
                <div
                    v-if="form.errors.mobile"
                    class="invalid-feedback d-block"
                >
                    {{ form.errors.mobile }}
                </div>
            </div>
            <div class="form-group">
                <input
                    v-model="form.subject"
                    type="text"
                    class="form-control input-custom input-full"
                    :class="{ 'is-invalid': form.errors.subject }"
                    :placeholder="trans('contact_us.subject_optional')"
                />
                <div
                    v-if="form.errors.subject"
                    class="invalid-feedback d-block"
                >
                    {{ form.errors.subject }}
                </div>
            </div>
            <div class="form-group">
                <textarea
                    v-model="form.message"
                    class="form-control textarea-custom input-full"
                    rows="8"
                    required
                    maxlength="5000"
                    :class="{ 'is-invalid': form.errors.message }"
                    :placeholder="trans('contact_us.message')"
                />
                <div
                    v-if="form.errors.message"
                    class="invalid-feedback d-block"
                >
                    {{ form.errors.message }}
                </div>
            </div>
           <div class="d-flex justify-content-start">
            <button
                type="submit"
                class="btn btn-primary"
                :class="
                    variant === 'sidebar'
                        ? 'multiple-send-message w-100'
                        : 'btn-lg'
                "
                :disabled="form.processing"
            >
                {{
                    variant === "sidebar"
                        ? trans("property_show.request_inquiry")
                        : trans("contact_us.submit")
                }}
            </button>
           </div>
        </form>
    </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";

const props = defineProps({
    contactStoreUrl: {
        type: String,
        required: true,
    },
    hideTitle: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: "page",
    },
    defaultSubject: {
        type: String,
        default: "",
    },
});

const page = usePage();
const contactFormEl = ref(null);

const flash = computed(() => page.props.flash ?? {});
const showSuccessFlash = computed(() => Boolean(flash.value?.contact_sent));

const form = useForm({
    first_name: "",
    last_name: "",
    email: "",
    mobile: "",
    subject: props.defaultSubject ?? "",
    message: "",
});

watch(
    () => props.defaultSubject,
    (value) => {
        if (typeof value === "string" && value.trim() !== "" && !form.subject) {
            form.subject = value;
        }
    },
    { immediate: true },
);

function trans(key) {
    return page.props.translations[key] || key;
}

function submit() {
    const el = contactFormEl.value;
    if (el && typeof el.checkValidity === "function" && !el.checkValidity()) {
        el.reportValidity();
        return;
    }
    const url = props.contactStoreUrl?.trim();
    if (!url) {
        return;
    }
    form.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            const subject = props.defaultSubject ?? "";
            form.reset();
            form.subject = subject;
            form.clearErrors();
        },
    });
}
</script>

<style scoped>
.imas-contact-form :deep(.form-control) {
    border-color: #dddddd !important;
}

.imas-contact-form :deep(.form-control:focus) {
    border-color: #dddddd !important;
    box-shadow: 0 0 0 0.2rem rgba(221, 221, 221, 0.35);
}

.imas-contact-form :deep(.form-control.is-invalid) {
    border-color: #dc3545 !important;
}

.imas-contact-form :deep(.form-control.is-invalid:focus) {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.imas-contact-form--sidebar :deep(.form-group) {
    margin-bottom: 0;
}

.imas-contact-form--sidebar :deep(.form-control),
.imas-contact-form--sidebar :deep(.textarea-custom) {
    width: 100%;
    margin-bottom: 12px;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    padding: 10px 12px;
    font-size: 14px;
}

.imas-contact-form--sidebar :deep(textarea.form-control) {
    min-height: 120px;
    resize: vertical;
}

.imas-contact-form--sidebar .multiple-send-message {
    background: var(--brand-gold) !important;
    border-color: var(--brand-gold) !important;
    color: #fff !important;
    font-weight: 600;
}

.imas-contact-form--sidebar .multiple-send-message:hover:not(:disabled),
.imas-contact-form--sidebar .multiple-send-message:focus:not(:disabled),
.imas-contact-form--sidebar .multiple-send-message:active:not(:disabled) {
    background: var(--brand-gold-hover) !important;
    border-color: var(--brand-gold-hover) !important;
    color: #fff !important;
}
</style>
