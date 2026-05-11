<template>
    <header id="header-container" class="header head-tr">
        <div id="header" class="head-tr bottom">
            <div class="container container-header">
                <div class="left-side">
                    <div id="logo">
                        <Link :href="route('home')">
                            <img
                                :src="logoUrl"
                                :data-sticky-logo="logoUrl"
                                alt=""
                            />
                        </Link>
                    </div>
                    <div class="mmenu-trigger">
                        <button
                            class="hamburger hamburger--collapse"
                            type="button"
                        >
                            <span class="hamburger-box">
                                <span class="hamburger-inner"></span>
                            </span>
                        </button>
                    </div>
                    <nav id="navigation" class="style-1 head-tr">
                        <ul id="responsive">
                            <li
                                v-for="item in navLinks"
                                :key="item.key"
                                :class="{ 'has-submenu': item?.children?.length }"
                            >
                                <Link v-if="item.href" :href="item.href">
                                    {{ trans(item.key) }}
                                </Link>
                                <a v-else href="#" @click.prevent>
                                    {{ trans(item.key) }}
                                </a>

                                <ul v-if="item?.children?.length">
                                    <li
                                        v-for="child in item.children"
                                        :key="`${item.key}-${child.key}`"
                                    >
                                        <Link :href="child.href">
                                            {{ trans(child.key) }}
                                        </Link>
                                    </li>
                                </ul>
                            </li>
                            <li v-if="auth?.type === 'admin'">
                                <a :href="route('admin.dashboard.index')">{{
                                    trans("Dashboard")
                                }}</a>
                            </li>
                            <li class="d-xl-none mb-2 mt-2">
                                <span >{{
                                    trans("Language")
                                }}</span>
                                <ul class="list-unstyled mb-0 pl-0">
                                    <li
                                        v-for="loc in localeSwitcher"
                                        :key="loc.code"
                                    >
                                        <a
                                            href="#"
                                            class="lang-switch-row"
                                            :class="{
                                                'font-weight-bold':
                                                    loc.code === currentLocale,
                                            }"
                                            @click.prevent="
                                                switchLocale(loc.url)
                                            "
                                        >
                                            <span
                                                v-if="flagCountryClass(loc.code)"
                                                class="fi lang-switch-flag"
                                                :class="flagCountryClass(loc.code)"
                                                aria-hidden="true"
                                            ></span>
                                            {{ loc.native }}
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li
                                v-if="!auth"
                                class="d-none d-xl-none d-block d-lg-block"
                            >
                                <Link :href="route('login')">{{
                                    trans("Login")
                                }}</Link>
                            </li>
                            <li
                                v-if="!auth"
                                class="d-none d-xl-none d-block d-lg-block"
                            >
                                <Link :href="route('register')">{{
                                    trans("Register")
                                }}</Link>
                            </li>
                            <li
                                v-if="!auth"
                                class="d-none d-xl-none d-block d-lg-block mt-5 pb-4 ml-5 border-bottom-0"
                            >
                                <Link
                                    :href="route('register')"
                                    class="button border btn-lg btn-block text-center"
                                >
                                    {{ trans("Add Listing") }}
                                    <i class="fas fa-laptop-house ml-2"></i>
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </div>

                <div class="right-side d-none d-none d-lg-none d-xl-flex">
                    <div class="header-widget">
                        <Link :href="route('register')" class="button border">
                            {{ trans("Add Listing") }}
                            <i class="fas fa-laptop-house ml-2"></i>
                        </Link>
                    </div>
                </div>

                <div v-if="auth" class="header-user-menu user-menu add">
                    <div class="header-user-name">
                        <span
                            ><img
                                :src="`${themeUrl}/images/testimonials/ts-1.jpg`"
                                alt=""
                        /></span>
                        {{ trans("Hi") }}, {{ auth.name }}!
                    </div>
                    <ul>
                        <li>
                            <a :href="route('home')">{{ trans("Profile") }}</a>
                        </li>
                        <li>
                            <button
                                type="button"
                                class="dropdown-logout"
                                @click="logout"
                            >
                                {{ trans("Log Out") }}
                            </button>
                        </li>
                    </ul>
                </div>

                <div
                    v-else
                    class="right-side d-none d-none d-lg-none d-xl-flex sign ml-0"
                >
                    <div class="header-widget sign-in">
                        <Link :href="route('login')" class="show-reg-form">{{
                            trans("Sign In")
                        }}</Link>
                    </div>
                </div>

                <div
                    class="header-user-menu user-menu add d-none d-lg-none d-xl-flex"
                >
                    <div
                        ref="langWrapRef"
                        class="lang-wrap"
                        :class="{ 'lang-wrap--open': langMenuOpen }"
                    >
                        <div
                            class="show-lang"
                            role="button"
                            tabindex="0"
                            :aria-expanded="langMenuOpen"
                            aria-haspopup="listbox"
                            :aria-label="trans('Language')"
                            @click.stop="toggleLangMenu"
                            @keydown.enter.prevent="toggleLangMenu"
                            @keydown.space.prevent="toggleLangMenu"
                        >
                            <span class="show-lang-trigger-inner">
                                <span
                                    v-if="flagCountryClass(currentLocale)"
                                    class="fi lang-switch-flag lang-switch-flag--trigger "
                                    :class="flagCountryClass(currentLocale)"
                                    aria-hidden="true"
                                ></span>
                                <strong>{{ localeBadge }}</strong>
                            </span>
                            <i class="fa fa-caret-down arrlan"></i>
                        </div>
                        <ul
                            class="lang-tooltip lang-action no-list-style"
                            role="listbox"
                        >
                            <li v-for="loc in localeSwitcher" :key="loc.code">
                                <a
                                    href="#"
                                    class="lang-switch-row"
                                    :class="{
                                        'current-lan': loc.code === currentLocale,
                                    }"
                                    role="option"
                                    :aria-selected="loc.code === currentLocale"
                                    @click.prevent="switchLocale(loc.url)"
                                >
                                    <span
                                        v-if="flagCountryClass(loc.code)"
                                        class="fi lang-switch-flag "
                                        :class="flagCountryClass(loc.code)"
                                        aria-hidden="true"
                                    ></span>
                                    <span class="mx-2">{{ loc.native }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";

defineProps({
    navLinks: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const langMenuOpen = ref(false);
const langWrapRef = ref(null);

const themeUrl = computed(() => page.props.theme_url || "");
const auth = computed(() => page.props.auth);

const mediaData = computed(() => page.props.globals.media || {});
const logoUrl = computed(() => mediaData.value.white_logo || "");

const localeSwitcher = computed(() => page.props.locale_switcher || []);
const currentLocale = computed(() => page.props.locale || "en");
const localeBadge = computed(() => {
    const code = currentLocale.value;
    if (code === "en") {
        return "ENG";
    }
    return code.toUpperCase();
});

function trans(key) {
    return page.props.translations[key] || key;
}

/** ISO 3166-1 alpha-2 for flag-icons (`fi-xx`). Not every locale maps 1:1 to a flag — adjust as needed. */
const LOCALE_FLAG_SUFFIX = {
    en: "gb",
    tr: "tr",
    ar: "sa",
};

function flagCountryClass(localeCode) {
    const suffix = LOCALE_FLAG_SUFFIX[localeCode];
    return suffix ? `fi-${suffix}` : null;
}

function toggleLangMenu() {
    langMenuOpen.value = !langMenuOpen.value;
}

function closeLangMenuOnOutsideClick(event) {
    const el = langWrapRef.value;
    if (!el || !langMenuOpen.value) {
        return;
    }
    if (!el.contains(event.target)) {
        langMenuOpen.value = false;
    }
}

function switchLocale(url) {
    langMenuOpen.value = false;
    router.visit(url, { preserveScroll: true });
}

function logout() {
    router.post(route("logout"));
}

let mmenuApi = null;

/**
 * Root panel navbar has title only (no close). Add an X that links to #app so mmenu's offCanvas handler closes the drawer.
 */
function attachMmenuCloseButton($) {
    const $page = $(".mm-page").first().length
        ? $(".mm-page").first()
        : $("#app");
    const pageId = $page.attr("id");
    if (!pageId) {
        return;
    }

    $(".mm-menu.mm-offcanvas").each(function () {
        const $menu = $(this);
        const $rootPanel = $menu.find("> .mm-panels > .mm-panel").first();
        const $navbar = $rootPanel.children(".mm-navbar").first();
        if (!$navbar.length || $navbar.find("a.mm-close").length) {
            return;
        }

        const isRtl =
            document.documentElement.getAttribute("dir") === "rtl" ||
            document.documentElement.dir === "rtl";
        const label = isRtl ? "إغلاق القائمة" : "Close menu";
        $navbar.prepend(
            `<a class="mm-btn mm-close" href="#${pageId}" aria-label="${label}"></a>`,
        );
    });
}

function initMobileMenuMmenu() {
    const $ = window.jQuery;
    if (!$ || !$.fn?.mmenu) {
        return;
    }

    const wi = $(window).width();
    if (wi > 992) {
        teardownMobileMenuMmenu();
        return;
    }

    $(".mmenu-init").remove();

    const $navigation = $("#navigation").first();
    if (!$navigation.length) {
        return;
    }

    $navigation
        .clone()
        .addClass("mmenu-init")
        .insertBefore("#navigation")
        .removeAttr("id")
        .removeClass("style-1 style-2")
        .find("ul")
        .removeAttr("id");

    $(".mmenu-init").find(".container").removeClass("container");

    const isRtl =
        document.documentElement.getAttribute("dir") === "rtl" ||
        document.documentElement.dir === "rtl";

    $(".mmenu-init").mmenu(
        { counters: true },
        {
            offCanvas: {
                // Inertia mounts inside a root element (usually `#app`).
                // Using `pageSelector` ensures the whole SPA (including header) slides out.
                pageSelector: "#app",
                // Drawer must open from the inline-start side (right in RTL).
                position: isRtl ? "right" : "left",
            },
        },
    );

    mmenuApi = $(".mmenu-init").data("mmenu") || null;
    if (!mmenuApi) {
        return;
    }

    const $icon = $(".hamburger");

    $(".mmenu-trigger")
        .off("click.imasMmenu")
        .on("click.imasMmenu", () => {
            mmenuApi?.open?.();
        });

    mmenuApi.bind("open:finish", () => {
        setTimeout(() => {
            $icon.addClass("is-active");
            attachMmenuCloseButton($);
        });
    });

    mmenuApi.bind("close:finish", () => {
        setTimeout(() => {
            $icon.removeClass("is-active");
        });
    });

    $(".mm-next").addClass("mm-fullsubopen");

    setTimeout(() => attachMmenuCloseButton($), 0);
}

function teardownMobileMenuMmenu() {
    const $ = window.jQuery;
    if (!$) {
        return;
    }

    $(".mmenu-trigger").off("click.imasMmenu");
    $(".mmenu-init").remove();
    mmenuApi = null;
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

    const $origHeader = $("#header").first();
    if (!$origHeader.length || $origHeader.next("#header.cloned").length) {
        return;
    }

    $origHeader
        .not("#header-container.header-style-2 #header")
        .clone(true)
        .addClass("cloned unsticky")
        .insertAfter("#header");

    $("#navigation.style-2")
        .clone(true)
        .addClass("cloned unsticky")
        .insertAfter("#navigation.style-2");
    $("#logo .sticky-logo")
        .clone(true)
        .prependTo("#navigation.style-2.cloned ul#responsive");

    function syncStickyLogo() {
        const stickySrc = $("#header:not(.cloned) #logo img")
            .first()
            .attr("data-sticky-logo");
        if (stickySrc) {
            $("#header.cloned #logo img").first().attr("src", stickySrc);
        }
    }

    function onStickyScroll() {
        const headerOffset = $("#header-container").height() * 2;
        if ($(window).scrollTop() >= headerOffset) {
            $("#header.cloned").addClass("sticky").removeClass("unsticky");
            $("#navigation.style-2.cloned")
                .addClass("sticky")
                .removeClass("unsticky");
        } else {
            $("#header.cloned").addClass("unsticky").removeClass("sticky");
            $("#navigation.style-2.cloned")
                .addClass("unsticky")
                .removeClass("sticky");
        }
        syncStickyLogo();
    }

    $(window).on("scroll.imasSticky load.imasSticky", onStickyScroll);
    onStickyScroll();
}

function teardownStickyHeaderClone() {
    const $ = window.jQuery;
    $(window).off(".imasSticky");
    $("#header.cloned").remove();
    $("#navigation.style-2.cloned").remove();
}

function reinitHeaderChromeForLocale() {
    langMenuOpen.value = false;
    nextTick(() => {
        teardownStickyHeaderClone();
        teardownMobileMenuMmenu();
        initStickyHeaderClone();
        initMobileMenuMmenu();
    });
}

watch(
    () => page.props.locale,
    () => reinitHeaderChromeForLocale(),
);

onMounted(() => {
    document.addEventListener("click", closeLangMenuOnOutsideClick);

    nextTick(() => {
        initStickyHeaderClone();
        initMobileMenuMmenu();
    });

    const $ = window.jQuery;
    if ($) {
        $(window)
            .off("resize.imasMmenu")
            .on("resize.imasMmenu", () => {
                initMobileMenuMmenu();
            });
    }
});

onBeforeUnmount(() => {
    document.removeEventListener("click", closeLangMenuOnOutsideClick);

    teardownStickyHeaderClone();
    teardownMobileMenuMmenu();

    const $ = window.jQuery;
    if ($) {
        $(window).off("resize.imasMmenu");
    }
});
</script>

<style scoped>
.show-lang span strong{
    padding:0 !important;
}
.lang-switch-flag--trigger{
    font-size: 1.3em !important;
}
.show-lang .fa-caret-down.arrlan {
    left: 90px;
}
.dropdown-logout {
    background: none;
    border: 0;
    color: inherit;
    cursor: pointer;
    font: inherit;
    padding: 0;
    text-align: start;
    width: 100%;
}

/* Theme leaves .lang-tooltip permanently hidden; toggle visibility in JS. */
.lang-wrap.lang-wrap--open .lang-tooltip {
    opacity: 1 !important;
    visibility: visible !important;
}

.lang-switch-row {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.lang-switch-flag {
    flex-shrink: 0;
    font-size: 1.1em;
    line-height: 1;
    border-radius: 2px;
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.08);
}

.show-lang-trigger-inner {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.lang-switch-flag--trigger {
    font-size: 1em;
}
</style>
