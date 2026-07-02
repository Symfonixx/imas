<template>
    <div
        class="imas-auth-phone-field"
        :class="{
            'imas-auth-phone-field--country-open': countryDropdownOpen,
            'is-invalid': invalid,
        }"
        dir="ltr"
    >
        <div ref="countryDropdownRoot" class="imas-auth-country-select-shell">
            <button
                :id="countryTriggerId"
                type="button"
                class="imas-auth-country-trigger"
                :aria-expanded="countryDropdownOpen"
                aria-haspopup="listbox"
                :aria-label="countrySelectAriaLabel"
                @click.stop="countryDropdownOpen = !countryDropdownOpen"
            >
                <img
                    v-if="selectedCountry?.flag"
                    class="imas-auth-country-flag-img"
                    :src="selectedCountry.flag"
                    alt=""
                    width="22"
                    height="16"
                    decoding="async"
                    loading="lazy"
                />
                <span class="imas-auth-country-code-label" aria-hidden="true"
                    >+{{ displayCallingCode(selectedCountry?.phone_code) }}</span
                >
            </button>
            <div
                v-show="countryDropdownOpen"
                class="imas-auth-country-dropdown-panel"
                @click.stop
            >
                <div class="imas-auth-country-dropdown-search-wrap text-start">
                    <input
                        ref="countrySearchInput"
                        v-model="countrySearchQuery"
                        type="search"
                        enterkeyhint="search"
                        autocomplete="off"
                        autocorrect="off"
                        spellcheck="false"
                        class="imas-auth-country-dropdown-search"
                        :placeholder="trans('Search')"
                        :aria-label="trans('Search')"
                        @keydown.escape.prevent="countryDropdownOpen = false"
                    />
                </div>
                <ul
                    class="imas-auth-country-dropdown-scroll"
                    role="listbox"
                    tabindex="-1"
                >
                    <li
                        v-if="countriesWithPhoneFiltered.length === 0"
                        class="imas-auth-country-option imas-auth-country-option--empty"
                        aria-live="polite"
                    >
                        {{ trans("auth_modal.country_code_search_empty") }}
                    </li>
                    <li
                        v-for="c in countriesWithPhoneFiltered"
                        :key="c.id"
                        role="option"
                        class="imas-auth-country-option"
                        :class="{
                            'imas-auth-country-option--selected':
                                c.id === countryId,
                        }"
                        :aria-selected="c.id === countryId"
                        @click.prevent="selectCountry(c.id)"
                    >
                        <img
                            v-if="c.flag"
                            class="imas-auth-country-flag-img imas-auth-country-flag-img--option"
                            :src="c.flag"
                            alt=""
                            width="22"
                            height="16"
                            decoding="async"
                            loading="lazy"
                        />
                        <span class="imas-auth-country-option-code"
                            >+{{ displayCallingCode(c.phone_code) }}</span
                        >
                    </li>
                </ul>
            </div>
        </div>
        <span class="imas-auth-phone-sep" aria-hidden="true"></span>
        <input
            :id="inputId"
            v-model="mobileLocal"
            type="tel"
            inputmode="numeric"
            autocomplete="tel-national"
            class="imas-auth-phone-input"
            :required="required"
            :placeholder="placeholder"
            @focus="$event.target.select()"
        />
    </div>
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
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
    inputId: {
        type: String,
        required: true,
    },
    placeholder: {
        type: String,
        default: "",
    },
    invalid: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue"]);

const page = usePage();

const countryId = ref(null);
const mobileLocal = ref("");
const countryDropdownOpen = ref(false);
const countryDropdownRoot = ref(null);
const countrySearchQuery = ref("");
const countrySearchInput = ref(null);

const countryTriggerId = computed(() => `${props.inputId}-country-code`);

const countries = computed(() => page.props.globals?.countries ?? []);

const countriesWithPhone = computed(() => {
    const list = countries.value.filter(
        (c) => String(c.phone_code ?? "").trim() !== "",
    );
    return list.length ? list : countries.value;
});

const selectedCountry = computed(() => {
    const list = countriesWithPhone.value;
    const id = countryId.value;
    if (id == null || !list.length) {
        return null;
    }
    return list.find((c) => c.id === id) ?? null;
});

