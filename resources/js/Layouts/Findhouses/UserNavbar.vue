<template>
    <header
        ref="headerContainerRef"
        id="header-container"
        class="header imas-nav-shell"
        :class="[
            transparentNavbar ? 'head-tr' : 'imas-navbar-solid',
            { 'imas-header-scroll-pinned': headerPinned },
        ]"
    >
        <div
            ref="headerBarRef"
            id="header"
            class="imas-nav imas-nav__bar bottom"
            :class="{
                'imas-scroll-pinned': headerPinned,
                'imas-scroll-pinned--in': headerPinned && headerPinnedVisible,
            }"
        >
            <div class="container imas-nav__container">
                <div id="logo" ref="logoRef" class="imas-nav__logo">
                    <Link :href="homeHref" class="imas-nav__logo-link">
                        <img
                            :src="logoUrl"
                            :data-sticky-logo="logoUrl"
                            alt=""
                        />
                        <span class="imas-brand-text">
                            <span class="website-name">{{ websiteName }}</span>
                            <span class="website-slogan">{{ websiteSlogan }}</span>
                        </span>
                    </Link>
                </div>

                <nav id="navigation" class="imas-nav__menu style-1">
                    <ul id="responsive" ref="navListRef">
                        <li
                            v-for="item in navLinks"
                            :key="item.key"
                            class="imas-nav-item"
                            :class="{
                                'has-submenu': item?.children?.length,
                            }"
                        >
                            <Link
                                v-if="item.href"
                                :href="item.href"
                                :class="{ active: isNavLinkActive(item) }"
                            >
                                {{ item.label ?? trans(item.key) }}
                            </Link>
                            <a
                                v-else
                                href="#"
                                :class="{ active: isNavLinkActive(item) }"
                                @click.prevent
                            >
                                {{ item.label ?? trans(item.key) }}
                            </a>

                            <ul
                                v-if="item?.children?.length"
                                class="imas-nav__submenu"
                            >
                                <li
                                    v-for="child in item.children"
                                    :key="`${item.key}-${child.key}`"
                                    class="imas-nav__submenu-item"
                                >
                                    <Link
                                        :href="child.href"
                                        class="imas-nav__submenu-link"
                                        :class="{
                                            active: isNavLinkActive(child),
                                        }"
                                    >
                                        {{ child.label ?? trans(child.key) }}
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li v-if="!auth && mounted" class="imas-mmenu-only">
                            <a
                                href="#"
                                class="imas-auth-nav-link"
                                data-open-auth="login"
                                >{{ trans("Login") }}</a
                            >
                        </li>
                        <li v-if="!auth && mounted" class="imas-mmenu-only">
                            <a
                                href="#"
                                class="imas-auth-nav-link"
                                data-open-auth="register"
                                >{{ trans("Register") }}</a
                            >
                        </li>
                    </ul>
                </nav>

                <div class="imas-nav__end">
                    <div
                        class="imas-nav__actions right"
                        :class="{ 'imas-nav__actions--rtl': isRtl }"
                    >
                        <div
                            class="header-user-menu user-menu add imas-nav__lang imas-header-action"
                        >
                            <div
                                ref="langWrapRef"
                                class="lang-wrap"
                                :class="{ 'lang-wrap--open': langMenuOpen }"
                            >
                                <button
                                    type="button"
                                    class="show-lang imas-nav__lang-trigger"
                                    :aria-expanded="langMenuOpen"
                                    aria-haspopup="listbox"
                                    :aria-label="trans('Language')"
                                    @click.stop="toggleLangMenu"
                                >
                                    <span class="show-lang-trigger-inner">
                                        <span
                                            v-if="
                                                flagCountryClass(currentLocale)
                                            "
                                            class="fi lang-switch-flag lang-switch-flag--trigger"
                                            :class="
                                                flagCountryClass(currentLocale)
                                            "
                                            aria-hidden="true"
                                        ></span>
                                        <!-- <strong>{{ localeBadge }}</strong> -->
                                    </span>
                                    <i class="fa fa-caret-down arrlan"></i>
                                </button>
                                <ul
                                    class="lang-tooltip lang-action no-list-style"
                                    role="listbox"
                                >
                                    <li
                                        v-for="loc in localeSwitcher"
                                        :key="loc.code"
                                    >
                                        <a
                                            href="#"
                                            class="lang-switch-row"
                                            :class="{
                                                'current-lan':
                                                    loc.code === currentLocale,
                                            }"
                                            role="option"
                                            :aria-selected="
                                                loc.code === currentLocale
                                            "
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
                                            <span class="mx-2">{{
                                                loc.native
                                            }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="imas-nav__search imas-header-action"
                            :aria-label="trans('Search')"
                            :title="trans('Search')"
                            @click="openSearchModal"
                        >
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </button>
                        <Link
                            v-if="auth && mounted"
                            :href="favoritesHref"
                            class="imas-nav__favorites imas-header-action"
                            :class="{ 'is-active': favoritesNavActive }"
                            :aria-label="
                                trans('properties.favorite_properties')
                            "
                            :title="trans('properties.favorite_properties')"
                        >
                            <i class="fa fa-heart" aria-hidden="true"></i>
                        </Link>
                        <div
                            v-if="mounted"
                            ref="userMenuWrapRef"
                            class="header-user-menu user-menu add UserMenu imas-header-action"
                            :class="{ active: userMenuOpen }"
                        >
                            <template v-if="auth">
                                <button
                                    type="button"
                                    class="header-user-name imas-nav__account-trigger"
                                    :class="{
                                        'imas-nav__account-trigger--rtl': isRtl,
                                    }"
                                    :aria-expanded="userMenuOpen"
                                    aria-haspopup="true"
                                    :aria-label="trans('Account menu')"
                                    @click.stop="toggleUserMenu"
                                >
                                    <span class="imas-nav__avatar">
                                        <img :src="auth.avatar" alt="" />
                                    </span>
                                    <span
                                        class="imas-nav__account-text imas-nav__desktop-only"
                                    >
                                        {{ accountGreeting }}
                                    </span>
                                    <i
                                        class="fa fa-caret-down imas-nav__account-caret imas-nav__desktop-only"
                                        aria-hidden="true"
                                    ></i>
                                </button>
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
                                    <li v-if="isAdmin">
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
                            </template>

                            <div v-else class="imas-nav__sign-in imas-header-action">
                                <a
                                    href="#"
                                    class="imas-nav__sign-in-link show-reg-form modal-open"
                                    data-open-auth="login"
                                    >{{ trans("Sign In") }}</a
                                >
                            </div>
                        </div>
                    </div>

                    <div class="mmenu-trigger imas-nav__mmenu">
                        <button
                            class="hamburger hamburger--collapse"
                            type="button"
                            :aria-label="trans('Menu')"
                        >
                            <span class="hamburger-box">
                                <span class="hamburger-inner"></span>
                            </span>
                        </button>
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
        <NavbarSearchModal v-model:open="searchModalOpen" />
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
import { IMAS_OPEN_AUTH_EVENT } from "@/composables/useOpenAuthModal";
import { useGsap } from "@/composables/useGsap";
import { prefersReducedMotion } from "@/plugins/gsap";
import AuthModal from "./AuthModal.vue";
import NavbarSearchModal from "./NavbarSearchModal.vue";
import { localizedRoute } from "@/utils/localizedRoute.js";

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

