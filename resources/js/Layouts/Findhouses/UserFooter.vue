<template>
    <footer class="first-footer rec-pro imas-blog-footer">
        <div class="top-footer">
            <div class="container imas-footer-wrap">
                <div class="row imas-footer-grid">
                    <div class="col-lg-3 col-md-6 f-col imas-footer-col--brand">
                        <div class="netabout">
                            <div class="brand-line">
                                <div class="logo">
                                    <img
                                        :src="logoUrl"
                                        alt="logo"
                                        class="footer_logo"
                                    />
                                </div>
                                <div class="imas-brand-text">
                                    <span class="website-name">{{
                                        websiteName
                                    }}</span>
                                    <span class="website-slogan">{{
                                        websiteSlogan
                                    }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="contactus text-start">
                            <ul>
                                <li class="contact-line">
                                    <div class="info">
                                        <span class="ic" aria-hidden="true"
                                            ><i class="fa fa-map-marker"></i
                                        ></span>
                                        <p class="in-p">
                                            {{
                                                settings.contact_address ||
                                                fallbackAddress
                                            }}
                                        </p>
                                    </div>
                                </li>
                                <li class="contact-line">
                                    <div class="info">
                                        <span class="ic" aria-hidden="true"
                                            ><i class="fa fa-phone"></i
                                        ></span>
                                        <p class="in-p in-p--phone" dir="ltr">
                                            <a
                                                v-if="phoneDisplay && phoneHref"
                                                :href="phoneHref"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                {{ phoneDisplay }}
                                            </a>
                                            <template v-else-if="phoneDisplay">{{
                                                phoneDisplay
                                            }}</template>
                                        </p>
                                    </div>
                                </li>
                                <li class="contact-line">
                                    <div class="info">
                                        <span class="ic" aria-hidden="true"
                                            ><i class="fa fa-envelope"></i
                                        ></span>
                                        <p class="in-p ti">
                                            {{
                                                settings.contact_email ||
                                                fallbackEmail
                                            }}
                                        </p>
                                    </div>
                                </li>
                                <li class="contact-line">
                                    <div class="info">
                                        <!-- <span class="ic" aria-hidden="true"
                                            ><i class="fa fa-map-marker"></i
                                        ></span> -->
                                        <p class="in-p">
                                            {{ trans("navBar.footer_location") }}
                                        </p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 f-col">
                        <div class="navigation text-start">
                            <h3>{{ trans("navBar.navigation") }}</h3>
                            <div class="nav-footer text-start">
                                <ul class="links">
                                    <li
                                        v-for="item in mainNavLinks"
                                        :key="item.key"
                                    >
                                        <Link :href="item.href">{{
                                            trans(item.key)
                                        }}</Link>
                                    </li>
                                </ul>
                                <!-- <ul class="nav-pages links">
                                    <li v-for="item in pagesNavLinks" :key="item.key">
                                        <Link :href="item.href">{{
                                            item.label ?? trans(item.key)
                                        }}</Link>
                                    </li>
                                </ul> -->
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 f-col">
                        <div class="navigation text-start">
                            <h3>{{ trans("navBar.useful_links") }}</h3>
                            <ul class="links links--single">
                                <li
                                    v-for="item in footerPagesLinks"
                                    :key="item.key"
                                >
                                    <Link :href="item.href">{{
                                        item.label
                                    }}</Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 f-col">
                        <div class="newsletters text-start">
                            <h3>{{ trans("navBar.newsLetters") }}</h3>
                            <p>{{ trans("navBar.signup_for_newsletters") }}</p>
                        </div>
                        <form
                            ref="newsletterFormEl"
                            class="bloq-email mailchimp form-inline newsletter"
                            @submit.prevent="submitNewsletter"
                        >
                            <div class="email">
                                <input
                                    id="subscribeEmail"
                                    v-model="subscribeForm.email"
                                    type="email"
                                    name="email"
                                    required
                                    maxlength="255"
                                    :placeholder="
                                        trans('navBar.enter_your_email')
                                    "
                                    :disabled="subscribeForm.processing"
                                    :class="{
                                        'is-invalid':
                                            subscribeForm.errors.email,
                                    }"
                                />
                                <button
                                    type="submit"
                                    :disabled="subscribeForm.processing"
                                >
                                    {{ trans("navBar.subscribe") }}
                                </button>
                            </div>
                            <p
                                v-if="subscribeForm.errors.email"
                                class="subscription-error"
                                role="alert"
                            >
                                {{ subscribeForm.errors.email }}
                            </p>
                            <p
                                v-if="showSubscriptionSuccess"
                                class="subscription-success"
                                role="status"
                            >
                                {{ trans("navBar.subscription_success") }}
                            </p>
                        </form>
                        <div
                            v-if="footerSocialLinks.length"
                            class="socials imas-footer-socials"
                            :aria-label="trans('Social media')"
                        >
                            <a
                                v-for="item in footerSocialLinks"
                                :key="item.key"
                                :href="item.href"
                                target="_blank"
                                rel="noopener noreferrer"
                                :aria-label="item.label"
                                ><i :class="item.icon" aria-hidden="true"></i
                            ></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="second-footer rec-pro copyright">
            <div class="container imas-footer-wrap imas-second-footer__inner">
                <nav
                    v-if="bottomBarPages.length"
                    class="imas-second-footer__bottom-bar"
                    :aria-label="trans('navBar.useful_links')"
                >
                    <template v-for="(p, index) in bottomBarPages" :key="p.id">
                        <span
                            v-if="index > 0"
                            class="imas-second-footer__separator"
                            aria-hidden="true"
                            >|</span
                        >
                        <Link
                            class="imas-second-footer__page-link"
                            :href="cmsPageUrl(p.slug, activeLocale)"
                        >
                            {{ p.title }}
                        </Link>
                    </template>
                </nav>
                <div
                    v-else
                    class="imas-second-footer__bottom-bar imas-second-footer__bottom-bar--empty"
                    aria-hidden="true"
                ></div>
                <p class="imas-second-footer__copy">
                    {{ year }} © {{ appName }} —
                    {{ trans("navBar.All Rights Reserved") }}
                </p>
                <p class="imas-second-footer__developer">
                    <span>{{ developedByPrefix }}</span>
                    <a
                        href="https://symfonix.io/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="imas-second-footer__developer-link"
                        >Symfonix</a
                    >
                </p>
            </div>
        </div>
    </footer>

    <a data-scroll href="#wrapper" class="go-up"
        ><i class="fa fa-angle-double-up" aria-hidden="true"></i
    ></a>
