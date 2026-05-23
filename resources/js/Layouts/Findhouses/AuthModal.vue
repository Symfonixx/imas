<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="login-and-register-form modal imas-auth-modal"
            role="dialog"
            aria-modal="true"
            :aria-label="trans('auth_modal.dialog_label')"
        >
            <div class="main-overlay" tabindex="-1" @click="closeModal"></div>
            <div class="main-register-holder">
                <div class="main-register fl-wrap">
                    <div
                        class="close-reg"
                        role="button"
                        tabindex="0"
                        :aria-label="trans('auth_modal.close')"
                        @click="closeModal"
                        @keydown.enter.prevent="closeModal"
                        @keydown.space.prevent="closeModal"
                    >
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-center">
                        {{ trans("auth_modal.welcome_prefix") }}
                        <span
                            class="imas-auth-modal__brand"
                            v-html="welcomeBrandHtml"
                        ></span>
                    </h3>

                    <!-- Recover password (theme: lost password flow)  / forgot password -->
                    <div v-if="authSubview === 'forgot'" class="custom-form">
                        <p class="mb-3 text-start px-0"></p>
                        <a
                            href="#"
                            class="imas-auth-modal__back text-start"
                            @click.prevent="authSubview = null"
                        >
                            <i
                                class="fa fa-angle-left fa-lg imas-auth-modal__back-icon"
                                aria-hidden="true"
                            ></i>
                            <span class="imas-auth-modal__back-label">{{
                                trans("auth_modal.back_to_login")
                            }}</span>
                        </a>
                        <form
                            @submit.prevent="submitForgot"
                            class="forgot-password-form"
                        >
                            <div>
                                <label for="imas-auth-forgot-email"
                                    >{{ trans("Email") }} *</label
                                >
                                <input
                                    id="imas-auth-forgot-email"
                                    v-model="forgotForm.email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    @focus="$event.target.select()"
                                />
                                <span
                                    v-if="forgotForm.errors.email"
                                    class="imas-auth-field-error"
                                    >{{ forgotForm.errors.email }}</span
                                >
                                <button
                                    type="submit"
                                    class="log-submit-btn"
                                    :disabled="forgotForm.processing"
                                >
                                    <span>{{
                                        trans("Send Email Verification")
                                    }}</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Login | Register | Reset tabs -->
                    <div v-else id="tabs-container">
                        <ul class="tabs-menu">
                            <li :class="{ current: activeMainTab === 'login' }">
                                <a
                                    href="#tab-imas-login"
                                    @click.prevent="activeMainTab = 'login'"
                                    >{{ trans("Login") }}</a
                                >
                            </li>
                            <li
                                :class="{
                                    current: activeMainTab === 'register',
                                }"
                            >
                                <a
                                    href="#tab-imas-register"
                                    @click.prevent="activeMainTab = 'register'"
                                    >{{ trans("Register") }}</a
                                >
                            </li>
                            <!-- <li :class="{ current: activeMainTab === 'reset' }">
                                <a
                                    href="#tab-imas-reset"
                                    @click.prevent="activeMainTab = 'reset'"
                                    >{{ trans("Reset Password") }}</a
                                >
                            </li> -->
                        </ul>
                        <div class="tab">
                            <div
                                id="tab-imas-login"
                                class="tab-contents"
                                :class="{
                                    'imas-auth-tab--active':
                                        activeMainTab === 'login',
                                }"
                            >
                                <div class="custom-form">
                                    <form @submit.prevent="submitLogin">
                                        <label for="imas-auth-login-email"
                                            >{{ trans("Email") }} *</label
                                        >
                                        <input
                                            id="imas-auth-login-email"
                                            v-model="loginForm.email"
                                            type="email"
                                            autocomplete="username"
                                            required
                                            @focus="$event.target.select()"
                                        />
                                        <span
                                            v-if="loginForm.errors.email"
                                            class="imas-auth-field-error"
                                            >{{ loginForm.errors.email }}</span
                                        >
                                        <label for="imas-auth-login-password"
                                            >{{ trans("Password") }} *</label
                                        >
                                        <input
                                            id="imas-auth-login-password"
                                            v-model="loginForm.password"
                                            type="password"
                                            autocomplete="current-password"
                                            required
                                            @focus="$event.target.select()"
                                        />
                                        <span
                                            v-if="loginForm.errors.password"
                                            class="imas-auth-field-error"
                                            >{{
                                                loginForm.errors.password
                                            }}</span
                                        >
                                        <button
                                            type="submit"
                                            class="log-submit-btn"
                                            :disabled="loginForm.processing"
                                        >
                                            <span>{{ trans("Sign In") }}</span>
                                        </button>
                                        <div class="clearfix"></div>
                                        <div class="filter-tags">
                                            <input
                                                id="imas-auth-remember"
                                                v-model="loginForm.remember"
                                                type="checkbox"
                                                class="mx-2 remember-me-checkbox"
                                            />
                                            <label for="imas-auth-remember">{{
                                                trans("Remember Me")
                                            }}</label>
                                        </div>
                                    </form>
                                    <div class="lost_password">
                                        <a
                                            href="#"
                                            @click.prevent="
                                                authSubview = 'forgot'
                                            "
                                            >{{ trans("Forgot Password") }}</a
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="tab">
                                <div
                                    id="tab-imas-register"
                                    class="tab-contents"
                                    :class="{
                                        'imas-auth-tab--active':
                                            activeMainTab === 'register',
                                    }"
                                >
                                    <div class="custom-form main-register-form">
                                        <form @submit.prevent="submitRegister">
                                            <div class="imas-auth-form-field">
                                                <label for="imas-auth-reg-name"
                                                    >{{
                                                        trans("Name")
                                                    }}
                                                    *</label
                                                >
                                                <input
                                                    id="imas-auth-reg-name"
                                                    v-model="registerForm.name"
                                                    type="text"
                                                    autocomplete="name"
                                                    required
                                                    @focus="
                                                        $event.target.select()
                                                    "
                                                />
                                                <span
                                                    v-if="
                                                        registerForm.errors.name
                                                    "
                                                    class="imas-auth-field-error"
                                                    >{{
                                                        registerForm.errors.name
                                                    }}</span
                                                >
                                            </div>
                                            <div class="imas-auth-form-field">
                                                <label for="imas-auth-reg-email"
                                                    >{{
                                                        trans("Email")
                                                    }}
                                                    *</label
                                                >
                                                <input
                                                    id="imas-auth-reg-email"
                                                    v-model="registerForm.email"
                                                    type="email"
                                                    autocomplete="email"
                                                    required
                                                    @focus="
                                                        $event.target.select()
                                                    "
                                                />
                                                <span
                                                    v-if="
                                                        registerForm.errors
                                                            .email
                                                    "
                                                    class="imas-auth-field-error"
                                                    >{{
                                                        registerForm.errors
                                                            .email
                                                    }}</span
                                                >
                                            </div>
                                            <div class="imas-auth-form-field">
                                                <label
                                                    for="imas-auth-reg-mobile"
                                                    >{{
                                                        trans("Mobile")
                                                    }}
                                                    *</label
                                                >
                                                <div
                                                    class="imas-auth-phone-field"
                                                    :class="{
                                                        'imas-auth-phone-field--country-open':
                                                            registerCountryDropdownOpen,
                                                    }"
                                                    dir="ltr"
                                                >
                                                    <div
                                                        ref="registerCountryDropdownRoot"
                                                        class="imas-auth-country-select-shell"
                                                    >
                                                        <button
                                                            id="imas-auth-reg-country-code"
                                                            type="button"
                                                            class="imas-auth-country-trigger"
                                                            :aria-expanded="
                                                                registerCountryDropdownOpen
                                                            "
                                                            aria-haspopup="listbox"
                                                            :aria-label="
                                                                registerCountrySelectAriaLabel
                                                            "
                                                            @click.stop="
                                                                registerCountryDropdownOpen =
                                                                    !registerCountryDropdownOpen
                                                            "
                                                        >
                                                            <img
                                                                v-if="
                                                                    selectedRegisterCountry?.flag
                                                                "
                                                                class="imas-auth-country-flag-img"
                                                                :src="
                                                                    selectedRegisterCountry.flag
                                                                "
                                                                alt=""
                                                                width="22"
                                                                height="16"
                                                                decoding="async"
                                                                loading="lazy"
                                                            />
                                                            <span
                                                                class="imas-auth-country-code-label"
                                                                aria-hidden="true"
                                                                >+{{
                                                                    displayCallingCode(
                                                                        selectedRegisterCountry?.phone_code,
                                                                    )
                                                                }}</span
                                                            >
                                                        </button>
                                                        <div
                                                            v-show="
                                                                registerCountryDropdownOpen
                                                            "
                                                            class="imas-auth-country-dropdown-panel"
                                                            @click.stop
                                                        >
                                                            <div
                                                                class="imas-auth-country-dropdown-search-wrap text-start"
                                                            >
                                                                <input
                                                                    ref="registerCountrySearchInput"
                                                                    v-model="
                                                                        registerCountrySearchQuery
                                                                    "
                                                                    type="search"
                                                                    enterkeyhint="search"
                                                                    autocomplete="off"
                                                                    autocorrect="off"
                                                                    spellcheck="false"
                                                                    class="imas-auth-country-dropdown-search"
                                                                    :placeholder="
                                                                        trans(
                                                                            'Search',
                                                                        )
                                                                    "
                                                                    :aria-label="
                                                                        trans(
                                                                            'Search',
                                                                        )
                                                                    "
                                                                    @keydown.escape.prevent="
                                                                        registerCountryDropdownOpen = false
                                                                    "
                                                                />
                                                            </div>
                                                            <ul
                                                                class="imas-auth-country-dropdown-scroll"
                                                                role="listbox"
                                                                tabindex="-1"
                                                            >
                                                                <li
                                                                    v-if="
                                                                        countriesWithPhoneFiltered.length ===
                                                                        0
                                                                    "
                                                                    class="imas-auth-country-option imas-auth-country-option--empty"
                                                                    aria-live="polite"
                                                                >
                                                                    {{
                                                                        trans(
                                                                            "auth_modal.country_code_search_empty",
                                                                        )
                                                                    }}
                                                                </li>
                                                                <li
                                                                    v-for="c in countriesWithPhoneFiltered"
                                                                    :key="c.id"
                                                                    role="option"
                                                                    class="imas-auth-country-option"
                                                                    :class="{
                                                                        'imas-auth-country-option--selected':
                                                                            c.id ===
                                                                            registerCountryId,
                                                                    }"
                                                                    :aria-selected="
                                                                        c.id ===
                                                                        registerCountryId
                                                                    "
                                                                    @click.prevent="
                                                                        selectRegisterCountry(
                                                                            c.id,
                                                                        )
                                                                    "
                                                                >
                                                                    <img
                                                                        v-if="
                                                                            c.flag
                                                                        "
                                                                        class="imas-auth-country-flag-img imas-auth-country-flag-img--option"
                                                                        :src="
                                                                            c.flag
                                                                        "
                                                                        alt=""
                                                                        width="22"
                                                                        height="16"
                                                                        decoding="async"
                                                                        loading="lazy"
                                                                    />
                                                                    <span
                                                                        class="imas-auth-country-option-code"
                                                                        >+{{
                                                                            displayCallingCode(
                                                                                c.phone_code,
                                                                            )
                                                                        }}</span
                                                                    >
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="imas-auth-phone-sep"
                                                        aria-hidden="true"
                                                    ></span>
                                                    <input
                                                        id="imas-auth-reg-mobile"
                                                        v-model="
                                                            registerMobileLocal
                                                        "
                                                        type="tel"
                                                        inputmode="numeric"
                                                        autocomplete="tel-national"
                                                        class="imas-auth-phone-input"
                                                        required
                                                        :placeholder="
                                                            trans(
                                                                'auth_modal.mobile_national_placeholder',
                                                            )
                                                        "
                                                        @focus="
                                                            $event.target.select()
                                                        "
                                                    />
                                                </div>
                                                <span
                                                    v-if="
                                                        registerMobileClientError
                                                    "
                                                    class="imas-auth-field-error"
                                                    >{{
                                                        registerMobileClientError
                                                    }}</span
                                                >
                                                <span
                                                    v-if="
                                                        registerForm.errors
                                                            .mobile
                                                    "
                                                    class="imas-auth-field-error"
                                                    >{{
                                                        registerForm.errors
                                                            .mobile
                                                    }}</span
                                                >
                                            </div>
                                            <div class="imas-auth-form-field">
                                                <label
                                                    for="imas-auth-reg-password"
                                                    >{{
                                                        trans("Password")
                                                    }}
                                                    *</label
                                                >
                                                <input
                                                    id="imas-auth-reg-password"
                                                    v-model="
                                                        registerForm.password
                                                    "
                                                    type="password"
                                                    autocomplete="new-password"
                                                    required
                                                    @focus="
                                                        $event.target.select()
                                                    "
                                                />
                                                <span
                                                    v-if="
                                                        registerForm.errors
                                                            .password
                                                    "
                                                    class="imas-auth-field-error"
                                                    >{{
                                                        registerForm.errors
                                                            .password
                                                    }}</span
                                                >
                                            </div>
                                            <div class="imas-auth-form-field">
                                                <label
                                                    for="imas-auth-reg-password-confirmation"
                                                    >{{
                                                        trans(
                                                            "Confirm Password",
                                                        )
                                                    }}
                                                    *</label
                                                >
                                                <input
                                                    id="imas-auth-reg-password-confirmation"
                                                    v-model="
                                                        registerForm.password_confirmation
                                                    "
                                                    type="password"
                                                    autocomplete="new-password"
                                                    required
                                                    @focus="
                                                        $event.target.select()
                                                    "
                                                />
                                                <span
                                                    v-if="
                                                        registerForm.errors
                                                            .password_confirmation
                                                    "
                                                    class="imas-auth-field-error"
                                                    >{{
                                                        registerForm.errors
                                                            .password_confirmation
                                                    }}</span
                                                >
                                            </div>
                                            <div
                                                class="imas-auth-form-field imas-auth-form-field--actions"
                                            >
                                                <button
                                                    type="submit"
                                                    class="log-submit-btn"
                                                    :disabled="
                                                        registerForm.processing
                                                    "
                                                >
                                                    <span>{{
                                                        trans("Register")
                                                    }}</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="tab">
                                <div
                                    id="tab-imas-reset"
                                    class="tab-contents"
                                    :class="{
                                        'imas-auth-tab--active':
                                            activeMainTab === 'reset',
                                    }"
                                >
                                    <div class="custom-form">
                                        <p
                                            v-if="!resetToken"
                                            class="imas-auth-modal__hint"
                                        >
                                            {{ trans("auth_modal.reset_hint") }}
                                        </p>
                                        <form @submit.prevent="submitReset">
                                            <label for="imas-auth-reset-email"
                                                >{{ trans("Email") }} *</label
                                            >
                                            <input
                                                id="imas-auth-reset-email"
                                                v-model="resetForm.email"
                                                type="email"
                                                autocomplete="email"
                                                required
                                                @focus="$event.target.select()"
                                            />
                                            <span
                                                v-if="resetForm.errors.email"
                                                class="imas-auth-field-error"
                                                >{{
                                                    resetForm.errors.email
                                                }}</span
                                            >
                                            <label
                                                for="imas-auth-reset-password"
                                                >{{
                                                    trans("Password")
                                                }}
                                                *</label
                                            >
                                            <input
                                                id="imas-auth-reset-password"
                                                v-model="resetForm.password"
                                                type="password"
                                                autocomplete="new-password"
                                                required
                                                @focus="$event.target.select()"
                                            />
                                            <span
                                                v-if="resetForm.errors.password"
                                                class="imas-auth-field-error"
                                                >{{
                                                    resetForm.errors.password
                                                }}</span
                                            >
                                            <label
                                                for="imas-auth-reset-password-confirmation"
                                                >{{
                                                    trans("Confirm Password")
                                                }}
                                                *</label
                                            >
                                            <input
                                                id="imas-auth-reset-password-confirmation"
                                                v-model="
                                                    resetForm.password_confirmation
                                                "
                                                type="password"
                                                autocomplete="new-password"
                                                required
                                                @focus="$event.target.select()"
                                            />
                                            <span
                                                v-if="
                                                    resetForm.errors
                                                        .password_confirmation
                                                "
                                                class="imas-auth-field-error"
                                                >{{
                                                    resetForm.errors
                                                        .password_confirmation
                                                }}</span
                                            >
                                            <button
                                                type="submit"
                                                class="log-submit-btn"
                                                :disabled="
                                                    resetForm.processing ||
                                                    !resetToken
                                                "
                                            >
                                                <span>{{
                                                    trans("Reset Password")
                                                }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
import { useForm, usePage } from "@inertiajs/vue3";

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    startTab: {
        type: String,
        default: "login",
    },
});

