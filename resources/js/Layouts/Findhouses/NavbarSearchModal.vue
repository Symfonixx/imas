<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="imas-navbar-search"
            role="dialog"
            aria-modal="true"
            :aria-label="trans('Search')"
        >
            <div
                class="imas-navbar-search__bar"
                :class="{ 'imas-navbar-search__bar--rtl': isRtl }"
            >
                <div class="imas-navbar-search__brand">
                    <img
                        v-if="logoUrl"
                        :src="logoUrl"
                        alt=""
                        class="imas-navbar-search__logo"
                    />
                </div>

                <button
                    type="button"
                    class="imas-navbar-search__back"
                    :aria-label="trans('auth_modal.close')"
                    @click="close"
                >
                    <i
                        class="fa"
                        :class="isRtl ? 'fa-arrow-left' : 'fa-arrow-left'"
                        aria-hidden="true"
                    ></i>
                </button>

                <form
                    class="imas-navbar-search__form"
                    @submit.prevent="submitSearch"
                >
                    <label class="sr-only" for="imas-navbar-search-input">{{
                        trans("Search")
                    }}</label>
                    <div class="imas-navbar-search__input-wrap">
                        <input
                            id="imas-navbar-search-input"
                            ref="inputRef"
                            v-model="searchQuery"
                            type="search"
                            class="imas-navbar-search__input"
                            :placeholder="trans('Search')"
                            autocomplete="off"
                            maxlength="255"
                            enterkeyhint="search"
                        />
                        <button
                            v-if="searchQuery"
                            type="button"
                            class="imas-navbar-search__clear"
                            :aria-label="trans('auth_modal.close')"
                            @click="clearInput"
                        >
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                    <button
                        type="submit"
                        class="imas-navbar-search__submit"
                        :aria-label="trans('Search')"
                    >
                        <i class="fa fa-search" aria-hidden="true"></i>
                    </button>
                </form>
            </div>

            <button
                type="button"
                class="imas-navbar-search__backdrop"
                :aria-label="trans('auth_modal.close')"
                tabindex="-1"
                @click="close"
            ></button>
        </div>
    </Teleport>
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
import { router, usePage } from "@inertiajs/vue3";
import { localizedRoute } from "@/utils/localizedRoute.js";

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:open"]);

const page = usePage();
const inputRef = ref(null);
const searchQuery = ref("");

const activeLocale = computed(() => page.props.locale || "en");

const isRtl = computed(
    () => page.props.text_direction === "rtl" || page.props.locale === "ar",
);

const mediaData = computed(() => page.props.globals?.media || {});
const logoUrl = computed(() => {
    const m = mediaData.value;
    return m.transparent_logo || m.white_logo || "";
});

function trans(key) {
    return page.props.translations[key] || key;
}

function close() {
    emit("update:open", false);
}

function readQueryFromUrl() {
    if (typeof window === "undefined") {
        return "";
    }
    try {
        const params = new URLSearchParams(window.location.search);
        return (params.get("q") || "").trim();
    } catch {
        return "";
    }
}

function isPropertyIndexPath() {
    if (typeof window === "undefined") {
        return false;
    }
    try {
        const indexPath =
            new URL(
                localizedRoute(
                    "property.index",
                    {},
                    activeLocale.value,
                    "/property",
                ),
                window.location.origin,
            ).pathname.replace(/\/+$/, "") || "/";
        const currentPath = window.location.pathname.replace(/\/+$/, "") || "/";
        return currentPath === indexPath;
    } catch {
        return false;
    }
}

function propertyIndexHref() {
    return localizedRoute(
        "property.index",
        {},
        activeLocale.value,
        "/property",
    );
}

function readCurrentListingParams() {
    const params = {};
    if (typeof window === "undefined" || !isPropertyIndexPath()) {
        return params;
    }

    try {
        const sp = new URLSearchParams(window.location.search);
        const keys = [
            "sort",
            "property_type_id",
            "min_price",
            "max_price",
            "min_area",
            "max_area",
        ];

        for (const key of keys) {
            const value = sp.get(key);
            if (value !== null && value !== "") {
                params[key] = value;
            }
        }

        const locationIds = sp.getAll("location_id[]");
        if (locationIds.length > 0) {
            params.location_id = locationIds;
        } else {
            const singleLocation = sp.get("location_id");
            if (singleLocation !== null && singleLocation !== "") {
                params.location_id = singleLocation;
            }
        }

        const unitIds = sp.getAll("project_unit_type_id[]");
        if (unitIds.length > 0) {
            params.project_unit_type_id = unitIds;
        }
    } catch {
        /* ignore malformed query */
    }

    return params;
}

function clearInput() {
    searchQuery.value = "";
    nextTick(() => inputRef.value?.focus());
}

