<template>
    <div>
        <h3 class="mb-4 text-start">{{ trans("contact_us.title") }}</h3>

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
                class="btn btn-primary btn-lg "
                :disabled="form.processing"
            >
                {{ trans("contact_us.submit") }}
            </button>
           </div>
        </form>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";

const props = defineProps({
    contactStoreUrl: {
        type: String,
        required: true,
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
    subject: "",
    message: "",
});

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
            form.reset();
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
</style>