const countriesWithPhoneFiltered = computed(() => {
    const list = countriesWithPhone.value;
    const raw = countrySearchQuery.value.trim();
    if (!raw) {
        return list;
    }

    const qDigits = digitsOnly(raw);
    const alphaQuery = raw
        .replace(/[\d+()\-\s]/g, "")
        .trim()
        .toLowerCase();

    return list.filter((c) => {
        const codeDigits = digitsOnly(c.phone_code);
        if (qDigits.length > 0 && codeDigits.startsWith(qDigits)) {
            return true;
        }
        if (alphaQuery.length > 0) {
            const name = String(c.name ?? "").toLowerCase();
            const iso = String(c.iso_code_2 ?? "").toLowerCase();
            return name.includes(alphaQuery) || iso.includes(alphaQuery);
        }
        return false;
    });
});

const countrySelectAriaLabel = computed(() => {
    const list = countriesWithPhone.value;
    const c = list.find((x) => x.id === countryId.value);
    const prefix = trans("auth_modal.country_calling_code");
    if (!c) {
        return prefix;
    }
    const cc = displayCallingCode(c.phone_code);
    const iso = String(c.iso_code_2 ?? "")
        .trim()
        .toUpperCase();
    return `${prefix}: +${cc}${iso ? `, ${iso}` : ""}`;
});

watch(countryDropdownOpen, (open) => {
    if (!open) {
        countrySearchQuery.value = "";
        return;
    }
    nextTick(() => {
        countrySearchInput.value?.focus?.();
    });
});

function pickDefaultCountry() {
    const list = countriesWithPhone.value;
    if (!list.length) {
        countryId.value = null;
        return;
    }
    if (
        countryId.value != null &&
        list.some((c) => c.id === countryId.value)
    ) {
        return;
    }
    const prefer =
        { tr: "TR", en: "US", ar: "SA" }[String(page.props.locale)] ?? "TR";
    const found = list.find((c) => c.iso_code_2 === prefer);
    countryId.value = (found ?? list[0]).id;
}

watch(countriesWithPhone, pickDefaultCountry, { immediate: true });

function digitsOnly(s) {
    return String(s ?? "").replace(/\D/g, "");
}

function displayCallingCode(phoneCode) {
    const d = digitsOnly(phoneCode);
    return d || "—";
}

function normalizeNationalDigits(raw) {
    let x = digitsOnly(raw);
    while (x.startsWith("0")) {
        x = x.slice(1);
    }
    return x;
}

function buildMobilePayload() {
    const list = countriesWithPhone.value;
    const c = list.find((x) => x.id === countryId.value);
    const cc = c ? digitsOnly(c.phone_code) : "";
    const local = normalizeNationalDigits(mobileLocal.value);
    return cc + local;
}

function syncModelValue() {
    const local = normalizeNationalDigits(mobileLocal.value);
    if (!local) {
        emit("update:modelValue", "");
        return;
    }
    emit("update:modelValue", buildMobilePayload());
}

watch([mobileLocal, countryId], syncModelValue);

watch(
    () => props.modelValue,
    (value) => {
        if (!value) {
            mobileLocal.value = "";
        }
    },
);

function selectCountry(id) {
    countryId.value = id;
    countryDropdownOpen.value = false;
}

function onCountryDocPointerDown(e) {
    if (!countryDropdownOpen.value) {
        return;
    }
    const root = countryDropdownRoot.value;
    if (root && !root.contains(e.target)) {
        countryDropdownOpen.value = false;
    }
}

function onCountryDocKeydown(e) {
    if (e.key === "Escape") {
        countryDropdownOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener("pointerdown", onCountryDocPointerDown);
    document.addEventListener("keydown", onCountryDocKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener("pointerdown", onCountryDocPointerDown);
    document.removeEventListener("keydown", onCountryDocKeydown);
});

function trans(key) {
    return page.props.translations[key] || key;
}
</script>

<style scoped>
.imas-auth-phone-field {
    display: flex;
    align-items: stretch;
    width: 100%;
    border: 1px solid var(--border);
    background: var(--surface-2);
    border-radius: 6px;
    overflow: visible;
}

.imas-auth-phone-field.is-invalid {
    border-color: var(--danger);
}

.imas-auth-phone-field.is-invalid:focus-within {
    border-color: var(--danger);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--danger) 35%, transparent);
}

