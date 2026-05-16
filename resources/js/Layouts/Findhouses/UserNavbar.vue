<template>
    <header
        ref="headerContainerRef"
        id="header-container"
        class="header"
        :class="[
            transparentNavbar ? 'head-tr' : 'imas-navbar-solid',
            { 'imas-header-scroll-pinned': headerPinned },
        ]"
    >
        <div
            ref="headerBarRef"
            id="header"
            class="bottom"
            :class="{
                'head-tr': transparentNavbar && !headerPinned,
                'imas-scroll-pinned': headerPinned,
                'imas-scroll-pinned--in': headerPinned && headerPinnedVisible,
            }"
        >
            <div class="container container-header">
                <div class="left-side">
                    <div id="logo" ref="logoRef">
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
                    <nav
                        id="navigation"
                        class="style-1"
                        :class="{
                            'head-tr': transparentNavbar && !headerPinned,
                        }"
                    >
                        <ul id="responsive" ref="navListRef">
                            <li
                                v-for="item in navLinks"
                                :key="item.key"
                                class="imas-nav-item text-start"
                                :class="{
                                    'has-submenu': item?.children?.length,
                                }"
                            >
                                <Link v-if="item.href" :href="item.href">
                                    {{ item.label ?? trans(item.key) }}
                                </Link>
                                <a v-else href="#" @click.prevent>
                                    {{ item.label ?? trans(item.key) }}
                                </a>

                                <ul v-if="item?.children?.length">
                                    <li
                                        v-for="child in item.children"
                                        :key="`${item.key}-${child.key}`"
                                    >
                                        <Link :href="child.href">
                                            {{
                                                child.label ?? trans(child.key)
                                            }}
                                        </Link>
                                    </li>
                                </ul>
                            </li>
                            <!-- <li v-if="auth?.type === 'admin'">
                                <a :href="route('admin.dashboard.index')">{{
                                    trans("Dashboard")
                                }}</a>
                            </li> -->
                            <li class="d-xl-none mb-2 mt-2">
                                <span>{{ trans("Language") }}</span>
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
                                                v-if="
                                                    flagCountryClass(loc.code)
                                                "
                                                class="fi lang-switch-flag"
                                                :class="
                                                    flagCountryClass(loc.code)
                                                "
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
                                <a
                                    href="#"
                                    class="imas-auth-nav-link"
                                    data-open-auth="login"
                                    >{{ trans("Login") }}</a
                                >
                            </li>
                            <li
                                v-if="!auth"
                                class="d-none d-xl-none d-block d-lg-block"
                            >
                                <a
                                    href="#"
                                    class="imas-auth-nav-link"
                                    data-open-auth="register"
                                    >{{ trans("Register") }}</a
                                >
                            </li>
                            <!-- <li
                                v-if="!auth"
                                class="d-none d-xl-none d-block d-lg-block mt-5 pb-4 ml-5 border-bottom-0"
                            >
                                <a
                                    href="#"
                                    class="button border btn-lg btn-block text-center"
                                    data-open-auth="register"
                                >
                                    {{ trans("Add Listing") }}
                                    <i class="fas fa-laptop-house ml-2"></i>
                                </a>
                            </li> -->
                        </ul>
                    </nav>
                </div>

                <!-- <div
                    v-if="auth"
                    class="right-side d-none d-none d-lg-none d-xl-flex"
                >
                    <div class="header-widget">
                        <Link :href="route('register')" class="button border">
                            {{ trans("Add Listing") }}
                            <i class="fas fa-laptop-house ml-2"></i>
                        </Link>
                    </div>
                </div> -->

                <div
                    v-if="auth"
                    ref="userMenuWrapRef"
                    class="header-user-menu user-menu add UserMenu imas-header-action"
                    :class="{ active: userMenuOpen }"
                >
                    <div
                        class="header-user-name"
                        role="button"
                        tabindex="0"
                        :aria-expanded="userMenuOpen"
                        aria-haspopup="true"
                        :aria-label="trans('Account menu')"
                        @click.stop="toggleUserMenu"
                        @keydown.enter.prevent="toggleUserMenu"
                        @keydown.space.prevent="toggleUserMenu"
                    >
                        <span><img :src="auth.avatar" alt="" /></span>
                        {{ trans("Hi") }} {{ auth.nav_display_name }}
                    </div>
                    <ul class="imas-user-menu-dropdown text-start">
                        <li v-if="isAdmin">
                            <Link
                                class="imas-user-menu-dropdown__item"
                                :href="route('admin.dashboard.index')"
                                @click="userMenuOpen = false"
                            >
                                {{ trans("Dashboard") }}
                            </Link>
                        </li>
                        <li>
                            <Link
                                class="imas-user-menu-dropdown__item"
                                :href="profileHref"
                                @click="userMenuOpen = false"
                            >
                                {{ trans("global.profile") }}
                            </Link>
                        </li>
                        <li>
                            <button
                                type="button"
                                class="imas-user-menu-dropdown__item dropdown-logout"
                                @click="logout"
                            >
                                {{ trans("global.LogOut") }}
                            </button>
                        </li>
                    </ul>
                </div>

                <div
                    v-else
                    class="right-side d-none d-none d-lg-none d-xl-flex sign ml-0 imas-header-action"
                >
                    <div class="header-widget sign-in">
                        <a
                            href="#"
                            class="show-reg-form modal-open"
                            data-open-auth="login"
                            >{{ trans("Sign In") }}</a
                        >
                    </div>
                </div>

                <div
                    class="header-user-menu user-menu add d-none d-lg-none d-xl-flex mx-2 p-0 imas-header-action"
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
                                    class="fi lang-switch-flag lang-switch-flag--trigger"
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
                                        'current-lan':
                                            loc.code === currentLocale,
                                    }"
                                    role="option"
                                    :aria-selected="loc.code === currentLocale"
                                    @click.prevent="switchLocale(loc.url)"
                                >
                                    <span
                                        v-if="flagCountryClass(loc.code)"
                                        class="fi lang-switch-flag"
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
        <div
            v-show="headerPinned"
            class="imas-header-scroll-spacer"
            :style="{ height: `${scrollPinSpacerPx}px` }"
            aria-hidden="true"
        ></div>
        <AuthModal v-model:open="authModalOpen" :start-tab="authStartTab" />
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
import { useGsap } from "@/composables/useGsap";
import { prefersReducedMotion } from "@/plugins/gsap";
import AuthModal from "./AuthModal.vue";