const emit = defineEmits(["update:open"]);

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}

const authSubview = ref(null);
const activeMainTab = ref("login");
const resetToken = ref("");
const seo = computed(() => page.props.globals.seo || {});
const appName = computed(() => String(seo.value.main_title || ""));

/** Match theme emphasis: last word in `<strong>…</strong>` when possible */
const welcomeBrandHtml = computed(() => {
    const name = appName.value.trim();
    if (!name) {
        return "<strong></strong>";
    }
    const parts = name.split(/\s+/);
    if (parts.length >= 2) {
        const last = parts.pop();
        const rest = parts.join(" ");
        return `${escapeHtml(rest)} <strong>${escapeHtml(last)}</strong>`;
    }
    return `<strong>${escapeHtml(name)}</strong>`;
});

function escapeHtml(s) {
    return s
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

const loginForm = useForm({
    email: "",
    password: "",
    remember: false,
});

const registerForm = useForm({
    name: "",
    email: "",
    mobile: "",
    password: "",
    password_confirmation: "",
});

const registerCountryId = ref(null);
const registerMobileLocal = ref("");
const registerMobileClientError = ref("");
const registerCountryDropdownOpen = ref(false);
const registerCountryDropdownRoot = ref(null);
const registerCountrySearchQuery = ref("");
const registerCountrySearchInput = ref(null);

const countries = computed(() => page.props.globals?.countries ?? []);

const countriesWithPhone = computed(() => {
    const list = countries.value.filter(
        (c) => String(c.phone_code ?? "").trim() !== "",
    );
    return list.length ? list : countries.value;
});

const selectedRegisterCountry = computed(() => {
    const list = countriesWithPhone.value;
    const id = registerCountryId.value;
    if (id == null || !list.length) {
        return null;
    }
    return list.find((c) => c.id === id) ?? null;
});

const countriesWithPhoneFiltered = computed(() => {
    const list = countriesWithPhone.value;
    const raw = registerCountrySearchQuery.value.trim();
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

watch(registerCountryDropdownOpen, (open) => {
    if (!open) {
        registerCountrySearchQuery.value = "";
        return;
    }
    nextTick(() => {
        registerCountrySearchInput.value?.focus?.();
    });
});

function pickDefaultRegisterCountry() {
    const list = countriesWithPhone.value;
    if (!list.length) {
        registerCountryId.value = null;
        return;
    }
    if (
        registerCountryId.value != null &&
        list.some((c) => c.id === registerCountryId.value)
    ) {
        return;
    }
    const prefer =
        { tr: "TR", en: "US", ar: "SA" }[String(page.props.locale)] ?? "TR";
    const found = list.find((c) => c.iso_code_2 === prefer);
    registerCountryId.value = (found ?? list[0]).id;
}

watch(countriesWithPhone, pickDefaultRegisterCountry, { immediate: true });

function digitsOnly(s) {
    return String(s ?? "").replace(/\D/g, "");
}

function displayCallingCode(phoneCode) {
    const d = digitsOnly(phoneCode);
    return d || "—";
}

function selectRegisterCountry(id) {
    registerCountryId.value = id;
    registerCountryDropdownOpen.value = false;
}

function onRegisterCountryDocPointerDown(e) {
    if (!registerCountryDropdownOpen.value) {
        return;
    }
    const root = registerCountryDropdownRoot.value;
    if (root && !root.contains(e.target)) {
        registerCountryDropdownOpen.value = false;
    }
}

function onRegisterCountryDocKeydown(e) {
    if (e.key === "Escape") {
        registerCountryDropdownOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener("pointerdown", onRegisterCountryDocPointerDown);
    document.addEventListener("keydown", onRegisterCountryDocKeydown);
});

const registerCountrySelectAriaLabel = computed(() => {
    const list = countriesWithPhone.value;
    const c = list.find((x) => x.id === registerCountryId.value);
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

function normalizeNationalDigits(raw) {
    let x = digitsOnly(raw);
    while (x.startsWith("0")) {
        x = x.slice(1);
    }
    return x;
}

function buildRegisterMobilePayload() {
    const list = countriesWithPhone.value;
    const c = list.find((x) => x.id === registerCountryId.value);
    const cc = c ? digitsOnly(c.phone_code) : "";
    const local = normalizeNationalDigits(registerMobileLocal.value);
    return cc + local;
}

const forgotForm = useForm({
    email: "",
});

const resetForm = useForm({
    token: "",
    email: "",
    password: "",
    password_confirmation: "",
});

function extractResetTokenFromLocation() {
    const pathname = window.location.pathname || "";
    const match = pathname.match(/\/reset-password\/([^/?#]+)/);
    return match ? decodeURIComponent(match[1]) : "";
}

function extractResetEmailFromLocation() {
    return new URLSearchParams(window.location.search || "").get("email") || "";
}

function syncResetFromUrl() {
    const t = extractResetTokenFromLocation();
    resetToken.value = t;
    resetForm.token = t;
    const em = extractResetEmailFromLocation();
    if (em) {
        resetForm.email = em;
    }
}

function closeModal() {
    emit("update:open", false);
}

function resetAllForms() {
    loginForm.reset();
    registerForm.reset();
    forgotForm.reset();
    resetForm.reset();
    registerMobileLocal.value = "";
    registerMobileClientError.value = "";
    pickDefaultRegisterCountry();
    syncResetFromUrl();
}

watch(
    () => props.open,
    (isOpen) => {
        document.documentElement.classList.toggle("hid-body", !!isOpen);
        document.body.classList.toggle("hid-body", !!isOpen);
        if (!isOpen) {
            authSubview.value = null;
            registerCountryDropdownOpen.value = false;
            registerCountrySearchQuery.value = "";
            return;
        }
        authSubview.value = null;
        const start = props.startTab;
        if (start === "register") {
            activeMainTab.value = "register";
        } else if (start === "reset") {
            activeMainTab.value = "reset";
        } else {
            activeMainTab.value = "login";
        }
        syncResetFromUrl();
    },
);

function submitLogin() {
    loginForm.post(route("login"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            resetAllForms();
        },
    });
}

function submitRegister() {
    registerMobileClientError.value = "";
    const mobile = buildRegisterMobilePayload();
    if (mobile.length < 8 || mobile.length > 15) {
        registerMobileClientError.value = trans(
            "auth_modal.mobile_invalid_length",
        );
        return;
    }
    registerForm.mobile = mobile;
    registerForm.post(route("register"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            resetAllForms();
        },
    });
}

function submitForgot() {
    forgotForm.post(route("password.email"), {
        preserveScroll: true,
        onSuccess: () => {
            authSubview.value = null;
            activeMainTab.value = "login";
        },
    });
}

function submitReset() {
    if (!resetForm.token) {
        return;
    }
    resetForm.post(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            resetAllForms();
        },
    });
}

watch(
    () => activeMainTab.value,
    (tab) => {
        if (tab === "reset") {
            syncResetFromUrl();
        }
    },
);

onBeforeUnmount(() => {
    document.documentElement.classList.remove("hid-body");
    document.body.classList.remove("hid-body");
    document.removeEventListener(
        "pointerdown",
        onRegisterCountryDocPointerDown,
    );
    document.removeEventListener("keydown", onRegisterCountryDocKeydown);
});
</script>

<style scoped>
/* Theme default is `display: none` on `.login-and-register-form` (jQuery fades it in). */
.imas-auth-modal.login-and-register-form.modal {
    display: block !important;
    background: var(--overlay) !important;
    font-family: var(--font-app);
}

.imas-auth-modal.login-and-register-form .main-register-holder {
    margin: 4rem auto 2rem;
}

.imas-auth-modal.login-and-register-form .main-register {
    --imas-auth-content-width: 80%;
    background: var(--surface) !important;
    border: 1px solid var(--border);
    border-radius: 10px;
    box-shadow: var(--shadow-lg) !important;
    margin-bottom: 2rem;
    padding: 0 0 1rem;
}

.imas-auth-modal.login-and-register-form .main-register h3 {
    float: none;
    padding: 0.75rem 1.25rem;
    margin-bottom: 0;
    font-size: var(--text-md);
    font-weight: 600;
    color: var(--text-dim);
    border-bottom: 1px solid var(--divider);
    text-align: center;
}

.imas-auth-modal.login-and-register-form .main-register h3 span {
    font-size: inherit;
    font-weight: inherit;
    color: var(--text);
    text-transform: none;
    font-family: var(--font-app);
}

.imas-auth-modal.login-and-register-form .main-register h3 :deep(strong) {
    color: var(--brand-gold);
    font-weight: 700;
}

.imas-auth-modal.login-and-register-form .close-reg {
    top: 0.65rem;
    right: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    padding: 0;
    line-height: 1;
    background: var(--brand-gold);
    color: var(--text-on-gold);
}

.imas-auth-modal.login-and-register-form .close-reg:hover {
    background: var(--brand-gold-hover);
}

.imas-auth-modal.login-and-register-form .close-reg .fa.fa-times {
    padding: 0 !important;
    margin: 0;
    line-height: 1;
    font-size: 1rem;
    text-align: center;
    pointer-events: none;
}

.imas-auth-modal.login-and-register-form #tabs-container {
    margin-top: 0.5rem;
    padding: 0;
}

.imas-auth-modal.login-and-register-form .tabs-menu {
    float: none;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    width: var(--imas-auth-content-width);
    max-width: 100%;
    margin: 0.5rem auto 0.75rem;
    padding: 0 !important;
    border-bottom-color: var(--divider);
}

.imas-auth-modal.login-and-register-form .tabs-menu li {
    margin-right: 0.5rem;
}

.imas-auth-modal.login-and-register-form .tabs-menu li a {
    padding: 0.5rem 1.25rem;
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--text-dim);
    background: var(--surface-2);
    border-radius: 6px 6px 0 0;
}

.imas-auth-modal.login-and-register-form .tabs-menu li.current a,
.imas-auth-modal.login-and-register-form .tabs-menu li a:hover {
    color: var(--text-on-gold);
    background: var(--brand-gold);
}

.imas-auth-modal.login-and-register-form .tab-contents {
    padding: 0.5rem 0 0;
}

/* Form + tabs: shared width, centered (header stays full width) */
.imas-auth-modal.login-and-register-form .custom-form {
    width: var(--imas-auth-content-width);
    max-width: 100%;
    margin-left: auto;
    margin-right: auto;
    box-sizing: border-box;
}

@media (max-width: 520px) {
    .imas-auth-modal.login-and-register-form .main-register-holder {
        margin: 2rem 1rem 1.5rem;
        width: auto;
        max-width: none;
        box-sizing: border-box;
    }

    .imas-auth-modal.login-and-register-form .main-register {
        --imas-auth-content-width: 100%;
        padding-left: 1rem;
        padding-right: 1rem;
    }

    .imas-auth-modal.login-and-register-form .custom-form {
        padding-left: 0;
        padding-right: 0;
    }
}

/* Theme hides all `.tab-contents` and only shows `#tab-1`; we use active class instead. */
.imas-auth-modal.login-and-register-form .tab-contents.imas-auth-tab--active {
    display: block !important;
}

.imas-auth-form-field {
    margin-bottom: 0.5rem;
}

.imas-auth-form-field--actions {
    margin-bottom: 0;
    margin-top: 0.25rem;
}

.forgot-password-form {
    padding: 0 !important;
}

/* Theme forces float + text-align:left on labels; use logical start for RTL/LTR. */
.imas-auth-modal.login-and-register-form .custom-form label {
    float: none;
    display: block;
    clear: both;
    text-align: start;
    padding-bottom: 0.25rem;
    margin-bottom: 0;
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--text-dim);
}

.imas-auth-modal.login-and-register-form .imas-auth-field-error {
    text-align: start;
}

.imas-auth-modal.login-and-register-form
    .custom-form
    input:not([type="checkbox"]):not([type="radio"]),
.imas-auth-modal.login-and-register-form .custom-form textarea {
    float: none;
    text-align: start;
    width: 100%;
    margin-bottom: 0.5rem;
    padding: 0.65rem 0.85rem !important;
    border: 1px solid var(--border) !important;
    border-radius: 6px;
    background: var(--surface-2) !important;
    color: var(--text) !important;
    font-size: var(--text-sm);
    -webkit-appearance: none;
    appearance: none;
    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease;
}

.imas-auth-modal.login-and-register-form
    .custom-form
    input:not([type="checkbox"]):not([type="radio"])::placeholder,
.imas-auth-modal.login-and-register-form .custom-form textarea::placeholder {
    color: var(--text-muted);
}

.imas-auth-modal.login-and-register-form
    .custom-form
    input:not([type="checkbox"]):not([type="radio"]):focus,
.imas-auth-modal.login-and-register-form .custom-form textarea:focus {
    outline: none;
    border-color: var(--brand-gold) !important;
    box-shadow: var(--ring);
}

.imas-auth-modal.login-and-register-form .custom-form .log-submit-btn {
    float: none;
    width: 100%;
    margin-top: 0.35rem;
    padding: 0.65rem 1.25rem;
    border-radius: 6px;
    background: var(--brand-gold) !important;
    color: var(--text-on-gold) !important;
    font-size: var(--text-sm);
    font-weight: 600;
}

.imas-auth-modal.login-and-register-form .custom-form .log-submit-btn:hover:not(:disabled) {
    background: var(--brand-gold-hover) !important;
}

.imas-auth-modal.login-and-register-form .custom-form .log-submit-btn:focus-visible {
    outline: none;
    box-shadow: var(--ring);
}

.imas-auth-modal.login-and-register-form .filter-tags {
    margin-top: 0.5rem;
    align-items: center;
}

.imas-auth-modal.login-and-register-form .filter-tags label {
    width: auto;
    padding-bottom: 0;
    color: var(--text-dim);
    font-weight: 400;
}

.imas-auth-modal.login-and-register-form .lost_password {
    margin-top: 0.5rem;
}

.imas-auth-modal.login-and-register-form .lost_password a {
    color: var(--text-dim);
    font-size: var(--text-sm);
}

.imas-auth-modal.login-and-register-form .lost_password a:hover {
    color: var(--brand-gold);
}

/* Match `.login-and-register-form .custom-form input[type="text"]` (theme): single cohesive field */
.imas-auth-phone-field {
    display: flex;
    align-items: stretch;
    width: 100%;
    border: 1px solid var(--border);
    background: var(--surface-2);
    border-radius: 6px;
    overflow: visible;
    margin-bottom: 0.5rem;
}

.imas-auth-phone-field .imas-auth-country-select-shell {
    position: relative;
    flex: 0 0 auto;
    align-self: stretch;
    min-width: 6.75rem;
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

.imas-auth-modal__back {
    font-weight: 600;
    text-decoration: none;
    width: 100%;
    padding: 0 1.25rem 0.75rem 1.25rem !important;
    text-align: start;
    display: flex;
    justify-content: start;
    align-items: center;
    gap: 0.5rem;
    color: var(--brand-gold) !important;
}

.imas-auth-modal__back-label {
    display: inline-block;
}

/* RTL: place trailing arrow on inline-end (physical right); keep DOM icon-before-text for LTR. */
:root[dir="rtl"] .imas-auth-modal .imas-auth-modal__back-icon,
[dir="rtl"] .imas-auth-modal .imas-auth-modal__back-icon {
    /* order: 1; */
    transform: scaleX(-1);
}

:root[dir="rtl"] .imas-auth-modal .imas-auth-modal__back-label,
[dir="rtl"] .imas-auth-modal .imas-auth-modal__back-label {
    order: 2;
}

.imas-auth-modal__hint {
    color: var(--text-dim);
    font-size: var(--text-sm);
    margin-bottom: 0.5rem;
}

.imas-auth-field-error {
    display: block;
    color: var(--danger);
    font-size: var(--text-xs);
    margin: -0.15rem 0 0.35rem;
}

.imas-auth-modal.login-and-register-form .custom-form .filter-tags input[type="checkbox"],
.imas-auth-modal.login-and-register-form .remember-me-checkbox {
    width: 1rem !important;
    height: 1rem !important;
    margin: 0 0.35rem 0 0 !important;
    padding: 0;
    border: 1px solid var(--border) !important;
    border-radius: 3px;
    background: var(--surface-2) !important;
    accent-color: var(--brand-gold);
    cursor: pointer;
}

.imas-auth-modal.login-and-register-form
    .custom-form
    .filter-tags
    input[type="checkbox"]:checked::after {
    color: var(--brand-gold);
}
</style>