.imas-auth-phone-field .imas-auth-country-select-shell {
    position: relative;
    flex: 0 0 auto;
    align-self: stretch;
    min-width: 5.5rem;
    max-width: 46%;
    margin: 0;
}

.imas-auth-country-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    height: 100%;
    min-height: 100%;
    padding: 0.65rem 1.75rem 0.65rem 0.75rem;
    margin: 0;
    border: none;
    border-radius: 0;
    background-color: transparent;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239aa6bd' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 10px;
    cursor: pointer;
    text-align: start;
    color: var(--text);
    -webkit-appearance: none;
    appearance: none;
}

.imas-auth-country-trigger:focus {
    outline: none;
}

.imas-auth-country-trigger:focus-visible {
    box-shadow: inset var(--ring);
}

.imas-auth-country-dropdown-panel {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    min-width: 100%;
    max-height: min(280px, 48vh);
    display: flex;
    flex-direction: column;
    margin: 0;
    padding: 0;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 6px;
    box-shadow: var(--shadow-md);
    z-index: 10050;
    overflow: hidden;
}

.imas-auth-country-dropdown-search-wrap {
    flex-shrink: 0;
    padding: 6px;
    border-bottom: 1px solid var(--divider);
    background: var(--surface);
}

.imas-auth-country-dropdown-search {
    display: block;
    width: 100%;
    box-sizing: border-box;
    padding: 0.5rem 0.65rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--text);
    background: var(--surface-2);
    -webkit-appearance: none;
    appearance: none;
}

.imas-auth-country-dropdown-search::placeholder {
    color: var(--text-muted);
}

.imas-auth-country-dropdown-search:focus {
    outline: none;
    border-color: var(--brand-gold);
    box-shadow: var(--ring);
}

.imas-auth-country-dropdown-scroll {
    flex: 1;
    min-height: 0;
    margin: 0;
    padding: 6px 0;
    list-style: none;
    overflow-x: hidden;
    overflow-y: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.imas-auth-country-dropdown-scroll::-webkit-scrollbar {
    width: 0;
    height: 0;
    display: none;
}

.imas-auth-country-option--empty {
    cursor: default;
    color: var(--text-muted);
    font-weight: 400;
}

.imas-auth-country-option--empty:hover {
    background: transparent;
}

.imas-auth-country-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0.5rem 0.75rem;
    margin: 0;
    cursor: pointer;
    color: var(--text);
    font-size: var(--text-sm);
    font-weight: 500;
    line-height: 1;
}

.imas-auth-country-option:hover {
    background: var(--surface-3);
}

.imas-auth-country-option--selected {
    background: var(--surface-2);
}

.imas-auth-country-option-code {
    flex-shrink: 0;
    color: var(--text-dim);
}

.imas-auth-country-flag-img--option {
    pointer-events: none;
}

.imas-auth-country-flag-img {
    flex-shrink: 0;
    width: 22px;
    height: 16px;
    object-fit: cover;
    border-radius: 2px;
    pointer-events: none;
}

.imas-auth-country-code-label {
    flex-shrink: 0;
    font-size: var(--text-sm);
    line-height: 1;
    font-weight: 500;
    color: var(--text-dim);
    pointer-events: none;
}

.imas-auth-phone-field:focus-within {
    border-color: var(--brand-gold);
    box-shadow: var(--ring);
}

.imas-auth-phone-field:focus-within.is-invalid {
    border-color: var(--danger);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--danger) 35%, transparent);
}

.imas-auth-phone-field--country-open {
    z-index: 10040;
    position: relative;
}

.imas-auth-phone-sep {
    width: 1px;
    align-self: stretch;
    background: var(--divider);
    flex-shrink: 0;
}

.imas-auth-phone-field .imas-auth-phone-input {
    flex: 1 1 120px;
    min-width: 0;
    float: none;
    width: auto;
    margin: 0 !important;
    padding: 0.65rem 0.85rem;
    border: none;
    border-radius: 0;
    background: transparent;
    color: var(--text);
    font-size: var(--text-sm);
    -webkit-appearance: none;
    appearance: none;
}

.imas-auth-phone-field .imas-auth-phone-input:focus {
    outline: none;
    box-shadow: none;
}

.imas-auth-phone-field .imas-auth-phone-input::placeholder {
    color: var(--text-muted);
}
</style>