const activeLocale = computed(() => page.props.locale || "en");

const homeHref = computed(() =>
    localizedRoute("home", {}, activeLocale.value, "/"),
);

const authModalOpen = ref(false);
const authStartTab = ref("login");
const searchModalOpen = ref(false);

/**
 * Client-only reveal flag. Stays `false` during SSR *and* the initial client
 * hydration render so both trees match, then flips to `true` after mount to show
 * elements that must only exist in the browser (mmenu auth links, favorites,
 * user menu). Avoids the "server rendered Comment / expected li" hydration
 * mismatch caused by calling `isBrowser()` directly in `v-if`.
 */
const mounted = ref(false);

function openSearchModal() {
    authModalOpen.value = false;
    searchModalOpen.value = true;
    mmenuApi?.close?.();
}

function normalizeAuthTab(tab) {
    if (tab === "register" || tab === "reset" || tab === "forgot") {
        return tab;
    }
    return "login";
}

function openAuthModal(tab = "login") {
    searchModalOpen.value = false;
    authStartTab.value = normalizeAuthTab(tab);
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
    openAuthModal(el.getAttribute("data-open-auth") || "login");
}

function onImasOpenAuthEvent(e) {
    openAuthModal(e.detail?.tab || "login");
}

/** Open auth modal when landing from email links or Fortify flash after reset. */
function openAuthFromCurrentContext() {
    const path = window.location.pathname || "";
    if (/\/reset-password\//.test(path)) {
        openAuthModal("reset");
        return;
    }
    if (/\/forgot-password\/?$/.test(path)) {
        openAuthModal("forgot");
        return;
    }
    if (page.props.flash?.status && !authModalOpen.value) {
        openAuthModal("login");
    }
}

