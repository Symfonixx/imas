<template>
    <div
        class="imas-blog-v2-sidebar__box imas-property-show-contact mt-33"
    >
        <div class="imas-contact-sidebar-header">
            <h4 class="imas-blog-v2-sidebar__heading text-start mb-0">
                {{ trans("navBar.Contact us") }}
            </h4>
            <div class="imas-contact-sidebar-actions">
                <button
                    v-if="canToggleFavorite"
                    type="button"
                    class="imas-contact-favorite__toggle"
                    :class="{ 'is-favorited': localFavorited }"
                    :aria-label="favoriteAriaLabel"
                    :aria-pressed="localFavorited"
                    @click="onFavoriteClick"
                >
                    <i
                        class="fa favorite-icon"
                        :class="localFavorited ? 'fa-heart' : 'fa-heart-o'"
                        aria-hidden="true"
                    ></i>
                </button>
                <div ref="shareMenuRef" class="imas-contact-share">
                    <button
                        type="button"
                        class="imas-contact-share__toggle"
                        :aria-label="trans('property_show.share_page')"
                        :aria-expanded="shareOpen ? 'true' : 'false'"
                        aria-haspopup="true"
                        @click.stop="toggleShareMenu"
                    >
                        <i class="fa fa-share-alt" aria-hidden="true"></i>
                    </button>
                    <div
                        v-show="shareOpen"
                        class="imas-contact-share__menu"
                        role="menu"
                    >
                    <a
                        v-for="item in shareLinks"
                        :key="item.key"
                        :href="item.href"
                        class="imas-contact-share__item"
                        role="menuitem"
                        target="_blank"
                        rel="noopener noreferrer"
                        @click="onShareLinkClick"
                    >
                        <i :class="item.icon" aria-hidden="true"></i>
                        <span>{{ item.label }}</span>
                    </a>
                    <button
                        type="button"
                        class="imas-contact-share__item imas-contact-share__item--button"
                        role="menuitem"
                        @click="copyPageLink"
                    >
                        <i class="fa fa-link" aria-hidden="true"></i>
                        <span>{{
                            linkCopied
                                ? trans("property_show.link_copied")
                                : trans("property_show.copy_link")
                        }}</span>
                    </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="imas-property-show-contact__body">
            <div class="sidebar-widget author-widget2">
                <ul
                    v-if="contactItems.length"
                    class="author__contact imas-contact-list"
                >
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
                                    :target="
                                        item.external ? '_blank' : undefined
                                    "
                                    :rel="
                                        item.external
                                            ? 'noopener noreferrer'
                                            : undefined
                                    "
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
                                    :target="
                                        item.external ? '_blank' : undefined
                                    "
                                    :rel="
                                        item.external
                                            ? 'noopener noreferrer'
                                            : undefined
                                    "
                                    >{{ item.text }}</a
                                >
                                <template v-else>{{ item.text }}</template>
                            </span>
                        </template>
                    </li>
                </ul>
                <div
                    class="agent-contact-form-sidebar imas-property-show-contact__form"
                >
                    <h4
                        class="imas-property-show-contact__form-title text-start"
                    >
                        {{ trans("property_show.request_inquiry") }}
                    </h4>
                    <ContactForm
                        :contact-store-url="contactStoreUrl"
                        variant="sidebar"
                        hide-title
                        :hide-subject="hideFormSubject"
                        :default-subject="effectiveDefaultSubject"
                        :default-message="defaultMessage"
                        :source-page="effectiveSourcePage"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from "axios";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useOpenAuthModal } from "@/composables/useOpenAuthModal";
import ContactForm from "../../../../../Support/resources/assets/js/Components/ContactForm.vue";
import {
    formatTurkishPhone,
    normalizeTurkishPhoneDigits,
} from "@/utils/turkishPhone.js";
import { resolveWhatsAppContactHref } from "@/utils/whatsappUrl.js";

const props = defineProps({
    contactStoreUrl: { type: String, required: true },
    defaultSubject: { type: String, default: "" },
    defaultMessage: { type: String, default: "" },
    sourcePage: { type: String, default: "" },
    hideFormSubject: { type: Boolean, default: false },
    propertyId: { type: Number, default: null },
    isFavorited: { type: Boolean, default: false },
    isSoldOut: { type: Boolean, default: false },
});

const canToggleFavorite = computed(
    () => props.propertyId != null && !props.isSoldOut,
);

const effectiveDefaultSubject = computed(() => {
    if (!props.hideFormSubject) {
        return props.defaultSubject;
    }
    return shareUrl.value || props.defaultSubject;
});