</template>

<script setup>
import { computed, onBeforeUnmount, ref } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { cmsPageUrl } from "@/utils/cmsPageUrl.js";
import {
    formatTurkishPhone,
    normalizeTurkishPhoneDigits,
} from "@/utils/turkishPhone.js";
import { resolveWhatsAppContactHref } from "@/utils/whatsappUrl.js";

const props = defineProps({
    navLinks: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const newsletterFormEl = ref(null);
const showSubscriptionSuccess = ref(false);
let subscriptionSuccessTimer = null;

const subscribeForm = useForm({
    email: "",
});

const subscribeStoreUrl = computed(() => {
    const url = page.props.subscribe_store_url;
    return typeof url === "string" ? url.trim() : "";
});

const themeUrl = computed(() => page.props.theme_url || "");
const auth = computed(() => page.props.auth);
const appName = computed(() => page.props.appName);
const settings = computed(() => page.props.settings || {});
const globals = computed(() => page.props.globals ?? {});
const mediaData = computed(() => page.props.globals.media || {});
const logoUrl = computed(() => {
    const m = mediaData.value;
    return m.transparent_logo || m.white_logo || "";
});

const websiteName = computed(
    () => page.props.globals?.seo?.website_name?.toUpperCase() || "",
);
const websiteSlogan = "MOST ACCURATE SOLUTIONS";

const year = new Date().getFullYear();

const developedByPrefix = computed(() => {
    const full = trans("Developed By Symfonix");
    return full.replace(/\s*Symfonix\s*$/i, "").trim() || "Developed by";
});

const tagline = computed(() => settings.value.tagline || page.props.appName);
const fallbackAddress = "95 South Park Avenue, USA";
const fallbackPhone = "+456 875 369 208";
const fallbackEmail = "support@example.com";

const rawPhone = computed(() =>
    String(settings.value.contact_phone || settings.value.phone || "").trim(),
);

const phoneDisplay = computed(() => {
    const raw = rawPhone.value;
    if (raw) {
        return formatTurkishPhone(raw);
    }
    return formatTurkishPhone(fallbackPhone) || fallbackPhone;
});

const phoneHref = computed(() => {
    const social = globals.value.social ?? {};
    const contact = globals.value.contact ?? {};
    const raw =
        rawPhone.value ||
        contact.phone ||
        settings.value.contact_phone ||
        settings.value.phone ||
        fallbackPhone;
    const normalized = normalizeTurkishPhoneDigits(raw);
    const phoneForWhatsApp = normalized ? `+${normalized}` : raw;

    return resolveWhatsAppContactHref({
        whatsapp: social.whatsapp || settings.value.whatsapp,
        phone: phoneForWhatsApp,
    });
});

const mainNavLinks = computed(() =>
    (props.navLinks || []).filter((l) => l?.href),
);
const pagesNavLinks = computed(() => {
    const pages = (props.navLinks || []).find((l) => l?.children?.length);
    return pages?.children || [];
});

const activeLocale = computed(() => page.props.locale || "en");

const footerPagesLinks = computed(() =>
    (page.props.globals?.pages?.footer ?? []).map((p) => ({
        key: `footer-page-${p.id}`,
        label: p.title,
        href: cmsPageUrl(p.slug, activeLocale.value),
    })),
);

const bottomBarPages = computed(
    () => page.props.globals?.pages?.bottom_bar ?? [],
);

const footerSocialLinks = computed(() => {
    const s = settings.value;
    const defs = [
        { key: "facebook", label: "Facebook", icon: "fa fa-facebook" },
        { key: "twitter", label: "Twitter", icon: "fa fa-twitter" },
        { key: "instagram", label: "Instagram", icon: "fab fa-instagram" },
        { key: "youtube", label: "YouTube", icon: "fa fa-youtube" },
        { key: "tiktok", label: "TikTok", icon: "fab fa-tiktok" },
    ];
    return defs
        .map((d) => {
            const raw = String(s[d.key] ?? "").trim();
            if (!raw) {
                return null;
            }
            return { ...d, href: raw };
        })
        .filter(Boolean);
});

function trans(key) {
    return page.props.translations[key] || key;
}

function clearSubscriptionSuccessTimer() {
    if (subscriptionSuccessTimer !== null) {
        clearTimeout(subscriptionSuccessTimer);
        subscriptionSuccessTimer = null;
    }
}

function showSubscriptionSuccessMessage() {
    clearSubscriptionSuccessTimer();
    showSubscriptionSuccess.value = true;
    subscriptionSuccessTimer = setTimeout(() => {
        showSubscriptionSuccess.value = false;
        subscriptionSuccessTimer = null;
    }, 8000);
}

function submitNewsletter() {
    const el = newsletterFormEl.value;
    if (el && typeof el.checkValidity === "function" && !el.checkValidity()) {
        el.reportValidity();
        return;
    }

    const url = subscribeStoreUrl.value;
    if (!url) {
        return;
    }

    subscribeForm.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            subscribeForm.reset();
            subscribeForm.clearErrors();
            showSubscriptionSuccessMessage();
        },
    });
}