watch(
    () => page.props.flash?.status,
    (status, prev) => {
        if (!status || status === prev || authModalOpen.value) {
            return;
        }
        const path = window.location.pathname || "";
        if (/\/reset-password\//.test(path) || /\/forgot-password\/?$/.test(path)) {
            return;
        }
        openAuthModal("login");
    },
);

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

const websiteName = computed(() => {
    const name =
        page.props.globals?.seo?.website_name ||
        page.props.appName ||
        "";
    return String(name).toUpperCase();
});
const websiteSlogan = "MOST ACCURATE SOLUTIONS";
const themeUrl = computed(() => page.props.theme_url || "");
const auth = computed(() => page.props.auth);

const isRtl = computed(
    () => page.props.text_direction === "rtl" || page.props.locale === "ar",
);

const accountGreeting = computed(() => {
    const hello = trans("Hi");
    const name = String(auth.value?.nav_display_name ?? "").trim();
    return name ? `${hello} ${name}` : hello;
});

const isAdmin = computed(() => auth.value?.type === "admin");

const profileHref = computed(() => {
    if (isAdmin.value) {
        return route("admin.profile.edit");
    }
    return homeHref.value;
});

const favoritesHref = computed(() =>
    localizedRoute(
        "property.favorites",
        {},
        activeLocale.value,
        "/favorite-properties",
    ),
);

const favoritesNavActive = computed(() => {
    const current = normalizePath(page.url);
    const target = normalizePath(favoritesHref.value);
    return Boolean(target) && current === target;
});

const mediaData = computed(() => page.props.globals.media || {});
const logoUrl = computed(() => {
    const m = mediaData.value;
    return m.transparent_logo || m.white_logo || "";
});

function normalizePath(url) {
    if (typeof url !== "string" || url.trim() === "") {
        return "";
    }
    try {
        const base =
            typeof window !== "undefined"
                ? window.location.origin
                : "http://localhost";
        const path = new URL(url, base).pathname.replace(/\/+$/, "") || "/";
        return path;
    } catch {
        return url.split("?")[0].replace(/\/+$/, "") || "/";
    }
}

function isNavLinkActive(item) {
    if (!item?.href) {
        return false;
    }

    const current = normalizePath(page.url);
    const target = normalizePath(item.href);

    if (!target || target === "#") {
        return false;
    }

    if (current === target) {
        return true;
    }

    if (item.key === "navBar.Home") {
        try {
            if (typeof route === "function" && route().current?.("home")) {
                return true;
            }
        } catch {
            /* ignore */
        }
        try {
            if (typeof route === "function" && route().has?.("home")) {
                return current === normalizePath(route("home"));
            }
        } catch {
            /* ignore */
        }
    }

    if (target !== "/" && current.startsWith(`${target}/`)) {
        return true;
    }

    return false;
}

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
    return page.props.translations?.[key] ?? key;
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
 * Root panel navbar: translated title, theme chrome, close control.
 */
