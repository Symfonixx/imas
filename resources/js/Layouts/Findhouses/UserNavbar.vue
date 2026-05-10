<template>
    <header id="header-container" class="header head-tr">
        <div id="header" class="head-tr bottom">
            <div class="container container-header">
                <div class="left-side">
                    <div id="logo">
                        <Link :href="route('home')">
                            <img
                                :src="`${themeUrl}/images/logo-white-1.svg`"
                                :data-sticky-logo="`${themeUrl}/images/logo-red.svg`"
                                alt=""
                            >
                        </Link>
                    </div>
                    <div class="mmenu-trigger">
                        <button class="hamburger hamburger--collapse" type="button">
                            <span class="hamburger-box">
                                <span class="hamburger-inner"></span>
                            </span>
                        </button>
                    </div>
                    <nav id="navigation" class="style-1 head-tr">
                        <ul id="responsive">
                            <li>
                                <Link :href="route('home')">{{ trans('Home') }}</Link>
                            </li>
                            <li>
                                <Link :href="route('property.index')">{{ trans('Properties') }}</Link>
                            </li>
                            <li v-if="auth?.type === 'admin'">
                                <a :href="route('admin.dashboard.index')">{{ trans('Dashboard') }}</a>
                            </li>
                            <li v-if="!auth" class="d-none d-xl-none d-block d-lg-block">
                                <Link :href="route('login')">{{ trans('Login') }}</Link>
                            </li>
                            <li v-if="!auth" class="d-none d-xl-none d-block d-lg-block">
                                <Link :href="route('register')">{{ trans('Register') }}</Link>
                            </li>
                            <li
                                v-if="!auth"
                                class="d-none d-xl-none d-block d-lg-block mt-5 pb-4 ml-5 border-bottom-0"
                            >
                                <Link :href="route('register')" class="button border btn-lg btn-block text-center">
                                    {{ trans('Add Listing') }}
                                    <i class="fas fa-laptop-house ml-2"></i>
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </div>

                <div class="right-side d-none d-none d-lg-none d-xl-flex">
                    <div class="header-widget">
                        <Link :href="route('register')" class="button border">
                            {{ trans('Add Listing') }}
                            <i class="fas fa-laptop-house ml-2"></i>
                        </Link>
                    </div>
                </div>

                <div v-if="auth" class="header-user-menu user-menu add">
                    <div class="header-user-name">
                        <span><img :src="`${themeUrl}/images/testimonials/ts-1.jpg`" alt=""></span>
                        {{ trans('Hi') }}, {{ auth.name }}!
                    </div>
                    <ul>
                        <li><a :href="route('home')">{{ trans('Profile') }}</a></li>
                        <li>
                            <button type="button" class="dropdown-logout" @click="logout">{{ trans('Log Out') }}</button>
                        </li>
                    </ul>
                </div>

                <div v-else class="right-side d-none d-none d-lg-none d-xl-flex sign ml-0">
                    <div class="header-widget sign-in">
                        <Link :href="route('login')" class="show-reg-form">{{ trans('Sign In') }}</Link>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import {computed, nextTick, onBeforeUnmount, onMounted} from 'vue';
import {Link, router, usePage} from '@inertiajs/vue3';

const page = usePage();

const themeUrl = computed(() => page.props.theme_url || '');
const auth = computed(() => page.props.auth);

function trans(key) {
    return page.props.translations[key] || key;
}

function logout() {
    router.post(route('logout'));
}

/**
 * Theme sticky header (see `public/theme/findhouses/js/mmenu.js`): clones `#header` into `#header.cloned`.
 * That runs on `document.ready` before Inertia mounts the navbar, so `#header` does not exist yet.
 * Re-run clone + scroll handling once the real header is in the DOM.
 */
function initStickyHeaderClone() {
    const $ = window.jQuery;
    if (!$) {
        return;
    }

    const $origHeader = $('#header').first();
    if (!$origHeader.length || $origHeader.next('#header.cloned').length) {
        return;
    }

    $origHeader
        .not('#header-container.header-style-2 #header')
        .clone(true)
        .addClass('cloned unsticky')
        .insertAfter('#header');

    $('#navigation.style-2').clone(true).addClass('cloned unsticky').insertAfter('#navigation.style-2');
    $('#logo .sticky-logo').clone(true).prependTo('#navigation.style-2.cloned ul#responsive');

    function syncStickyLogo() {
        const stickySrc = $('#header:not(.cloned) #logo img').first().attr('data-sticky-logo');
        if (stickySrc) {
            $('#header.cloned #logo img').first().attr('src', stickySrc);
        }
    }

    function onStickyScroll() {
        const headerOffset = $('#header-container').height() * 2;
        if ($(window).scrollTop() >= headerOffset) {
            $('#header.cloned').addClass('sticky').removeClass('unsticky');
            $('#navigation.style-2.cloned').addClass('sticky').removeClass('unsticky');
        } else {
            $('#header.cloned').addClass('unsticky').removeClass('sticky');
            $('#navigation.style-2.cloned').addClass('unsticky').removeClass('sticky');
        }
        syncStickyLogo();
    }

    $(window).on('scroll.imasSticky load.imasSticky', onStickyScroll);
    onStickyScroll();
}

function teardownStickyHeaderClone() {
    const $ = window.jQuery;
    $(window).off('.imasSticky');
    $('#header.cloned').remove();
    $('#navigation.style-2.cloned').remove();
}

onMounted(() => {
    nextTick(() => {
        initStickyHeaderClone();
    });
});

onBeforeUnmount(() => {
    teardownStickyHeaderClone();
});
</script>

<style scoped>
.dropdown-logout {
    background: none;
    border: 0;
    color: inherit;
    cursor: pointer;
    font: inherit;
    padding: 0;
    text-align: left;
    width: 100%;
}
</style>