const effectiveSourcePage = computed(() => {
    if (props.sourcePage) {
        return props.sourcePage;
    }
    if (!props.hideFormSubject) {
        return props.defaultSubject;
    }
    return "";
});

const page = usePage();
const { openAuthModal } = useOpenAuthModal();

const isAuthenticated = computed(() => page.props.auth != null);

const localFavorited = ref(Boolean(props.isFavorited));

watch(
    () => props.isFavorited,
    (v) => {
        localFavorited.value = Boolean(v);
    },
);

const favoriteAriaLabel = computed(() =>
    localFavorited.value
        ? trans("properties.remove_favorite")
        : trans("properties.add_favorite"),
);

const shareMenuRef = ref(null);
const shareOpen = ref(false);
const linkCopied = ref(false);
const shareUrl = ref("");

const isRtl = computed(() => {
    const dir = page.props.text_direction;
    if (dir === "rtl" || dir === "ltr") {
        return dir === "rtl";
    }
    return (page.props.locale || "en") === "ar";
});

const globals = computed(() => page.props.globals ?? {});
const settings = computed(() => page.props.settings ?? {});
const contact = computed(() => globals.value.contact ?? {});

const rawPhone = computed(() => String(contact.value.phone ?? "").trim());

const phoneDisplay = computed(
    () => formatTurkishPhone(rawPhone.value) || rawPhone.value,
);

const phoneHref = computed(() => {
    const raw = rawPhone.value;
    if (!raw) {
        return "";
    }

    const social = globals.value.social ?? {};
    const normalized = normalizeTurkishPhoneDigits(raw);
    const phoneForWhatsApp = normalized ? `+${normalized}` : raw;

    return resolveWhatsAppContactHref({
        whatsapp: social.whatsapp || settings.value.whatsapp,
        phone: phoneForWhatsApp,
    });
});