function customizeMmenuNavbar($) {
    const $page = $(".mm-page").first().length
        ? $(".mm-page").first()
        : $("#app");
    const pageId = $page.attr("id");
    if (!pageId) {
        return;
    }

    const isRtl =
        document.documentElement.getAttribute("dir") === "rtl" ||
        document.documentElement.dir === "rtl";
    const closeLabel = isRtl ? "إغلاق القائمة" : "Close menu";

    $(".mm-menu.mm-offcanvas").each(function () {
        const $menu = $(this);
        const $rootPanel = $menu.find("> .mm-panels > .mm-panel").first();
        const $navbar = $rootPanel.children(".mm-navbar").first();
        if (!$navbar.length) {
            return;
        }

        $navbar.find(".mm-title").text(trans("Menu"));

        if (!$navbar.find("a.mm-close").length) {
            $navbar.prepend(
                `<a class="mm-btn mm-close" href="#${pageId}" aria-label="${closeLabel}"></a>`,
            );
        }
    });
}

/** Login/Register rows are mobile-only; drop from drawer when session is active. */
function stripMmenuAuthLinks($) {
    $(".mmenu-init")
        .find("li.imas-mmenu-only")
        .has(".imas-auth-nav-link")
        .remove();
}

function initMobileMenuMmenu() {
    const $ = window.jQuery;
    if (!$ || !$.fn?.mmenu) {
        return;
    }

    const wi = $(window).width();
    if (wi > 1024) {
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
        .removeClass("style-1 style-2 imas-nav__menu")
        .find("ul")
        .removeAttr("id");

    $(".mmenu-init").find(".container").removeClass("container");

    /* Language switcher lives in the top bar on mobile — drop drawer copy if present */
    $(".mmenu-init")
        .find("li.imas-mmenu-only")
        .has(".lang-switch-row")
        .remove();

    if (auth.value) {
        stripMmenuAuthLinks($);
    }

    const isRtl =
        document.documentElement.getAttribute("dir") === "rtl" ||
        document.documentElement.dir === "rtl";

    $(".mmenu-init").mmenu(
        {
            counters: true,
            navbar: {
                title: trans("Menu"),
            },
        },
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

    customizeMmenuNavbar($);

    mmenuApi.bind("open:finish", () => {
        setTimeout(() => {
            $icon.addClass("is-active");
            customizeMmenuNavbar($);
            if (auth.value) {
                stripMmenuAuthLinks($);
            }
            playMobileNavEnterAnimation();
        });
    });

    mmenuApi.bind("close:finish", () => {
        setTimeout(() => {
            $icon.removeClass("is-active");
        });
    });

    $(".mm-next").addClass("mm-fullsubopen");

    setTimeout(() => customizeMmenuNavbar($), 0);
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

watch(
    () => auth.value,
    () => {
        nextTick(() => {
            const $ = window.jQuery;
            if ($ && $(window).width() <= 1024) {
                initMobileMenuMmenu();
            }
        });
    },
);

onMounted(() => {
    mounted.value = true;

    document.addEventListener(IMAS_OPEN_AUTH_EVENT, onImasOpenAuthEvent);
    document.addEventListener("click", closeHeaderDropdownsOnOutsideClick);
    document.addEventListener("click", onDelegatedOpenAuth, true);

    nextTick(() => {
        initScrollPinnedHeader();
        initMobileMenuMmenu();
        playNavbarEnterAnimation();
        openAuthFromCurrentContext();
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
    document.removeEventListener(IMAS_OPEN_AUTH_EVENT, onImasOpenAuthEvent);
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
.imas-nav__logo-link {
    display: inline-flex;
    align-items: center;
    /* gap: 10px; */
    text-decoration: none;
}

.imas-nav__logo-link img {
    display: block;
    max-height: 42px;
    width: auto;
    max-width: 160px;
    object-fit: contain;
}

.imas-nav__sign-in {
    display: inline-flex;
    align-items: center;
}

.imas-nav__sign-in a {
    font-size: 14px;
    font-weight: 500;
    font-family: var(--font-body);
    line-height: 1.4;
    color: var(--text-dim, #9aa6bd);
    text-decoration: none;
    transition: color 0.2s ease;
}

.imas-nav__sign-in a:hover {
    color: var(--brand-gold, #d9a800);
}

.imas-nav__favorites,
.imas-nav__search {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    padding: 0;
    margin: 0;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: var(--text-dim, #9aa6bd);
    text-decoration: none;
    cursor: pointer;
    transition:
        color 0.2s ease,
        background-color 0.2s ease;
}

.imas-nav__favorites .fa-heart,
.imas-nav__search .fa-search {
    font-size: 16px;
    line-height: 1;
}

.imas-nav__favorites:hover,
.imas-nav__favorites.is-active,
.imas-nav__search:hover {
    color: var(--brand-gold, #d9a800);
    background: rgba(217, 168, 0, 0.12);
}

.imas-nav__search:focus-visible {
    outline: none;
    box-shadow: var(--ring);
}

:deep(.imas-nav__lang-trigger.show-lang) {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px;
    width: auto !important;
    float: none !important;
    padding: 0 !important;
    margin: 0 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    font-family: var(--font-body) !important;
    line-height: 1.4 !important;
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    outline: none !important;
    -webkit-appearance: none;
    appearance: none;
}

:deep(.imas-nav__lang-trigger.show-lang:focus),
:deep(.imas-nav__lang-trigger.show-lang:focus-visible),
:deep(.imas-nav__lang-trigger.show-lang:active) {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

:deep(.imas-nav__lang-trigger .show-lang span) {
    float: none !important;
    margin: 0 !important;
}

:deep(.imas-nav__lang-trigger .show-lang span strong) {
    padding: 0 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
}

.lang-switch-flag--trigger {
    font-size: 14px !important;
    line-height: 1;
}

.show-lang-trigger-inner .fa-globe {
    font-size: 14px;
    color: var(--text-dim, #9aa6bd);
}

:deep(.imas-nav__lang-trigger .fa-caret-down.arrlan) {
    position: static !important;
    margin-inline-start: 2px;
    color: var(--text-dim, #9aa6bd);
    font-size: 12px;
    line-height: 1;
}

:deep(.header-user-menu.imas-header-action) {
    float: none !important;
    top: 0 !important;
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
}

:deep(.imas-nav__account-trigger.header-user-name) {
    display: inline-flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 8px;
    float: none !important;
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    outline: none !important;
    -webkit-appearance: none;
    appearance: none;
    font-size: 14px !important;
    font-weight: 500 !important;
    font-family: var(--font-body) !important;
    line-height: 1.4 !important;
    max-height: none;
}

:deep(.imas-nav__account-trigger.header-user-name:focus),
:deep(.imas-nav__account-trigger.header-user-name:focus-visible),
:deep(.imas-nav__account-trigger.header-user-name:active) {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

:deep(.imas-nav__account-trigger.header-user-name:focus-visible) {
    box-shadow: var(--ring, 0 0 0 3px rgba(217, 168, 0, 0.35)) !important;
}

:deep(.imas-nav__account-trigger.header-user-name::-moz-focus-inner) {
    border: 0;
    padding: 0;
}

html[dir="rtl"] :deep(.imas-nav__account-trigger--rtl.header-user-name) {
    flex-direction: row !important;
    direction: rtl;
    unicode-bidi: isolate;
}

:deep(.imas-nav__account-trigger.header-user-name::before) {
    display: none !important;
    content: none !important;
}

@media (min-width: 1025px) {
    :deep(.imas-nav__account-trigger > span.imas-nav__account-text) {
        position: static !important;
        display: block !important;
        width: auto !important;
        height: auto !important;
        left: auto !important;
        top: auto !important;
        order: 2;
        flex: 0 1 auto;
    }
}

:deep(.imas-nav__account-trigger--rtl > span.imas-nav__account-text) {
    order: 1;
}

:deep(.imas-nav__account-trigger > span.imas-nav__avatar) {
    position: relative !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    left: auto !important;
    top: auto !important;
    order: 1;
    flex-shrink: 0;
    overflow: visible;
}

:deep(.imas-nav__account-trigger--rtl > span.imas-nav__avatar) {
    order: 2;
}

:deep(.imas-nav__avatar img) {
    width: 32px !important;
    height: 32px !important;
    border-radius: 50% !important;
    border: 2px solid rgba(255, 255, 255, 0.12) !important;
    box-shadow: none !important;
    object-fit: cover;
    display: block;
}

:deep(.imas-nav__account-trigger > span.imas-nav__avatar::after) {
    position: absolute;
    content: "";
    height: 10px;
    width: 10px;
    bottom: 0;
    right: 0;
    background-color: #38b653;
    border: 2px solid var(--brand-navy-hover, #0a1526);
    border-radius: 50%;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
}

:deep(.imas-nav__account-trigger > span.imas-nav__account-text::after) {
    display: none !important;
    content: none !important;
}

.imas-nav__account-text {
    white-space: nowrap;
    color: inherit;
}

.imas-nav__account-caret {
    font-size: 12px;
    color: var(--text-dim, #9aa6bd);
    flex-shrink: 0;
    order: 3;
}

@media (min-width: 1025px) {
    :deep(.imas-nav__actions--rtl) {
        flex-direction: row-reverse !important;
        gap: 10px !important;
    }
}

:deep(.imas-nav__account-trigger:hover) {
    color: var(--brand-gold, #d9a800) !important;
}

:deep(.imas-nav__account-trigger:hover .imas-nav__account-caret) {
    color: var(--brand-gold, #d9a800);
}

/* Blogs / Pages flyouts — match account & language dropdown panels */
:deep(#navigation.style-1 ul.imas-nav__submenu) {
    background: var(--surface, #101d36) !important;
    border: 1px solid var(--border, rgba(217, 168, 0, 0.18)) !important;
    border-radius: 6px !important;
    box-shadow: var(--shadow-md, 0 8px 24px rgba(0, 0, 0, 0.4)) !important;
    padding: 8px 0 !important;
    text-align: start !important;
}

:deep(#navigation.style-1 ul.imas-nav__submenu > li) {
    border: none !important;
    background: transparent !important;
    text-align: start !important;
}

:deep(#navigation.style-1 ul.imas-nav__submenu .imas-nav__submenu-link) {
    color: var(--text-dim, #9aa6bd) !important;
    padding-block: 8px !important;
    padding-inline: 14px !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    text-transform: capitalize !important;
    text-align: start !important;
    transition:
        color 0.2s ease,
        background-color 0.2s ease;
}

:deep(#navigation.style-1 ul.imas-nav__submenu .imas-nav__submenu-link:after) {
    display: none !important;
    content: none !important;
}

:deep(#navigation.style-1 ul.imas-nav__submenu .imas-nav__submenu-link:hover),
:deep(#navigation.style-1 ul.imas-nav__submenu .imas-nav__submenu-link.active) {
    color: var(--brand-gold, #d9a800) !important;
    background: var(--surface-2, #16264a) !important;
}

:deep(
    #navigation.style-1 > ul#responsive > li.imas-nav-item.has-submenu:hover
) {
    background: transparent !important;
}

/* Override Find Houses `float: left` + light dropdown chrome — dark theme panels */
.header-user-menu.user-menu .imas-user-menu-dropdown,
:deep(.lang-wrap .lang-tooltip) {
    text-align: start !important;
    background: var(--surface, #101d36) !important;
    border: 1px solid var(--border, rgba(217, 168, 0, 0.18)) !important;
    border-radius: 6px !important;
    box-shadow: var(--shadow-md, 0 8px 24px rgba(0, 0, 0, 0.4)) !important;
}

.header-user-menu.user-menu .imas-user-menu-dropdown > li,
:deep(.lang-wrap .lang-tooltip > li) {
    float: none !important;
    text-align: start !important;
    width: 100% !important;
    background: transparent !important;
}

.header-user-menu.user-menu .imas-user-menu-dropdown__item,
:deep(.lang-tooltip .lang-switch-row) {
    box-sizing: border-box;
    color: var(--text-dim, #9aa6bd) !important;
    cursor: pointer;
    display: block !important;
    float: none !important;
    font: inherit;
    line-height: 22px;
    padding: 8px 14px;
    text-align: start !important;
    text-decoration: none;
    width: 100% !important;
    transition:
        color 0.2s ease,
        background-color 0.2s ease;
}

.header-user-menu.user-menu a.imas-user-menu-dropdown__item:hover,
.header-user-menu.user-menu button.imas-user-menu-dropdown__item:hover,
:deep(.lang-tooltip .lang-switch-row:hover),
:deep(.lang-tooltip a.current-lan) {
    color: var(--brand-gold, #d9a800) !important;
    background: var(--surface-2, #16264a) !important;
}

.dropdown-logout {
    background: none;
    border: 0;
}

/* styles.css loads after menu.css and forces .header-user-menu ul hidden — re-open when .active (Vue toggle). */
.header-user-menu.user-menu.active > ul.imas-user-menu-dropdown {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translate3d(0, 0, 0) !important;
    background: var(--surface, #101d36) !important;
}

/* Theme leaves .lang-tooltip permanently hidden; toggle visibility in JS. */
.lang-wrap .lang-tooltip {
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
    background: var(--surface, #101d36) !important;
}

.lang-wrap.lang-wrap--open .lang-tooltip {
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
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

.imas-brand-text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
    min-width: 0;
}

.website-name {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--brand-gold);
    text-decoration: none;
    transition: color 0.2s ease;
    line-height: 1.15;
}

.website-slogan {
    font-size: 8px;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--text-dim);
    line-height: 1.3;
    white-space: nowrap;
}

@media (max-width: 1024px) {
    .imas-brand-text {
        display: none !important;
    }

    .imas-nav__logo-link img {
        max-height: 38px;
        max-width: 120px;
    }

    :deep(.imas-nav__desktop-only) {
        display: none !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        pointer-events: none !important;
    }

    :deep(.imas-nav__account-trigger.header-user-name) {
        gap: 0 !important;
    }

    :deep(.imas-nav__lang .lang-tooltip) {
        margin: 0 !important;
        padding: 8px 0 !important;
    }

    :deep(.show-lang-trigger-inner strong) {
        display: none !important;
    }

    .imas-nav__sign-in {
        display: inline-flex !important;
    }

    .imas-nav__sign-in-link {
        padding: 6px 12px;
        font-size: 13px;
        color: var(--text, #eef2f8) !important;
        border: 1px solid var(--border, rgba(217, 168, 0, 0.18));
        border-radius: 4px;
        white-space: nowrap;
    }

    .imas-nav__sign-in-link:hover {
        color: var(--brand-gold, #d9a800) !important;
        border-color: var(--border-strong, rgba(217, 168, 0, 0.35));
        background: var(--surface-2, #16264a);
    }

    .header-user-menu.user-menu.active > ul.imas-user-menu-dropdown,
    .lang-wrap.lang-wrap--open :deep(.lang-tooltip) {
        z-index: 10010 !important;
    }
}

.header-user-menu.user-menu {
    padding: 0 !important;
}

@media (min-width: 1025px) {
    .header-user-menu.user-menu {
        padding-left: 15px !important;
    }
}
.show-lang span strong {
    padding-left: 0 !important;
}
</style>

<style>
/* mmenu drawer clones #navigation outside the scoped tree (≤1024px) */
@media (max-width: 1024px) {
    .mm-menu .mm-listview > li > a,
    .mm-menu .mm-listview > li > span,
    .mm-menu .mm-listview a:not(.mm-next),
    .mm-menu .mm-listview .imas-auth-nav-link {
        text-transform: capitalize !important;
    }
}
</style>