onBeforeUnmount(() => {
    clearSubscriptionSuccessTimer();
});
</script>

<style scoped lang="scss">
/* Blog-v2 footer layout — dark theme tokens (see DARK_THEME_SPEC) */
.imas-blog-footer {
    background: var(--footer-bg, #06101f);
    color: var(--text-dim, #9aa6bd);
    margin-top: 0;
}

.imas-footer-wrap {
    max-width: 1280px;
    width: 100%;
    margin-inline: auto;
    padding-inline: 24px;
}

.imas-blog-footer .top-footer {
    background: var(--footer-bg, #06101f) !important;
    border-top: none !important;
    color: var(--text-dim, #9aa6bd) !important;
    padding: 56px 0 0 !important;
}

.imas-footer-grid {
    display: grid;
    grid-template-columns: 1.3fr 1fr 1fr 1.4fr;
    gap: 40px;
    margin: 0 !important;
}

.imas-footer-grid > [class*="col-"] {
    flex: none;
    width: 100%;
    max-width: 100%;
    padding: 0 !important;
}

.f-col p,
.f-col li,
.imas-blog-footer .netabout p,
.imas-blog-footer .contactus .in-p,
.imas-blog-footer .newsletters p {
    font-size: 13.5px;
    line-height: 1.85;
    color: var(--text-dim, #9aa6bd) !important;
}

.imas-blog-footer .navigation h3,
.imas-blog-footer .newsletters h3 {
    color: var(--text, #eef2f8) !important;
    font-size: 15px;
    font-weight: 700;
    position: relative;
    padding-bottom: 10px;
    margin-bottom: 18px;
    border: 0;
}

.imas-blog-footer .navigation h3::after,
.imas-blog-footer .newsletters h3::after {
    content: "";
    position: absolute;
    // left: 0;
    bottom: 0;
    width: 30px;
    height: 2px;
    background: var(--brand-gold, #d9a800);
    border-radius: 2px;
    display: block;
    margin: 0;
}

.brand-line {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}

.brand-line .logo {
    height: 55px !important;
    width: 55px !important;
    margin: 0 !important;
    flex-shrink: 0;
}

.brand-line .imas-brand-text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    min-width: 0;
}

.brand-line .website-name {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--brand-gold);
    line-height: 1.2;
}

.brand-line .website-slogan {
    font-size: 8px;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--text-dim);
    line-height: 1.3;
}

.brand-line .logo img {
    object-fit: contain;
    height: 100% !important;
    width: 100% !important;
    margin-bottom: 0 !important;
}

.contactus ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.contact-line {
    margin-top: 10px;
}

.contact-line .info {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    margin: 0;
}

.contact-line .ic {
    color: var(--brand-gold, #d9a800);
    flex-shrink: 0;
    width: 1.1em;
    text-align: center;
    line-height: 1.85;
}

.contact-line .ic i {
    font-size: 14px;
}

.contact-line .in-p {
    margin: 0 !important;
}

.contact-line .in-p--phone {
    direction: ltr;
    unicode-bidi: isolate;
}

.contact-line .in-p a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s ease;
}

.contact-line .in-p a:hover {
    color: var(--brand-gold, #d9a800);
}

.nav-footer {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 16px;
}

.nav-footer .links,
.nav-footer .nav-pages {
    display: contents;
    list-style: none;
    margin: 0;
    padding: 0;
}

.navigation > .links {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 16px;
}

.nav-footer .links a,
.navigation > .links a {
    transition:
        color 0.2s ease,
        padding 0.2s ease;
    color: var(--text-dim, #9aa6bd) !important;
}

.nav-footer .links a:hover,
.navigation > .links a:hover {
    color: var(--brand-gold, #d9a800) !important;
    padding-inline-start: 4px;
}

.links--single {
    grid-template-columns: 1fr !important;
}

.imas-blog-footer .newsletter {
    margin-top: 14px;
}

.imas-blog-footer .newsletter .email {
    display: flex;
    margin: 0;
    border-radius: 8px;
    overflow: hidden;
    background: var(--surface-2, #16264a);
    border: 1px solid transparent;
    transition:
        border-color 0.25s ease,
        box-shadow 0.25s ease;
}

.imas-blog-footer .newsletter .email:focus-within {
    border-color: var(--brand-gold, #d9a800);
    box-shadow: 0 0 0 4px rgba(217, 168, 0, 0.18);
}

.imas-blog-footer .newsletter .email input[type="email"] {
    flex: 1;
    background: transparent !important;
    border: 0 !important;
    padding: 12px 14px !important;
    color: var(--text, #eef2f8) !important;
    font-size: 13.5px;
    box-shadow: none !important;
    min-height: auto;
    width: auto;
}

.imas-blog-footer .newsletter .email input::placeholder {
    color: var(--text-muted, #6b7896) !important;
}

.imas-blog-footer .newsletter .email button {
    background: var(--brand-gold, #d9a800);
    border: 0;
    color: var(--text);
    padding: 0 22px;
    font-weight: 600;
    cursor: pointer;
    letter-spacing: 0.04em;
    font-size: 13px;
    text-transform: uppercase;
    transition: background 0.2s ease;
    flex-shrink: 0;
}

.imas-blog-footer .newsletter .email button:hover {
    background: var(--brand-gold-hover, #eecb3a);
}

.imas-blog-footer .newsletter .email button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.imas-blog-footer .newsletter .email input.is-invalid {
    color: #f8b4b4 !important;
}

.imas-blog-footer .subscription-success,
.imas-blog-footer .subscription-error {
    margin: 10px 0 0;
    font-size: 13px;
    line-height: 1.5;
}

.imas-blog-footer .subscription-success {
    color: #6ee7a0;
    background: rgba(110, 231, 160, 0.14);
    padding: 10px 12px;
    border-radius: 6px;
}

.imas-blog-footer .subscription-error {
    color: #f8b4b4;
}

.imas-blog-footer .second-footer.copyright {
    background: var(--footer-bg, #06101f) !important;
    border-top: 1px solid var(--divider, rgba(255, 255, 255, 0.06)) !important;
    margin-top: 48px;
    padding: 18px 0 !important;
}

.imas-second-footer__inner {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
    align-items: center;
    gap: 1rem 1.5rem;
    padding-inline: 24px;
}

.imas-second-footer__bottom-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-self: start;
    gap: 0;
    margin: 0;
}

.imas-second-footer__bottom-bar--empty {
    min-height: 0;
}

.imas-second-footer__page-link {
    color: var(--text-muted, #6b7896);
    font-size: 12.5px;
    font-weight: 500;
    text-decoration: none;
    white-space: nowrap;
    transition: color 0.2s ease;
}

.imas-second-footer__page-link:hover {
    color: var(--brand-gold, #d9a800);
    text-decoration: none;
}

.imas-second-footer__separator {
    color: rgba(255, 255, 255, 0.45);
    user-select: none;
    line-height: 1;
    margin-inline: 10px;
}

.imas-second-footer__copy {
    margin: 0;
    font-size: 12.5px;
    color: var(--text-muted, #6b7896) !important;
    text-align: center;
    justify-self: center;
}

/* blog-v2 .copyright .socials — placed under newsletter */
.imas-footer-socials {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 14px;
    margin-top: 18px;
}

.imas-footer-socials a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted, #6b7896);
    text-decoration: none;
    transition: color 0.2s ease;
}

.imas-footer-socials a:hover {
    color: var(--brand-gold, #d9a800);
}

.imas-footer-socials a i {
    margin: 0 !important;
    font-size: 14px;
    width: auto;
    height: auto;
    line-height: 1;
    background: transparent !important;
}

.imas-second-footer__developer {
    margin: 0;
    text-align: end;
    justify-self: end;
    font-size: 12.5px;
    color: var(--text-muted, #6b7896);
    font-weight: 400;
}

.imas-second-footer__developer-link {
    color: var(--text, #eef2f8);
    font-weight: 600;
    text-decoration: none;
    margin-inline-start: 0.35rem;
    transition: color 0.2s ease;
}

.imas-second-footer__developer-link:hover {
    color: var(--brand-gold, #d9a800);
    text-decoration: none;
}

@media screen and (max-width: 991px) {
    .imas-footer-grid {
        grid-template-columns: 1fr;
    }

    .imas-footer-col--brand {
        order: 4;
    }
}

@media screen and (max-width: 767px) {
    .imas-footer-grid {
        grid-template-columns: 1fr;
    }

    .imas-second-footer__inner {
        grid-template-columns: 1fr;
        justify-items: center;
        text-align: center;
    }

    .imas-second-footer__bottom-bar {
        justify-self: center;
        justify-content: center;
    }

    .imas-second-footer__copy,
    .imas-second-footer__developer {
        text-align: center;
        justify-self: center;
    }

    .imas-footer-socials {
        justify-content: center;
    }
}
</style>