const props = defineProps({
    navLinks: {
        type: Array,
        default: () => [],
    },
    transparentNavbar: {
        type: Boolean,
        default: true,
    },
});

const page = usePage();

const authModalOpen = ref(false);
const authStartTab = ref("login");

function openAuthModal(tab = "login") {
    authStartTab.value = tab === "register" || tab === "reset" ? tab : "login";
    authModalOpen.value = true;
    mmenuApi?.close?.();
}

/** mmenu clones `#navigation` without Vue listeners; delegate auth open from document. */
function onDelegatedOpenAuth(e) {
    const el = e.target.closest("a[data-open-auth]");
    if (!el) {
        return;
    }
    e.preventDefault();
    const tab = el.getAttribute("data-open-auth") || "login";
    openAuthModal(tab === "register" || tab === "reset" ? tab : "login");
}

const langMenuOpen = ref(false);
const langWrapRef = ref(null);
const userMenuOpen = ref(false);
const userMenuWrapRef = ref(null);
const headerContainerRef = ref(null);
const headerBarRef = ref(null);
const navListRef = ref(null);
const logoRef = ref(null);

const { gsap, context } = useGsap();
/** Pinned bar uses the real Vue-managed `#header` (no jQuery clone). */
const headerPinned = ref(false);
/** Second phase: slide/visibility in (mirrors theme `#header.cloned` unsticky → sticky). */
const headerPinnedVisible = ref(false);
const scrollPinSpacerPx = ref(0);

let scrollPinRaf = 0;
let scrollPinAnimToken = 0;
let onScrollPinnedBound = null;
let onResizePinnedBound = null;

const themeUrl = computed(() => page.props.theme_url || "");
const auth = computed(() => page.props.auth);

const isAdmin = computed(() => auth.value?.type === "admin");

const profileHref = computed(() => {
    if (isAdmin.value) {
        return route("admin.profile.edit");
    }
    return route("home");
});

const mediaData = computed(() => page.props.globals.media || {});
const logoUrl = computed(() => {
    const m = mediaData.value;
    if (props.transparentNavbar && !headerPinned.value) {
        return m.white_logo || m.black_logo || "";
    }

    return m.black_logo || m.white_logo || "";
});

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

function isDesktopNavViewport() {
    return window.matchMedia("(min-width: 1025px)").matches;
}