const contactItems = computed(() => {
    const c = contact.value;
    const items = [];

    // if (c.address) {
    //     items.push({
    //         key: "address",
    //         icon: "fa-map-marker",
    //         iconClass: "la-map-marker",
    //         text: c.address,
    //         href: null,
    //     });
    // }

    if (phoneDisplay.value) {
        items.push({
            key: "phone",
            icon: "fa-phone",
            iconClass: "la-phone",
            text: phoneDisplay.value,
            href: phoneHref.value || null,
            external: Boolean(phoneHref.value),
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

const shareLinks = computed(() => {
    const url = shareUrl.value;
    if (!url) {
        return [];
    }
    const encoded = encodeURIComponent(url);
    const title = encodeURIComponent(
        typeof document !== "undefined" ? document.title : "",
    );

    return [
        {
            key: "facebook",
            label: "Facebook",
            icon: "fa fa-facebook",
            href: `https://www.facebook.com/sharer/sharer.php?u=${encoded}`,
        },
        {
            key: "twitter",
            label: "X (Twitter)",
            icon: "fa fa-twitter",
            href: `https://twitter.com/intent/tweet?url=${encoded}&text=${title}`,
        },
        {
            key: "linkedin",
            label: "LinkedIn",
            icon: "fa fa-linkedin",
            href: `https://www.linkedin.com/sharing/share-offsite/?url=${encoded}`,
        },
        {
            key: "whatsapp",
            label: "WhatsApp",
            icon: "fa fa-whatsapp",
            href: `https://wa.me/?text=${encoded}`,
        },
        {
            key: "email",
            label: trans("property_show.share_email"),
            icon: "fa fa-envelope",
            href: `mailto:?subject=${title}&body=${encoded}`,
        },
    ];
});

function toggleShareMenu() {
    shareOpen.value = !shareOpen.value;
    if (!shareOpen.value) {
        linkCopied.value = false;
    }
}

function closeShareMenu() {
    shareOpen.value = false;
    linkCopied.value = false;
}

function onShareLinkClick() {
    closeShareMenu();
}

async function onFavoriteClick(e) {
    e.preventDefault();
    e.stopPropagation();

    if (props.isSoldOut || props.propertyId == null) {
        return;
    }

    if (!isAuthenticated.value) {
        openAuthModal("login");
        return;
    }

    const next = !localFavorited.value;
    const prev = localFavorited.value;
    localFavorited.value = next;

    try {
        const headers = {
            "X-CSRF-TOKEN": page.props.csrf,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        };
        if (next) {
            await axios.post(
                "/api/favorites",
                { property_id: props.propertyId },
                { headers },
            );
        } else {
            await axios.delete(`/api/favorites/${props.propertyId}`, {
                headers,
            });
        }
    } catch (err) {
        localFavorited.value = prev;
        const msg =
            (err.response?.data?.message &&
                String(err.response.data.message)) ||
            trans("properties.favorite_error");
        if (typeof window !== "undefined" && window.toastr) {
            window.toastr.error(msg);
        }
    }
}

async function copyPageLink() {
    const url = shareUrl.value;
    if (!url) {
        return;
    }

    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(url);
        } else {
            const input = document.createElement("textarea");
            input.value = url;
            input.setAttribute("readonly", "");
            input.style.position = "absolute";
            input.style.left = "-9999px";
            document.body.appendChild(input);
            input.select();
            document.execCommand("copy");
            document.body.removeChild(input);
        }
        linkCopied.value = true;
        window.setTimeout(() => {
            linkCopied.value = false;
        }, 2000);
    } catch {
        /* ignore */
    }
}

function onDocumentClick(event) {
    const root = shareMenuRef.value;
    if (!root || !(event.target instanceof Node)) {
        return;
    }
    if (!root.contains(event.target)) {
        closeShareMenu();
    }
}

onMounted(() => {
    if (typeof window !== "undefined") {
        shareUrl.value = window.location.href;
    }
    document.addEventListener("click", onDocumentClick);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", onDocumentClick);
});
</script>

<style scoped>
.imas-contact-sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.imas-contact-sidebar-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

.imas-contact-share {
    position: relative;
    flex-shrink: 0;
}

.imas-contact-favorite__toggle,
.imas-contact-share__toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    padding: 0;
    border: 1px solid var(--border);
    border-radius: 50%;
    background: var(--surface-2);
    color: var(--text);
    cursor: pointer;
    transition:
        color 0.2s ease,
        border-color 0.2s ease,
        background-color 0.2s ease;
}

.imas-contact-favorite__toggle:hover,
.imas-contact-favorite__toggle:focus-visible,
.imas-contact-share__toggle:hover,
.imas-contact-share__toggle:focus-visible {
    color: var(--brand-gold);
    border-color: var(--brand-gold);
    background: var(--color-accent-soft);
    box-shadow: var(--ring);
}

.imas-contact-favorite__toggle:not(.is-favorited) i {
    color: var(--text);
}

.imas-contact-favorite__toggle.is-favorited i {
    color: var(--brand-gold);
}

.imas-contact-favorite__toggle:hover i,
.imas-contact-favorite__toggle:focus-visible i {
    color: var(--brand-gold);
}

.imas-contact-favorite__toggle.is-favorited:hover i,
.imas-contact-favorite__toggle.is-favorited:focus-visible i {
    color: var(--text-on-gold);
}

.imas-contact-share__menu {
    position: absolute;
    top: calc(100% + 0.5rem);
    inset-inline-end: 0;
    z-index: 20;
    min-width: 11.5rem;
    padding: 0.35rem 0;
    border-radius: 8px;
    background: var(--surface);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border);
}

.imas-contact-share__item {
    display: flex;
    align-items: center;

    gap: 0.625rem;
    width: 100%;
    padding: 0.5rem 0.875rem;
    border: 0;
    background: transparent;
    color: var(--text);
    font-size: var(--text-sm);
    text-align: start;
    text-decoration: none;
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        color 0.2s ease;
}

.imas-contact-share__item--button {
    font-family: inherit;
}

.imas-contact-share__item:hover,
.imas-contact-share__item:focus-visible {
    background: var(--color-accent-soft);
    color: var(--brand-gold);
}

.imas-contact-share__item i {
    width: 1rem;
    text-align: center;
    color: var(--brand-gold);
}

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
    color: var(--text-dim);
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
    margin-top: 3px;
}

.imas-property-show-contact
    .imas-contact-list__item--rtl
    .imas-contact-list__label {
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

.imas-property-show-contact__body {
    padding-top: 0;
}

.imas-property-show-contact .imas-contact-sidebar-header {
    margin-bottom: 14px;
}

.imas-property-show-contact .imas-blog-v2-sidebar__heading {
    margin-bottom: 0;
}

.imas-property-show-contact__form {
    margin-top: 1.25rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--divider);
}

.imas-property-show-contact__form-title {
    font-size: var(--text-md);
    font-weight: 600;
    color: var(--text);
    margin-bottom: 1rem;
}
.imas-contact-list__icon.la-phone i{
    font-size: 18px !important;
}

</style>
