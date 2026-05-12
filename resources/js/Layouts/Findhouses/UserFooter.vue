<template>
    <footer class="first-footer rec-pro">
        <div class="top-footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="netabout">
                            <Link :href="route('home')" class="logo">
                                <img :src="logoUrl" alt="logo" class="footer_logo">
                            </Link>
                            <p>{{ tagline }}</p>
                        </div>
                        <div class="contactus">
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
                        <div class="navigation">
                            <h3>{{ trans('Navigation') }}</h3>
                            <div class="nav-footer">
                                <ul>
                                    <li v-for="item in mainNavLinks" :key="item.key">
                                        <Link :href="item.href">{{ trans(item.key) }}</Link>
                                    </li>
                                </ul>
                                <ul class="nav-pages">
                                    <li class="font-weight-bold">{{ trans('Pages') }}</li>
                                    <li v-for="item in pagesNavLinks" :key="item.key">
                                        <Link :href="item.href">{{ trans(item.key) }}</Link>
                                    </li>
                                </ul>
                                <ul class="nav-right">
                                    <li v-if="!auth"><Link :href="route('login')">{{ trans('Login') }}</Link></li>
                                    <li v-if="!auth"><Link :href="route('register')">{{ trans('Register') }}</Link></li>
                                    <!-- <li v-if="auth?.type === 'admin'" class="no-mgb">
                                        <a :href="route('admin.dashboard.index')">{{ trans('Dashboard') }}</a>
                                    </li> -->
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="widget">
                            <h3>{{ trans('Explore') }}</h3>
                            <div class="twitter-widget contuct">
                                <div class="twitter-area">
                                    <div class="single-item">
                                        <div class="icon-holder">
                                            <i class="fa fa-building" aria-hidden="true"></i>
                                        </div>
                                        <div class="text">
                                            <h5>{{ trans('New listings weekly') }}</h5>
                                            <h4>{{ trans('Stay tuned for featured properties.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="newsletters">
                            <h3>{{ trans('Newsletters') }}</h3>
                            <p>{{ trans('Sign Up for Our Newsletter to get Latest Updates and Offers.') }}</p>
                        </div>
                        <form class="bloq-email mailchimp form-inline" @submit.prevent>
                            <label for="subscribeEmail" class="error"></label>
                            <div class="email">
                                <input id="subscribeEmail" type="email" name="EMAIL" placeholder="Enter Your Email">
                                <input type="submit" value="Subscribe">
                                <p class="subscription-success"></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="second-footer rec-pro">
            <div class="container-fluid sd-f">
                <p>{{ year }} © {{ appName }} — {{ trans('All Rights Reserved.') }}</p>
                <ul class="netsocials">
                    <li v-for="item in footerSocialLinks" :key="item.key">
                        <a
                            :href="item.href"
                            target="_blank"
                            rel="noopener noreferrer"
                            :aria-label="item.label"
                        ><i :class="item.icon" aria-hidden="true"></i></a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>

    <a data-scroll href="#wrapper" class="go-up"><i class="fa fa-angle-double-up" aria-hidden="true"></i></a>
</template>

<script setup>
import {computed} from 'vue';
import {Link, usePage} from '@inertiajs/vue3';

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


<style scoped>
.logo {
    height: 60px;
    width: 60px;
    object-fit: contain;
    /* transform: translate3d(0, 0, 0); */
}
</style>