function playNavbarEnterAnimation() {
    if (prefersReducedMotion()) {
        return;
    }

    const list = navListRef.value;
    const logo = logoRef.value;
    const header = headerContainerRef.value;
    if (!list || !header) {
        return;
    }

    const navItems = list.querySelectorAll(":scope > li.imas-nav-item");
    const actions = header.querySelectorAll(".imas-header-action");
    const isDesktop = isDesktopNavViewport();
    const isRtl =
        document.documentElement.getAttribute("dir") === "rtl" ||
        document.documentElement.dir === "rtl";

    context(() => {
        const tl = gsap.timeline({ defaults: { ease: "power2.out" } });

        if (logo) {
            tl.fromTo(
                logo,
                { opacity: 0, x: isRtl ? 16 : -16 },
                { opacity: 1, x: 0, duration: 0.5 },
                0,
            );
        }

        if (isDesktop && navItems.length) {
            tl.fromTo(
                navItems,
                { opacity: 0, y: -20 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.45,
                    stagger: 0.06,
                },
                logo ? 0.1 : 0,
            );
        }

        if (isDesktop && actions.length) {
            tl.fromTo(
                actions,
                { opacity: 0, x: isRtl ? -16 : 16 },
                {
                    opacity: 1,
                    x: 0,
                    duration: 0.45,
                    stagger: 0.08,
                },
                logo ? 0.14 : 0.08,
            );
        }
    }, headerContainerRef);
}

function playMobileNavEnterAnimation() {
    if (prefersReducedMotion()) {
        return;
    }

    const $ = window.jQuery;
    if (!$) {
        return;
    }

    const items = $(".mmenu-init").find("li.imas-nav-item").toArray();
    if (!items.length) {
        return;
    }

    const isRtl =
        document.documentElement.getAttribute("dir") === "rtl" ||
        document.documentElement.dir === "rtl";

    gsap.fromTo(
        items,
        {
            opacity: 0,
            x: isRtl ? 20 : -20,
        },
        {
            opacity: 1,
            x: 0,
            duration: 0.4,
            stagger: 0.05,
            ease: "power2.out",
            overwrite: "auto",
        },
    );
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
    if (langMenuOpen.value) {
        userMenuOpen.value = false;
    }
}

function toggleUserMenu() {
    userMenuOpen.value = !userMenuOpen.value;
    if (userMenuOpen.value) {
        langMenuOpen.value = false;
    }
}

function closeHeaderDropdownsOnOutsideClick(event) {
    const langEl = langWrapRef.value;
    if (langEl && langMenuOpen.value && !langEl.contains(event.target)) {
        langMenuOpen.value = false;
    }
    const userEl = userMenuWrapRef.value;
    if (userEl && userMenuOpen.value && !userEl.contains(event.target)) {
        userMenuOpen.value = false;
    }
}

function switchLocale(url) {
    langMenuOpen.value = false;
    router.visit(url, { preserveScroll: true });
}

function logout() {
    userMenuOpen.value = false;
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
            playMobileNavEnterAnimation();
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

/** Remove theme / legacy jQuery header clones (they duplicate DOM without Vue bindings). */
function removeLegacyHeaderClones() {
    const $ = window.jQuery;
    if ($) {
        $("#header.cloned").remove();
        $("#navigation.style-2.cloned").remove();
        return;
    }
    document.querySelectorAll("#header.cloned").forEach((el) => el.remove());
    document
        .querySelectorAll("#navigation.style-2.cloned")
        .forEach((el) => el.remove());
}

function updateScrollPinnedHeader() {
    const bar = headerBarRef.value;
    if (!bar) {
        return;
    }
    const h = bar.offsetHeight || 0;
    const threshold = Math.max(h * 2, 1);
    const next = window.scrollY >= threshold;

    if (next === headerPinned.value) {
        if (next) {
            scrollPinSpacerPx.value = h;
        }
        return;
    }

    if (next) {
        scrollPinAnimToken += 1;
        const token = scrollPinAnimToken;
        headerPinned.value = true;
        scrollPinSpacerPx.value = h;
        if (prefersReducedMotion()) {
            headerPinnedVisible.value = true;
            return;
        }
        headerPinnedVisible.value = false;
        nextTick(() => {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    if (scrollPinAnimToken !== token || !headerPinned.value) {
                        return;
                    }
                    headerPinnedVisible.value = true;
                });
            });
        });
    } else {
        scrollPinAnimToken += 1;
        headerPinnedVisible.value = false;
        headerPinned.value = false;
        scrollPinSpacerPx.value = 0;
    }
}

function scheduleScrollPinnedUpdate() {
    if (scrollPinRaf) {
        return;
    }
    scrollPinRaf = requestAnimationFrame(() => {
        scrollPinRaf = 0;
        updateScrollPinnedHeader();
    });
}