function submitSearch() {
    const q = searchQuery.value.trim();
    const activeQ = readQueryFromUrl();
    const indexHref = propertyIndexHref();

    if (!q) {
        if (!activeQ) {
            if (isPropertyIndexPath()) {
                close();
            } else {
                inputRef.value?.focus();
            }
            return;
        }

        router.get(
            indexHref,
            { ...readCurrentListingParams(), page: 1 },
            {
                preserveState: false,
                preserveScroll: false,
            },
        );
        close();
        return;
    }

    router.get(
        indexHref,
        { ...readCurrentListingParams(), q, page: 1 },
        {
            preserveState: false,
            preserveScroll: false,
        },
    );
    close();
}

function onKeydown(e) {
    if (e.key === "Escape" && props.open) {
        e.preventDefault();
        close();
    }
}

function setBodyScrollLock(locked) {
    document.documentElement.classList.toggle("hid-body", locked);
    document.body.classList.toggle("hid-body", locked);
}

watch(
    () => props.open,
    async (isOpen) => {
        setBodyScrollLock(!!isOpen);
        if (!isOpen) {
            return;
        }
        searchQuery.value = isPropertyIndexPath() ? readQueryFromUrl() : "";
        await nextTick();
        inputRef.value?.focus();
        inputRef.value?.select?.();
    },
);

onMounted(() => {
    document.addEventListener("keydown", onKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener("keydown", onKeydown);
    setBodyScrollLock(false);
});
</script>

<style scoped>
.imas-navbar-search {
    position: fixed;
    inset: 0;
    z-index: var(--z-header-search, 100105);
    display: flex;
    flex-direction: column;
}

.imas-navbar-search__backdrop {
    flex: 1;
    border: 0;
    margin: 0;
    padding: 0;
    background: var(--overlay);
    cursor: pointer;
}

.imas-navbar-search__bar {
    position: relative;
    z-index: 2;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    min-height: 68px;
    padding: 12px 20px;
    background: var(--brand-navy-hover);
    border-bottom: 1px solid var(--border);
    box-shadow: var(--shadow-md);
}

.imas-navbar-search__bar--rtl {
    flex-direction: row-reverse;
}

.imas-navbar-search__brand {
    flex-shrink: 0;
    display: flex;
    align-items: center;
}

.imas-navbar-search__logo {
    display: block;
    max-height: 40px;
    width: auto;
    max-width: 140px;
    object-fit: contain;
}

.imas-navbar-search__back {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: var(--text-dim);
    cursor: pointer;
    transition:
        color 0.2s ease,
        background-color 0.2s ease;
}

.imas-navbar-search__back:hover {
    color: var(--brand-gold);
    background: rgba(217, 168, 0, 0.12);
}

.imas-navbar-search__back:focus-visible {
    outline: none;
    box-shadow: var(--ring);
}

.imas-navbar-search__form {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.imas-navbar-search__bar--rtl .imas-navbar-search__form {
    flex-direction: row-reverse;
}

.imas-navbar-search__bar--rtl .imas-navbar-search__input {
    text-align: right;
}

.imas-navbar-search__input-wrap {
    position: relative;
    flex: 1 1 auto;
    min-width: 0;
    display: flex;
    align-items: center;
}

.imas-navbar-search__input {
    flex: 1 1 auto;
    width: 100%;
    min-width: 0;
    height: 44px;
    padding: 0 18px;
    padding-inline-end: 40px;
    border: 1px solid var(--border);
    border-radius: 999px;
    background: var(--surface-2);
    color: var(--text);
    font: inherit;
    font-size: var(--text-base);
    line-height: 1.4;
}

.imas-navbar-search__clear {
    position: absolute;
    inset-inline-end: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    transition:
        color 0.2s ease,
        background-color 0.2s ease;
}

.imas-navbar-search__clear:hover {
    color: var(--text);
    background: rgba(255, 255, 255, 0.06);
}

.imas-navbar-search__clear:focus-visible {
    outline: none;
    box-shadow: var(--ring);
}

.imas-navbar-search__input::placeholder {
    color: var(--text-muted);
}

.imas-navbar-search__input:focus {
    outline: none;
    border-color: var(--brand-gold);
    box-shadow: var(--ring);
}

.imas-navbar-search__input::-webkit-search-cancel-button,
.imas-navbar-search__input::-webkit-search-decoration,
.imas-navbar-search__input::-webkit-search-results-button,
.imas-navbar-search__input::-webkit-search-results-decoration {
    -webkit-appearance: none;
    appearance: none;
    display: none;
}

.imas-navbar-search__input::-ms-clear,
.imas-navbar-search__input::-ms-reveal {
    display: none;
    width: 0;
    height: 0;
}

.imas-navbar-search__submit {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: var(--brand-gold);
    cursor: pointer;
    transition:
        color 0.2s ease,
        background-color 0.2s ease;
}

.imas-navbar-search__submit:hover {
    color: var(--text-on-gold);
    background: var(--brand-gold);
}

.imas-navbar-search__submit:focus-visible {
    outline: none;
    box-shadow: var(--ring);
}

.imas-navbar-search__submit .fa-search {
    font-size: 18px;
    line-height: 1;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

@media (max-width: 560px) {
    .imas-navbar-search__bar {
        gap: 8px;
        padding: 10px 12px;
    }

    .imas-navbar-search__logo {
        max-width: 100px;
        max-height: 32px;
    }
}
</style>
