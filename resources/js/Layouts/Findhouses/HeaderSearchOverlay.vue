<template>
    <Teleport v-if="mounted" to="body">
        <Transition name="imas-header-search-fade">
            <div
                v-if="modelValue"
                class="imas-header-search"
                role="dialog"
                aria-modal="true"
                :aria-label="searchAriaLabel"
                @keydown.esc="close"
            >
                <div class="imas-header-search__bar">
                    <div class="container container-header imas-header-search__bar-inner">
                        <Link
                            :href="homeHref"
                            class="imas-header-search__logo"
                            @click="close"
                        >
                            <img
                                v-if="logoUrl"
                                :src="logoUrl"
                                alt=""
                            />
                        </Link>

                        <button
                            type="button"
                            class="imas-header-search__back"
                            :aria-label="closeLabel"
                            @click="close"
                        >
                            <i
                                class="fa fa-arrow-left"
                                aria-hidden="true"
                            ></i>
                        </button>

                        <form
                            class="imas-header-search__form"
                            @submit.prevent="submitSearch"
                        >
                            <input
                                ref="inputRef"
                                v-model="query"
                                type="search"
                                class="imas-header-search__input"
                                :placeholder="searchPlaceholder"
                                autocomplete="off"
                                enterkeyhint="search"
                            />
                        </form>

                        <button
                            type="button"
                            class="imas-header-search__submit"
                            :aria-label="searchLabel"
                            @click="submitSearch"
                        >
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <button
                    type="button"
                    class="imas-header-search__backdrop"
                    :aria-label="closeLabel"
                    tabindex="-1"
                    @click="close"
                />
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { localizedRoute } from "@/utils/localizedRoute.js";

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    logoUrl: { type: String, default: "" },
});

const emit = defineEmits(["update:modelValue"]);

const page = usePage();
const activeLocale = computed(() => page.props.locale || "en");
const query = ref("");
const inputRef = ref(null);

function trans(key) {
    return page.props.translations?.[key] || key;
}

const closeLabel = computed(
    () => trans("Close") || trans("close") || "Close",
);

const searchLabel = computed(
    () => trans("listing_page.search") || trans("Search") || "Search",
);

const searchPlaceholder = computed(
    () =>
        trans("listing_page.enter_keyword") ||
        trans("Search") ||
        "Search",
);

const searchAriaLabel = computed(() => searchLabel.value);

const homeHref = computed(() =>
    localizedRoute("home", {}, activeLocale.value, "/"),
);

function close() {
    emit("update:modelValue", false);
}

function lockBodyScroll(lock) {
    if (typeof document === "undefined") {
        return;
    }
    document.body.style.overflow = lock ? "hidden" : "";
}

function submitSearch() {
    const q = query.value.trim();
    if (!q) {
        inputRef.value?.focus();
        return;
    }

    const params = { q, page: 1 };

    close();
    query.value = "";

    router.get(
        localizedRoute("property.index", {}, activeLocale.value, "/property"),
        params,
        {
            preserveState: false,
            preserveScroll: false,
        },
    );
}

watch(
    () => props.modelValue,
    (open) => {
        lockBodyScroll(open);
        if (open) {
            query.value = "";
            nextTick(() => inputRef.value?.focus());
        }
    },
);

/**
 * Gate `<Teleport to="body">` until after mount so Inertia SSR (which drops
 * teleport-to-body content) and the client's first render stay identical.
 */
const mounted = ref(false);

onMounted(() => {
    mounted.value = true;
});

onBeforeUnmount(() => {
    lockBodyScroll(false);
});
</script>

<style scoped lang="scss">
.imas-header-search {
    position: fixed;
    inset: 0;
    z-index: var(--z-header-search, 100105);
    display: flex;
    flex-direction: column;
}

.imas-header-search__bar {
    position: relative;
    z-index: 2;
    flex-shrink: 0;
    background: #fff;
    box-shadow: 0 2px 16px rgba(26, 42, 74, 0.1);
}

.imas-header-search__bar-inner {
    display: flex;
    align-items: center;
    gap: 1rem;
    min-height: 88px;
    padding-top: 1rem;
    padding-bottom: 1rem;
}

.imas-header-search__logo {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    line-height: 0;
}

.imas-header-search__logo img {
    max-height: 52px;
    width: auto;
    max-width: 160px;
    object-fit: contain;
}

.imas-header-search__back {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border: 0;
    padding: 0;
    background: transparent;
    color: var(--brand-navy, #1a2a4a);
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition:
        color 0.2s ease,
        background-color 0.2s ease;
}

.imas-header-search__back:hover,
.imas-header-search__back:focus {
    color: var(--brand-gold, #d9a800);
    background: rgba(26, 42, 74, 0.06);
}

:global(html[dir="rtl"]) .imas-header-search__back .fa-arrow-left {
    transform: scaleX(-1);
}

.imas-header-search__form {
    flex: 1;
    min-width: 0;
}

.imas-header-search__input {
    width: 100%;
    height: 52px;
    padding: 0 1.5rem;
    border: 1px solid rgba(26, 42, 74, 0.18);
    border-radius: 999px;
    background: #fff;
    color: var(--brand-navy, #1a2a4a);
    font-size: 1rem;
    line-height: 1.4;
    outline: none;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.imas-header-search__input::placeholder {
    color: var(--color-text-muted, #5a6578);
    opacity: 0.85;
}

.imas-header-search__input:focus {
    border-color: var(--brand-gold, #d9a800);
    box-shadow: 0 0 0 3px rgba(217, 168, 0, 0.2);
}

.imas-header-search__submit {
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    border: 0;
    padding: 0;
    background: transparent;
    color: var(--brand-navy, #1a2a4a);
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition:
        color 0.2s ease,
        background-color 0.2s ease;
}

.imas-header-search__submit:hover,
.imas-header-search__submit:focus {
    color: var(--brand-gold, #d9a800);
    background: rgba(26, 42, 74, 0.06);
}

.imas-header-search__backdrop {
    flex: 1;
    border: 0;
    margin: 0;
    padding: 0;
    background: rgba(26, 42, 74, 0.55);
    cursor: pointer;
}

.imas-header-search-fade-enter-active,
.imas-header-search-fade-leave-active {
    transition: opacity 0.2s ease;
}

.imas-header-search-fade-enter-active .imas-header-search__bar,
.imas-header-search-fade-leave-active .imas-header-search__bar {
    transition: transform 0.22s ease;
}

.imas-header-search-fade-enter-from,
.imas-header-search-fade-leave-to {
    opacity: 0;
}

.imas-header-search-fade-enter-from .imas-header-search__bar,
.imas-header-search-fade-leave-to .imas-header-search__bar {
    transform: translateY(-12px);
}
</style>
