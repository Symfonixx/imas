<template>
    <div>
        <h3
            v-if="!hideTitle"
            class="imas-contact-page__heading text-xl font-semibold mb-4 text-start"
        >
            {{ trans("contact_us.title") }}
        </h3>

        <Transition name="imas-contact-alert">
            <div
                v-if="successVisible"
                class="alert alert-success imas-contact-page__alert imas-contact-page__alert--success text-start"
                role="status"
            >
                {{ trans("contact_us.message_sent") }}
            </div>
        </Transition>

        <form
            ref="contactFormEl"
            class="contact-form imas-contact-form"
            :class="{ 'imas-contact-form--sidebar': variant === 'sidebar' }"
            @submit.prevent="submit"
        >
            <div class="imas-contact-form__pair">
                <div class="imas-contact-form__pair-field">
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
                </div>
                <div class="imas-contact-form__pair-field">
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
                </div>
            </div>

            <div
                :class="[
                    pairRowClass,
                    isPairedLayout && 'imas-contact-form__pair--stack-sm',
                ]"
            >
                <div :class="pairColClass">
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
                </div>
                <div :class="pairColClass">
                    <div class="form-group">
                        <PhoneCountryInput
                            v-model="form.mobile"
                            input-id="imas-contact-mobile"
                            :placeholder="
                                trans('auth_modal.mobile_national_placeholder')
                            "
                            :invalid="!!form.errors.mobile"
                        />
                        <div
                            v-if="form.errors.mobile"
                            class="invalid-feedback d-block"
                        >
                            {{ form.errors.mobile }}
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="!hideSubject" class="form-group">
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
                    :rows="messageRows"
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
                class="btn btn-primary imas-contact-page__submit"
                :class="
                    variant === 'sidebar'
                        ? 'multiple-send-message w-100'
                        : 'btn-lg'
                "
                :disabled="form.processing"
            >
                {{
                    variant === "sidebar"
                        ? trans("property_show.connect_with_us_today")
                        : trans("contact_us.submit")
                }}
            </button>
           </div>
        </form>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import PhoneCountryInput from "@/components/Global/PhoneCountryInput.vue";

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
    hideSubject: {
        type: Boolean,
        default: false,
    },
    defaultMessage: {
        type: String,
        default: "",
    },
});

const page = usePage();
const contactFormEl = ref(null);

const flash = computed(() => page.props.flash ?? {});

const successVisible = ref(false);
let successHideTimer = null;

function clearSuccessTimers() {
    if (successHideTimer) {
        clearTimeout(successHideTimer);
        successHideTimer = null;
    }
}

function showSuccessAlert() {
    clearSuccessTimers();
    successVisible.value = true;
    successHideTimer = setTimeout(() => {
        successVisible.value = false;
    }, 8000);
}

watch(
    () => flash.value?.contact_sent,
    (sent) => {
        if (sent) {
            showSuccessAlert();
        }
    },
    { immediate: true },
);

onBeforeUnmount(clearSuccessTimers);

const isPairedLayout = computed(() => props.variant !== "sidebar");
const messageRows = computed(() => (props.variant === "sidebar" ? 3 : 8));
const pairRowClass = computed(() =>
    isPairedLayout.value ? "imas-contact-form__pair" : null,
);
const pairColClass = computed(() =>
    isPairedLayout.value ? "imas-contact-form__pair-field" : null,
);

const form = useForm({
    first_name: "",
    last_name: "",
    email: "",
    mobile: "",
    subject: props.defaultSubject ?? "",
    message: props.defaultMessage ?? "",
});

function applyDefaultSubject(value) {
    if (typeof value !== "string" || value.trim() === "") {
        return;
    }
    if (props.hideSubject || !form.subject) {
        form.subject = value;
    }
}

watch(() => props.defaultSubject, applyDefaultSubject, { immediate: true });

function applyDefaultMessage(value) {
    if (typeof value !== "string" || value.trim() === "") {
        return;
    }
    if (!form.message) {
        form.message = value;
    }
}

watch(() => props.defaultMessage, applyDefaultMessage, { immediate: true });

watch(
    () => props.hideSubject,
    (hidden) => {
        if (hidden) {
            applyDefaultSubject(props.defaultSubject);
        }
    },
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
            const message = props.defaultMessage ?? "";
            form.reset();
            form.subject = subject;
            form.message = message;
            form.clearErrors();
            showSuccessAlert();
        },
    });
}
</script>

<style scoped>
.imas-contact-alert-enter-active,
.imas-contact-alert-leave-active {
    transition:
        opacity 0.4s ease,
        transform 0.4s ease;
}

.imas-contact-alert-enter-from,
.imas-contact-alert-leave-to {
    opacity: 0;
    transform: translateY(-0.5rem);
}

.imas-contact-form--sidebar .imas-contact-form__pair {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 0.375rem;
    row-gap: 0;
    margin-bottom: 0;
}

.imas-contact-form--sidebar .imas-contact-form__pair-field {
    min-width: 0;
}

.imas-contact-form--sidebar :deep(.form-group) {
    margin-bottom: 0;
}

.imas-contact-form--sidebar :deep(.form-control),
.imas-contact-form--sidebar :deep(.textarea-custom) {
    width: 100%;
    margin-bottom: 12px;
    border-radius: 6px;
    font-size: var(--text-sm);
}

.imas-contact-form--sidebar :deep(input.form-control) {
    height: 48px !important;
    min-height: 48px !important;
    padding: 0 0.85rem;
    line-height: 1.5;
    box-sizing: border-box;
    border: 1px red solid;
}

.imas-contact-form--sidebar :deep(textarea.form-control) {
    padding: 10px 12px;
    height: auto !important;
    min-height: 0;
    resize: vertical;
}

.imas-contact-form--sidebar :deep(.imas-auth-phone-field) {
    height: 48px !important;
    min-height: 48px !important;
    margin-bottom: 12px;
}

input{
    height: 48px !important;
}
</style>
