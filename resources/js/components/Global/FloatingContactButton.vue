<template>
    <div
        v-if="hasAnyChannel"
        class="imas-floating-contact"
        :class="{ 'imas-floating-contact--open': isOpen }"
    >
        <Transition name="imas-contact-menu">
            <div
                v-show="isOpen"
                id="imas-floating-contact-menu"
                class="imas-floating-contact__panel"
                role="dialog"
                :aria-label="menuAriaLabel"
            >
                <p class="imas-floating-contact__title text-md font-semibold">
                    {{ menuTitle }}
                </p>
                <ul class="imas-floating-contact__list">
                    <li v-if="whatsappHref">
                        <a
                            :href="whatsappHref"
                            class="imas-floating-contact__item"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <span
                                class="imas-floating-contact__icon imas-floating-contact__icon--whatsapp"
                                aria-hidden="true"
                            >
                                <i class="fa fa-whatsapp"></i>
                            </span>
                            <span class="imas-floating-contact__label text-sm font-medium">{{
                                labelWhatsApp
                            }}</span>
                        </a>
                    </li>
                    <!-- <li v-if="messengerHref">
                        <a
                            :href="messengerHref"
                            class="imas-floating-contact__item"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <span
                                class="imas-floating-contact__icon imas-floating-contact__icon--messenger"
                                aria-hidden="true"
                            >
                                <i class="fab fa-facebook-messenger"></i>
                            </span>
                            <span class="imas-floating-contact__label text-sm font-medium">{{
                                labelMessenger
                            }}</span>
                        </a>
                    </li> -->
                    <li v-if="gmailHref">
                        <a
                            :href="gmailHref"
                            class="imas-floating-contact__item"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <span
                                class="imas-floating-contact__icon imas-floating-contact__icon--gmail"
                                aria-hidden="true"
                            >
                                <i class="fab fa-google"></i>
                            </span>
                            <span class="imas-floating-contact__label text-sm font-medium">{{
                                labelGmail
                            }}</span>
                        </a>
                    </li>
                    <li v-if="phoneHref">
                        <a
                            :href="phoneHref"
                            class="imas-floating-contact__item"
                        >
                            <span
                                class="imas-floating-contact__icon imas-floating-contact__icon--phone"
                                aria-hidden="true"
                            >
                                <i class="fa fa-phone"></i>
                            </span>
                            <span class="imas-floating-contact__label text-sm font-medium">{{
                                labelDirectCall
                            }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </Transition>

        <button
            type="button"
            class="imas-floating-contact__toggle"
            :aria-expanded="isOpen"
            :aria-controls="isOpen ? 'imas-floating-contact-menu' : undefined"
            :aria-label="toggleAriaLabel"
            @click="toggle"
        >
            <i
                class="fa"
                :class="isOpen ? 'fa-times' : 'fa-comment'"
                aria-hidden="true"
            ></i>
        </button>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { buildGmailComposeUrl } from "@/utils/gmailUrl.js";
import { resolveWhatsAppContactHref } from "@/utils/whatsappUrl.js";

const MESSENGER_URL =
    "https://m.me/61584547460936";

const page = usePage();
const isOpen = ref(false);

const globals = computed(() => page.props.globals ?? {});
const settings = computed(() => page.props.settings ?? {});

function trans(key) {
    return page.props.translations?.[key] || key;
}

const contactPhone = computed(() => {
    const contact = globals.value.contact ?? {};
    return String(
        contact.phone ||
            settings.value.contact_phone ||
            settings.value.phone ||
            "",
    ).trim();
});

const contactEmail = computed(() => {
    const contact = globals.value.contact ?? {};
    return String(
        contact.email ||
            settings.value.contact_email ||
            settings.value.email ||
            "",
    ).trim();
});

const whatsappHref = computed(() => {
    const social = globals.value.social ?? {};

    return resolveWhatsAppContactHref({
        whatsapp: social.whatsapp,
        phone: contactPhone.value,
    });
});

const messengerHref = computed(() => MESSENGER_URL);

const gmailHref = computed(() => buildGmailComposeUrl(contactEmail.value));

const phoneHref = computed(() => {
    const raw = contactPhone.value;
    const digits = raw.replace(/[^\d+]/g, "");
    return digits ? `tel:${digits}` : "";
});

const hasAnyChannel = computed(
    () =>
        Boolean(
            whatsappHref.value ||
                messengerHref.value ||
                gmailHref.value ||
                phoneHref.value,
        ),
);

const menuTitle = computed(
    () =>
        trans("floating_contact.menu_title") ||
        "Talk to us on your favorite channel",
);

const labelMessenger = computed(
    () => trans("floating_contact.messenger") || "Messenger chat",
);

const labelWhatsApp = computed(
    () => trans("floating_whatsapp.aria_label") || "Contact us on WhatsApp",
);

const labelGmail = computed(
    () => trans("floating_contact.gmail") || "Gmail",
);

const labelDirectCall = computed(
    () => trans("floating_contact.direct_call") || "Direct call",
);

const menuAriaLabel = computed(
    () => trans("floating_contact.menu_aria") || "Contact channels",
);

const toggleAriaLabel = computed(() =>
    isOpen.value
        ? trans("floating_contact.aria_close") || "Close contact menu"
        : trans("floating_contact.aria_open") || "Open contact menu",
);

function toggle() {
    isOpen.value = !isOpen.value;
}

function onDocumentClick(event) {
    if (!isOpen.value) {
        return;
    }
    const root = event.target?.closest?.(".imas-floating-contact");
    if (!root) {
        isOpen.value = false;
    }
}

function onEscape(event) {
    if (event.key === "Escape" && isOpen.value) {
        isOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener("click", onDocumentClick);
    document.addEventListener("keydown", onEscape);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", onDocumentClick);
    document.removeEventListener("keydown", onEscape);
});
</script>

<style scoped lang="scss">
.imas-floating-contact {
    position: fixed;
    bottom: 5.75rem;
    inset-inline-start: 1.25rem;
    z-index: var(--z-floating-contact, 100090);
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.imas-floating-contact__toggle {
    width: 56px;
    height: 56px;
    border: 1px solid var(--border-strong);
    border-radius: 50%;
    background: var(--brand-gold);
    color: var(--text-on-gold);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    box-shadow: var(--shadow-md);
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.imas-floating-contact__toggle:hover,
.imas-floating-contact__toggle:focus-visible {
    background: var(--brand-gold-hover);
    border-color: var(--brand-gold-hover);
    transform: scale(1.06);
    box-shadow: var(--shadow-lg);
    outline: none;
}

.imas-floating-contact__toggle:focus-visible {
    box-shadow: var(--ring), var(--shadow-md);
}

.imas-floating-contact__panel {
    position: absolute;
    bottom: calc(100% + 0.75rem);
    inset-inline-start: 0;
    width: min(300px, calc(100vw - 2.5rem));
    padding: 1.15rem 1rem 0.95rem;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    box-shadow: var(--shadow-lg);
    text-align: start;
}

.imas-floating-contact__title {
    margin: 0 0 0.9rem;
    line-height: 1.35;
    color: var(--text);
}

.imas-floating-contact__list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.imas-floating-contact__item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 0.8rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--surface-2);
    text-decoration: none;
    color: var(--text);
    transition:
        border-color 0.2s ease,
        background-color 0.2s ease,
        color 0.2s ease,
        box-shadow 0.2s ease;
}

.imas-floating-contact__item:hover,
.imas-floating-contact__item:focus-visible {
    border-color: var(--border-strong);
    background: var(--surface-3);
    color: var(--text);
    text-decoration: none;
    outline: none;
    box-shadow: var(--ring);
}

.imas-floating-contact__icon {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--surface-3);
    border: 1px solid var(--border);
    font-size: 1.125rem;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        color 0.2s ease;
}

