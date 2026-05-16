<template>
    <footer class="first-footer rec-pro">
        <div class="top-footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="netabout">
                            <div class="logo">
                                <img :src="logoUrl" alt="logo" class="footer_logo">
                            </div>
                            <p class="text-start">{{ tagline }}</p>
                        </div>
                        <div class="contactus text-start">
                            <ul>
                                <li>
                                    <div class="info">
                                        <i class="fa fa-map-marker" aria-hidden="true"></i>
                                        <p class="in-p">{{ settings.contact_address || fallbackAddress }}</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="info">
                                        <i class="fa fa-phone" aria-hidden="true"></i>
                                        <p class="in-p">{{ settings.contact_phone || fallbackPhone }}</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="info">
                                        <i class="fa fa-envelope" aria-hidden="true"></i>
                                        <p class="in-p ti">{{ settings.contact_email || fallbackEmail }}</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="navigation text-start">
                            <h3>{{ trans('navBar.navigation') }}</h3>
                            <div class="nav-footer text-start">
                                <ul>
                                    <li v-for="item in mainNavLinks" :key="item.key">
                                        <Link :href="item.href">{{ trans(item.key) }}</Link>
                                    </li>
                                </ul>
                                <ul class="nav-pages">
                                    <li v-for="item in pagesNavLinks" :key="item.key">
                                        <Link :href="item.href">{{
                                            item.label ?? trans(item.key)
                                        }}</Link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="navigation text-start">
                            <h3>{{ trans('navBar.useful_links') }}</h3>
                            <ul class="w-50">
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
                    <div class="col-lg-3 col-md-6">
                        <div class="newsletters text-start">
                            <h3>{{ trans('navBar.newsLetters') }}</h3>
                            <p>{{ trans('navBar.signup_for_newsletters') }}</p>
                        </div>
                        <form class="bloq-email mailchimp form-inline" @submit.prevent>
                            <label for="subscribeEmail" class="error"></label>
                            <div class="email">
                                <input id="subscribeEmail" type="email" name="EMAIL" :placeholder="trans('navBar.enter_your_email')">
                                <input type="submit" :value="trans('navBar.subscribe')">
                                <p class="subscription-success"></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="second-footer rec-pro">
            <div class="container-fluid sd-f imas-second-footer__inner">
                <p class="imas-second-footer__copy">
                    {{ year }} © {{ appName }} —
                    {{ trans("navBar.All Rights Reserved.   ") }}
                </p>
                <ul
                    v-if="footerSocialLinks.length"
                    class="netsocials imas-second-footer__socials"
                >
                    <li v-for="item in footerSocialLinks" :key="item.key">
                        <a
                            :href="item.href"
                            target="_blank"
                            rel="noopener noreferrer"
                            :aria-label="item.label"
                        ><i :class="item.icon" aria-hidden="true"></i></a>
                    </li>
                </ul>
                <p class="imas-second-footer__developer">
                    <span>{{ developedByPrefix }}</span>
                    <a
                        href="https://symfonix.io/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="imas-second-footer__developer-link"
                    >Symfonix</a>
                </p>
            </div>
        </div>
    </footer>

    <a data-scroll href="#wrapper" class="go-up"><i class="fa fa-angle-double-up" aria-hidden="true"></i></a>
</template>

<script setup>
import {computed} from 'vue';
import {Link, usePage} from '@inertiajs/vue3';
import { cmsPageUrl } from '@/utils/cmsPageUrl.js';

const props = defineProps({
    navLinks: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const themeUrl = computed(() => page.props.theme_url || '');
const auth = computed(() => page.props.auth);
const appName = computed(() => page.props.appName);
const settings = computed(() => page.props.settings || {});
const mediaData = computed(() => page.props.globals.media || {});
const logoUrl = computed(() => {
    const m = mediaData.value;
    return m.black_logo || m.white_logo || "";
});

const year = new Date().getFullYear();

const developedByPrefix = computed(() => {
    const full = trans("Developed By Symfonix");
    return full.replace(/\s*Symfonix\s*$/i, "").trim() || "Developed by";
});

const tagline = computed(() => settings.value.tagline || page.props.appName);
const fallbackAddress = '95 South Park Avenue, USA';
const fallbackPhone = '+456 875 369 208';
const fallbackEmail = 'support@example.com';

const mainNavLinks = computed(() =>
    (props.navLinks || []).filter((l) => l?.href),
);
const pagesNavLinks = computed(() => {
    const pages = (props.navLinks || []).find((l) => l?.children?.length);
    return pages?.children || [];
});

const footerPagesLinks = computed(() =>
    (page.props.globals?.pages?.footer ?? []).map((p) => ({
        key: `footer-page-${p.id}`,
        label: p.title,
        href: cmsPageUrl(p.slug),
    })),
);

const footerSocialLinks = computed(() => {
    const s = settings.value;
    const defs = [
        {key: 'facebook', label: 'Facebook', icon: 'fa fa-facebook'},
        {key: 'twitter', label: 'Twitter', icon: 'fa fa-twitter'},
        {key: 'instagram', label: 'Instagram', icon: 'fab fa-instagram'},
        {key: 'youtube', label: 'YouTube', icon: 'fa fa-youtube'},
        {key: 'tiktok', label: 'TikTok', icon: 'fab fa-tiktok'},
    ];
    return defs
        .map((d) => {
            const raw = String(s[d.key] ?? '').trim();
            if (!raw) {
                return null;
            }
            return {...d, href: raw};
        })
        .filter(Boolean);
});

function trans(key) {
    return page.props.translations[key] || key;
}
</script>


<style scoped lang="scss">
.logo {
    height: 130px !important;
    width: 130px !important;
    img{
        object-fit: contain;
        height: 100% !important;
        width: 100% !important;

    }
}
.nav-footer{
    gap: .7rem !important  ;
}
.contactus li i {
    margin-inline-end: 0.5rem !important;
}

.imas-second-footer__inner {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 1rem 1.5rem;
}

.imas-second-footer__copy {
    margin: 0;
    grid-column: 1;
    justify-self: start;
    text-align: start;
}

.imas-second-footer__socials {
    grid-column: 2;
    justify-content: center;
    justify-self: center;
    margin: 0 auto;
    padding: 0;
    width: auto;
}

.imas-second-footer__developer {
    margin: 0;
    grid-column: 3;
    justify-self: end;
    text-align: end;
    color: rgba(255, 255, 255, 0.88);
    font-weight: 400;
}

.imas-second-footer__developer-link {
    color: #fff;
    font-weight: 600;
    text-decoration: none;
    margin-inline-start: 0.35rem;
    transition: color 0.2s ease, opacity 0.2s ease;
}

.imas-second-footer__developer-link:hover {
    color: var(--brand-gold, #d9a800);
    text-decoration: none;
    opacity: 0.95;
    // border-radius: 10px !important;
}

@media screen and (max-width: 767px) {
    .imas-second-footer__inner {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .imas-second-footer__copy,
    .imas-second-footer__socials,
    .imas-second-footer__developer {
        grid-column: 1;
        justify-self: center;
        text-align: center;
    }
}
.imas-second-footer__socials li a i{
    margin: 0 !important;
    margin-inline-end: .7rem !important;
}
</style>