function initScrollPinnedHeader() {
    removeLegacyHeaderClones();
    onScrollPinnedBound = () => scheduleScrollPinnedUpdate();
    onResizePinnedBound = () => scheduleScrollPinnedUpdate();
    window.addEventListener("scroll", onScrollPinnedBound, { passive: true });
    window.addEventListener("resize", onResizePinnedBound);
    scheduleScrollPinnedUpdate();
}

function teardownScrollPinnedHeader() {
    if (onScrollPinnedBound) {
        window.removeEventListener("scroll", onScrollPinnedBound);
        onScrollPinnedBound = null;
    }
    if (onResizePinnedBound) {
        window.removeEventListener("resize", onResizePinnedBound);
        onResizePinnedBound = null;
    }
    if (scrollPinRaf) {
        cancelAnimationFrame(scrollPinRaf);
        scrollPinRaf = 0;
    }
    scrollPinAnimToken += 1;
    headerPinnedVisible.value = false;
    headerPinned.value = false;
    scrollPinSpacerPx.value = 0;
    removeLegacyHeaderClones();
}

function reinitHeaderChromeForLocale() {
    langMenuOpen.value = false;
    userMenuOpen.value = false;
    nextTick(() => {
        teardownScrollPinnedHeader();
        teardownMobileMenuMmenu();
        initScrollPinnedHeader();
        initMobileMenuMmenu();
        playNavbarEnterAnimation();
    });
}

watch(
    () => page.props.locale,
    () => reinitHeaderChromeForLocale(),
);

watch(
    () => props.transparentNavbar,
    () => reinitHeaderChromeForLocale(),
);

watch(
    () => props.navLinks,
    () => {
        nextTick(() => playNavbarEnterAnimation());
    },
    { deep: true },
);

onMounted(() => {
    document.addEventListener("click", closeHeaderDropdownsOnOutsideClick);
    document.addEventListener("click", onDelegatedOpenAuth, true);

    nextTick(() => {
        initScrollPinnedHeader();
        initMobileMenuMmenu();
        playNavbarEnterAnimation();
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
    document.removeEventListener("click", closeHeaderDropdownsOnOutsideClick);
    document.removeEventListener("click", onDelegatedOpenAuth, true);

    teardownScrollPinnedHeader();
    teardownMobileMenuMmenu();

    const $ = window.jQuery;
    if ($) {
        $(window).off("resize.imasMmenu");
    }
});
</script>

<style scoped>
.show-lang span strong {
    padding: 0 !important;
}
.lang-switch-flag--trigger {
    font-size: 1.3em !important;
}
.show-lang .fa-caret-down.arrlan {
    left: 90px;
}
/* Override Find Houses `float: left` + `text-align: left` on dropdown rows so LTR/RTL both align to inline-start. */
.header-user-menu.user-menu .imas-user-menu-dropdown {
    text-align: start !important;
    /* margin-left: 50px !important; */
}

.header-user-menu.user-menu .imas-user-menu-dropdown > li {
    float: none !important;
    text-align: start !important;
    width: 100% !important;
}

.header-user-menu.user-menu .imas-user-menu-dropdown__item {
    box-sizing: border-box;
    color: #696969;
    cursor: pointer;
    display: block !important;
    float: none !important;
    font: inherit;
    line-height: 22px;
    padding: 5px 15px;
    text-align: start !important;
    text-decoration: none;
    width: 100% !important;
}

.header-user-menu.user-menu a.imas-user-menu-dropdown__item:hover {
    color: #66676b;
}

.dropdown-logout {
    background: none;
    border: 0;
}

.header-user-menu.user-menu
    .imas-user-menu-dropdown__item.dropdown-logout:hover {
    color: #66676b;
}

/* styles.css loads after menu.css and forces .header-user-menu ul hidden — re-open when .active (Vue toggle). */
.header-user-menu.user-menu.active > ul {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translate3d(0, 0, 0) !important;
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

/* html[dir="rtl"] .header-user-menu.user-menu.add{
    margin-left: 50px !important;
} */
@media (min-width: 1200px) {
    .left-side {
        width: 870px !important;
    }
    .UserMenu {
        margin: 0 50px !important;
    }
}

/* GSAP sets initial state; keep items visible when motion is reduced */
@media (prefers-reduced-motion: reduce) {
    :deep(.imas-nav-item),
    :deep(.imas-header-action),
    :deep(#logo) {
    /* text-align: start !important; */
        opacity: 1 !important;
        transform: none !important;
    }
}
</style>