.imas-floating-contact__item:hover .imas-floating-contact__icon,
.imas-floating-contact__item:focus-visible .imas-floating-contact__icon {
    border-color: var(--border-strong);
    background: color-mix(in srgb, var(--brand-gold) 12%, var(--surface-3));
}

.imas-floating-contact__icon--messenger {
    color: var(--info);
}

.imas-floating-contact__icon--whatsapp {
    color: var(--success);
}

.imas-floating-contact__icon--gmail {
    color: var(--brand-gold);
}

.imas-floating-contact__icon--phone {
    color: var(--text-dim);
}

.imas-floating-contact__item:hover .imas-floating-contact__icon--phone,
.imas-floating-contact__item:focus-visible .imas-floating-contact__icon--phone {
    color: var(--brand-gold);
}

.imas-floating-contact__label {
    flex: 1;
    min-width: 0;
}

.imas-contact-menu-enter-active,
.imas-contact-menu-leave-active {
    transition:
        opacity 0.22s ease,
        transform 0.22s ease;
}

.imas-contact-menu-enter-from,
.imas-contact-menu-leave-to {
    opacity: 0;
    transform: translateY(10px);
}

.imas-contact-menu-enter-to,
.imas-contact-menu-leave-from {
    opacity: 1;
    transform: translateY(0);
}

@media (max-width: 575.98px) {
    .imas-floating-contact {
        bottom: 5.25rem;
        inset-inline-start: 1rem;
    }

    .imas-floating-contact__toggle {
        width: 52px;
        height: 52px;
        font-size: 20px;
    }

    .imas-floating-contact__panel {
        width: min(280px, calc(100vw - 2rem));
    }
}

@media (prefers-reduced-motion: reduce) {
    .imas-floating-contact__toggle,
    .imas-floating-contact__item,
    .imas-floating-contact__icon,
    .imas-contact-menu-enter-active,
    .imas-contact-menu-leave-active {
        transition: none;
    }

    .imas-floating-contact__toggle:hover,
    .imas-floating-contact__toggle:focus-visible {
        transform: none;
    }
}
</style>
