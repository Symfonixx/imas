import { inject, shallowRef, onBeforeUnmount, ref, computed, watch, nextTick, onMounted, unref, useSSRContext, mergeProps, withCtx, createVNode, toDisplayString, createTextVNode, openBlock, createBlock, createCommentVNode, Fragment, renderList } from "vue";
import { ssrRenderTeleport, ssrRenderAttr, ssrInterpolate, ssrIncludeBooleanAttr, ssrRenderClass, ssrRenderDynamicModel, ssrLooseContain, ssrRenderStyle, ssrRenderList, ssrRenderAttrs, ssrRenderComponent, ssrRenderSlot } from "vue/server-renderer";
import { usePage, useForm, Link, Head } from "@inertiajs/vue3";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { r as refreshScrollTrigger, p as prefersReducedMotion, c as createGsapContext, _ as _export_sfc } from "../ssr.js";
const IMAS_OPEN_AUTH_EVENT = "imas:open-auth";
function useGsap() {
  const gsapInstance = inject("gsap", gsap);
  const scrollTrigger = inject("ScrollTrigger", ScrollTrigger);
  const ctxRef = shallowRef(null);
  function context(fn, scope) {
    var _a, _b;
    (_b = (_a = ctxRef.value) == null ? void 0 : _a.revert) == null ? void 0 : _b.call(_a);
    const scopeEl = (scope == null ? void 0 : scope.value) ?? scope ?? void 0;
    ctxRef.value = createGsapContext(fn, scopeEl);
    return ctxRef.value;
  }
  onBeforeUnmount(() => {
    var _a, _b;
    (_b = (_a = ctxRef.value) == null ? void 0 : _a.revert) == null ? void 0 : _b.call(_a);
    ctxRef.value = null;
  });
  return {
    gsap: gsapInstance,
    ScrollTrigger: scrollTrigger,
    context,
    prefersReducedMotion,
    refreshScrollTrigger
  };
}
const _sfc_main$8 = {
  __name: "AuthModal",
  __ssrInlineRender: true,
  props: {
    open: {
      type: Boolean,
      default: false
    },
    startTab: {
      type: String,
      default: "login"
    }
  },
  emits: ["update:open"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const page = usePage();
    function trans(key) {
      var _a;
      return ((_a = page.props.translations) == null ? void 0 : _a[key]) ?? key;
    }
    const authSubview = ref(null);
    const activeMainTab = ref("login");
    const authNoteText = computed(
      () => activeMainTab.value === "register" ? trans("RegisterNote") : trans("LoginNote")
    );
    const resetToken = ref("");
    const seo = computed(() => page.props.globals.seo || {});
    const appName = computed(() => String(seo.value.main_title || ""));
    computed(() => {
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
      return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }
    const passwordVisible = ref({
      login: false,
      register: false,
      registerConfirm: false
    });
    function passwordToggleAria(field) {
      return passwordVisible.value[field] ? trans("Hide password") : trans("Show password");
    }
    const loginForm = useForm({
      email: "",
      password: "",
      remember: false
    });
    const registerForm = useForm({
      first_name: "",
      last_name: "",
      email: "",
      mobile: "",
      password: "",
      password_confirmation: ""
    });
    const registerCountryId = ref(null);
    const registerMobileLocal = ref("");
    const registerMobileClientError = ref("");
    const registerCountryDropdownOpen = ref(false);
    const registerCountryDropdownRoot = ref(null);
    const registerCountrySearchQuery = ref("");
    const registerCountrySearchInput = ref(null);
    ref(null);
    const registerTermsAccepted = ref(false);
    const registerTermsClientError = ref("");
    const countries = computed(() => {
      var _a;
      return ((_a = page.props.globals) == null ? void 0 : _a.countries) ?? [];
    });
    const countriesWithPhone = computed(() => {
      const list = countries.value.filter(
        (c) => String(c.phone_code ?? "").trim() !== ""
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
      const alphaQuery = raw.replace(/[\d+()\-\s]/g, "").trim().toLowerCase();
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
        var _a, _b;
        (_b = (_a = registerCountrySearchInput.value) == null ? void 0 : _a.focus) == null ? void 0 : _b.call(_a);
      });
    });
    function pickDefaultRegisterCountry() {
      const list = countriesWithPhone.value;
      if (!list.length) {
        registerCountryId.value = null;
        return;
      }
      if (registerCountryId.value != null && list.some((c) => c.id === registerCountryId.value)) {
        return;
      }
      const prefer = { tr: "TR", en: "US", ar: "SA" }[String(page.props.locale)] ?? "TR";
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
    const mounted = ref(false);
    onMounted(() => {
      mounted.value = true;
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
      const iso = String(c.iso_code_2 ?? "").trim().toUpperCase();
      return `${prefix}: +${cc}${iso ? `, ${iso}` : ""}`;
    });
    const forgotForm = useForm({
      email: ""
    });
    const resetForm = useForm({
      token: "",
      email: "",
      password: "",
      password_confirmation: ""
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
      }
    );
    watch(
      () => activeMainTab.value,
      (tab) => {
        if (tab === "reset") {
          syncResetFromUrl();
        }
      }
    );
    onBeforeUnmount(() => {
      document.documentElement.classList.remove("hid-body");
      document.body.classList.remove("hid-body");
      document.removeEventListener(
        "pointerdown",
        onRegisterCountryDocPointerDown
      );
      document.removeEventListener("keydown", onRegisterCountryDocKeydown);
    });
    const mediaData = computed(() => page.props.globals.media || {});
    const logoUrl = computed(() => {
      const m = mediaData.value;
      return m.transparent_logo || m.white_logo || "";
    });
    return (_ctx, _push, _parent, _attrs) => {
      if (mounted.value) {
        ssrRenderTeleport(_push, (_push2) => {
          var _a, _b;
          if (__props.open) {
            _push2(`<div class="login-and-register-form modal imas-auth-modal" role="dialog" aria-modal="true"${ssrRenderAttr("aria-label", trans("auth_modal.dialog_label"))} data-v-a53510cb><div class="main-overlay" tabindex="-1" data-v-a53510cb></div><div class="main-register-holder" data-v-a53510cb><div class="main-register fl-wrap" data-v-a53510cb><div class="close-reg" role="button" tabindex="0"${ssrRenderAttr("aria-label", trans("auth_modal.close"))} data-v-a53510cb><i class="fa fa-times" aria-hidden="true" data-v-a53510cb></i></div>`);
            if (logoUrl.value) {
              _push2(`<div class="app_logo" data-v-a53510cb><img${ssrRenderAttr("src", logoUrl.value)}${ssrRenderAttr("data-sticky-logo", logoUrl.value)} alt="" data-v-a53510cb></div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<h3 class="text-center" data-v-a53510cb>${ssrInterpolate(authNoteText.value)}</h3>`);
            if (authSubview.value === "forgot") {
              _push2(`<div class="custom-form" data-v-a53510cb><p class="mb-3 text-start px-0" data-v-a53510cb></p><a href="#" class="imas-auth-modal__back text-start" data-v-a53510cb><i class="fa fa-angle-left fa-lg imas-auth-modal__back-icon" aria-hidden="true" data-v-a53510cb></i><span class="imas-auth-modal__back-label" data-v-a53510cb>${ssrInterpolate(trans("auth_modal.back_to_login"))}</span></a><form class="forgot-password-form" data-v-a53510cb><div data-v-a53510cb><label for="imas-auth-forgot-email" data-v-a53510cb>${ssrInterpolate(trans("Email"))} *</label><input id="imas-auth-forgot-email"${ssrRenderAttr("value", unref(forgotForm).email)} type="email" autocomplete="email" required data-v-a53510cb>`);
              if (unref(forgotForm).errors.email) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(unref(forgotForm).errors.email)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`<button type="submit" class="log-submit-btn"${ssrIncludeBooleanAttr(unref(forgotForm).processing) ? " disabled" : ""} data-v-a53510cb><span data-v-a53510cb>${ssrInterpolate(trans("Send Email Verification"))}</span></button></div></form></div>`);
            } else {
              _push2(`<div id="tabs-container" data-v-a53510cb><ul class="tabs-menu" data-v-a53510cb><li class="${ssrRenderClass({ current: activeMainTab.value === "login" })}" data-v-a53510cb><a href="#tab-imas-login" data-v-a53510cb>${ssrInterpolate(trans("Login"))}</a></li><li class="${ssrRenderClass({
                current: activeMainTab.value === "register"
              })}" data-v-a53510cb><a href="#tab-imas-register" data-v-a53510cb>${ssrInterpolate(trans("Register"))}</a></li></ul><div class="tab" data-v-a53510cb><div id="tab-imas-login" class="${ssrRenderClass([{
                "imas-auth-tab--active": activeMainTab.value === "login"
              }, "tab-contents"])}" data-v-a53510cb><div class="custom-form" data-v-a53510cb><form data-v-a53510cb><label for="imas-auth-login-email" data-v-a53510cb>${ssrInterpolate(trans("Email"))} *</label><input id="imas-auth-login-email"${ssrRenderAttr("value", unref(loginForm).email)} type="email" autocomplete="username" required data-v-a53510cb>`);
              if (unref(loginForm).errors.email) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(unref(loginForm).errors.email)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`<label for="imas-auth-login-password" data-v-a53510cb>${ssrInterpolate(trans("Password"))} *</label><div class="imas-auth-password-field" data-v-a53510cb><input id="imas-auth-login-password"${ssrRenderDynamicModel(
                passwordVisible.value.login ? "text" : "password",
                unref(loginForm).password,
                null
              )}${ssrRenderAttr(
                "type",
                passwordVisible.value.login ? "text" : "password"
              )} autocomplete="current-password" required data-v-a53510cb><button type="button" class="imas-auth-password-toggle"${ssrRenderAttr(
                "aria-label",
                passwordToggleAria("login")
              )}${ssrRenderAttr(
                "aria-pressed",
                passwordVisible.value.login
              )} data-v-a53510cb><i class="${ssrRenderClass(
                passwordVisible.value.login ? "fa fa-eye-slash" : "fa fa-eye"
              )}" aria-hidden="true" data-v-a53510cb></i></button></div>`);
              if (unref(loginForm).errors.password) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(unref(loginForm).errors.password)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`<button type="submit" class="log-submit-btn"${ssrIncludeBooleanAttr(unref(loginForm).processing) ? " disabled" : ""} data-v-a53510cb><span data-v-a53510cb>${ssrInterpolate(trans("Sign In"))}</span></button><div class="clearfix" data-v-a53510cb></div><div class="filter-tags" data-v-a53510cb><input id="imas-auth-remember"${ssrIncludeBooleanAttr(Array.isArray(unref(loginForm).remember) ? ssrLooseContain(unref(loginForm).remember, null) : unref(loginForm).remember) ? " checked" : ""} type="checkbox" class="mx-2 remember-me-checkbox" data-v-a53510cb><label for="imas-auth-remember" data-v-a53510cb>${ssrInterpolate(trans("Remember Me"))}</label></div></form><div class="lost_password" data-v-a53510cb><a href="#" data-v-a53510cb>${ssrInterpolate(trans("Forgot Password"))}</a></div></div></div><div class="tab" data-v-a53510cb><div id="tab-imas-register" class="${ssrRenderClass([{
                "imas-auth-tab--active": activeMainTab.value === "register"
              }, "tab-contents"])}" data-v-a53510cb><div class="custom-form main-register-form" data-v-a53510cb><form data-v-a53510cb><div class="imas-auth-form-field-row" data-v-a53510cb><div class="imas-auth-form-field" data-v-a53510cb><label for="imas-auth-reg-first-name" data-v-a53510cb>${ssrInterpolate(trans(
                "contact_us.first_name"
              ))} *</label><input id="imas-auth-reg-first-name"${ssrRenderAttr(
                "value",
                unref(registerForm).first_name
              )} type="text" autocomplete="given-name" required maxlength="120" data-v-a53510cb>`);
              if (unref(registerForm).errors.first_name) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(unref(registerForm).errors.first_name)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div><div class="imas-auth-form-field" data-v-a53510cb><label for="imas-auth-reg-last-name" data-v-a53510cb>${ssrInterpolate(trans(
                "contact_us.last_name"
              ))} *</label><input id="imas-auth-reg-last-name"${ssrRenderAttr(
                "value",
                unref(registerForm).last_name
              )} type="text" autocomplete="family-name" required maxlength="120" data-v-a53510cb>`);
              if (unref(registerForm).errors.last_name) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(unref(registerForm).errors.last_name)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div></div><div class="imas-auth-form-field" data-v-a53510cb><label for="imas-auth-reg-email" data-v-a53510cb>${ssrInterpolate(trans("Email"))} *</label><input id="imas-auth-reg-email"${ssrRenderAttr("value", unref(registerForm).email)} type="email" autocomplete="email" required data-v-a53510cb>`);
              if (unref(registerForm).errors.email) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(unref(registerForm).errors.email)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div><div class="imas-auth-form-field" data-v-a53510cb><label for="imas-auth-reg-mobile" data-v-a53510cb>${ssrInterpolate(trans("Mobile"))} *</label><div class="${ssrRenderClass([{
                "imas-auth-phone-field--country-open": registerCountryDropdownOpen.value
              }, "imas-auth-phone-field"])}" dir="ltr" data-v-a53510cb><div class="imas-auth-country-select-shell" data-v-a53510cb><button id="imas-auth-reg-country-code" type="button" class="imas-auth-country-trigger"${ssrRenderAttr(
                "aria-expanded",
                registerCountryDropdownOpen.value
              )} aria-haspopup="listbox"${ssrRenderAttr(
                "aria-label",
                registerCountrySelectAriaLabel.value
              )} data-v-a53510cb>`);
              if ((_a = selectedRegisterCountry.value) == null ? void 0 : _a.flag) {
                _push2(`<img class="imas-auth-country-flag-img"${ssrRenderAttr(
                  "src",
                  selectedRegisterCountry.value.flag
                )} alt="" width="22" height="16" decoding="async" loading="lazy" data-v-a53510cb>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`<span class="imas-auth-country-code-label" aria-hidden="true" data-v-a53510cb>+${ssrInterpolate(displayCallingCode(
                (_b = selectedRegisterCountry.value) == null ? void 0 : _b.phone_code
              ))}</span></button><div style="${ssrRenderStyle(registerCountryDropdownOpen.value ? null : { display: "none" })}" class="imas-auth-country-dropdown-panel" data-v-a53510cb><div class="imas-auth-country-dropdown-search-wrap text-start" data-v-a53510cb><input${ssrRenderAttr(
                "value",
                registerCountrySearchQuery.value
              )} type="search" enterkeyhint="search" autocomplete="off" autocorrect="off" spellcheck="false" class="imas-auth-country-dropdown-search"${ssrRenderAttr(
                "placeholder",
                trans(
                  "Search"
                )
              )}${ssrRenderAttr(
                "aria-label",
                trans(
                  "Search"
                )
              )} data-v-a53510cb></div><ul class="imas-auth-country-dropdown-scroll" role="listbox" tabindex="-1" data-v-a53510cb>`);
              if (countriesWithPhoneFiltered.value.length === 0) {
                _push2(`<li class="imas-auth-country-option imas-auth-country-option--empty" aria-live="polite" data-v-a53510cb>${ssrInterpolate(trans(
                  "auth_modal.country_code_search_empty"
                ))}</li>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`<!--[-->`);
              ssrRenderList(countriesWithPhoneFiltered.value, (c) => {
                _push2(`<li role="option" class="${ssrRenderClass([{
                  "imas-auth-country-option--selected": c.id === registerCountryId.value
                }, "imas-auth-country-option"])}"${ssrRenderAttr(
                  "aria-selected",
                  c.id === registerCountryId.value
                )} data-v-a53510cb>`);
                if (c.flag) {
                  _push2(`<img class="imas-auth-country-flag-img imas-auth-country-flag-img--option"${ssrRenderAttr(
                    "src",
                    c.flag
                  )} alt="" width="22" height="16" decoding="async" loading="lazy" data-v-a53510cb>`);
                } else {
                  _push2(`<!---->`);
                }
                _push2(`<span class="imas-auth-country-option-code" data-v-a53510cb>+${ssrInterpolate(displayCallingCode(
                  c.phone_code
                ))}</span></li>`);
              });
              _push2(`<!--]--></ul></div></div><span class="imas-auth-phone-sep" aria-hidden="true" data-v-a53510cb></span><input id="imas-auth-reg-mobile"${ssrRenderAttr(
                "value",
                registerMobileLocal.value
              )} type="tel" inputmode="numeric" autocomplete="tel-national" class="imas-auth-phone-input" required${ssrRenderAttr(
                "placeholder",
                trans(
                  "auth_modal.mobile_national_placeholder"
                )
              )} data-v-a53510cb></div>`);
              if (registerMobileClientError.value) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(registerMobileClientError.value)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              if (unref(registerForm).errors.mobile) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(unref(registerForm).errors.mobile)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div><div class="imas-auth-form-field" data-v-a53510cb><label for="imas-auth-reg-password" data-v-a53510cb>${ssrInterpolate(trans("Password"))} *</label><div class="imas-auth-password-field" data-v-a53510cb><input id="imas-auth-reg-password"${ssrRenderDynamicModel(
                passwordVisible.value.register ? "text" : "password",
                unref(registerForm).password,
                null
              )}${ssrRenderAttr(
                "type",
                passwordVisible.value.register ? "text" : "password"
              )} autocomplete="new-password" required data-v-a53510cb><button type="button" class="imas-auth-password-toggle"${ssrRenderAttr(
                "aria-label",
                passwordToggleAria(
                  "register"
                )
              )}${ssrRenderAttr(
                "aria-pressed",
                passwordVisible.value.register
              )} data-v-a53510cb><i class="${ssrRenderClass(
                passwordVisible.value.register ? "fa fa-eye-slash" : "fa fa-eye"
              )}" aria-hidden="true" data-v-a53510cb></i></button></div>`);
              if (unref(registerForm).errors.password) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(unref(registerForm).errors.password)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div><div class="imas-auth-form-field" data-v-a53510cb><label for="imas-auth-reg-password-confirmation" data-v-a53510cb>${ssrInterpolate(trans(
                "Confirm Password"
              ))} *</label><div class="imas-auth-password-field" data-v-a53510cb><input id="imas-auth-reg-password-confirmation"${ssrRenderDynamicModel(
                passwordVisible.value.registerConfirm ? "text" : "password",
                unref(registerForm).password_confirmation,
                null
              )}${ssrRenderAttr(
                "type",
                passwordVisible.value.registerConfirm ? "text" : "password"
              )} autocomplete="new-password" required data-v-a53510cb><button type="button" class="imas-auth-password-toggle"${ssrRenderAttr(
                "aria-label",
                passwordToggleAria(
                  "registerConfirm"
                )
              )}${ssrRenderAttr(
                "aria-pressed",
                passwordVisible.value.registerConfirm
              )} data-v-a53510cb><i class="${ssrRenderClass(
                passwordVisible.value.registerConfirm ? "fa fa-eye-slash" : "fa fa-eye"
              )}" aria-hidden="true" data-v-a53510cb></i></button></div>`);
              if (unref(registerForm).errors.password_confirmation) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(unref(registerForm).errors.password_confirmation)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div><div class="imas-auth-terms-wrap" data-v-a53510cb><div class="filter-tags imas-auth-terms" data-v-a53510cb><input id="imas-auth-terms"${ssrIncludeBooleanAttr(
                Array.isArray(
                  registerTermsAccepted.value
                ) ? ssrLooseContain(
                  registerTermsAccepted.value,
                  null
                ) : registerTermsAccepted.value
              ) ? " checked" : ""} type="checkbox" class="mx-2 remember-me-checkbox" data-v-a53510cb><label for="imas-auth-terms" class="imas-auth-terms__label" data-v-a53510cb>${ssrInterpolate(trans(
                "auth_modal.agree_terms_prefix"
              ))} <a href="#" class="imas-auth-terms__link" data-v-a53510cb>${ssrInterpolate(trans(
                "auth_modal.terms_and_conditions"
              ))}</a></label></div>`);
              if (registerTermsClientError.value) {
                _push2(`<p class="imas-auth-terms__error" role="alert" data-v-a53510cb>${ssrInterpolate(registerTermsClientError.value)}</p>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div><div class="imas-auth-form-field imas-auth-form-field--actions" data-v-a53510cb><button type="submit" class="log-submit-btn"${ssrIncludeBooleanAttr(
                unref(registerForm).processing
              ) ? " disabled" : ""} data-v-a53510cb><span data-v-a53510cb>${ssrInterpolate(trans("Register"))}</span></button></div></form></div></div></div><div class="tab" data-v-a53510cb><div id="tab-imas-reset" class="${ssrRenderClass([{
                "imas-auth-tab--active": activeMainTab.value === "reset"
              }, "tab-contents"])}" data-v-a53510cb><div class="custom-form" data-v-a53510cb>`);
              if (!resetToken.value) {
                _push2(`<p class="imas-auth-modal__hint" data-v-a53510cb>${ssrInterpolate(trans("auth_modal.reset_hint"))}</p>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`<form data-v-a53510cb><label for="imas-auth-reset-email" data-v-a53510cb>${ssrInterpolate(trans("Email"))} *</label><input id="imas-auth-reset-email"${ssrRenderAttr("value", unref(resetForm).email)} type="email" autocomplete="email" required data-v-a53510cb>`);
              if (unref(resetForm).errors.email) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(unref(resetForm).errors.email)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`<label for="imas-auth-reset-password" data-v-a53510cb>${ssrInterpolate(trans("Password"))} *</label><input id="imas-auth-reset-password"${ssrRenderAttr("value", unref(resetForm).password)} type="password" autocomplete="new-password" required data-v-a53510cb>`);
              if (unref(resetForm).errors.password) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(unref(resetForm).errors.password)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`<label for="imas-auth-reset-password-confirmation" data-v-a53510cb>${ssrInterpolate(trans("Confirm Password"))} *</label><input id="imas-auth-reset-password-confirmation"${ssrRenderAttr(
                "value",
                unref(resetForm).password_confirmation
              )} type="password" autocomplete="new-password" required data-v-a53510cb>`);
              if (unref(resetForm).errors.password_confirmation) {
                _push2(`<span class="imas-auth-field-error" data-v-a53510cb>${ssrInterpolate(unref(resetForm).errors.password_confirmation)}</span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`<button type="submit" class="log-submit-btn"${ssrIncludeBooleanAttr(
                unref(resetForm).processing || !resetToken.value
              ) ? " disabled" : ""} data-v-a53510cb><span data-v-a53510cb>${ssrInterpolate(trans("Reset Password"))}</span></button></form></div></div></div></div></div>`);
            }
            _push2(`</div></div></div>`);
          } else {
            _push2(`<!---->`);
          }
        }, "body", false, _parent);
      } else {
        _push(`<!---->`);
      }
    };
  }
};
const _sfc_setup$8 = _sfc_main$8.setup;
_sfc_main$8.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/Findhouses/AuthModal.vue");
  return _sfc_setup$8 ? _sfc_setup$8(props, ctx) : void 0;
};
const AuthModal = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["__scopeId", "data-v-a53510cb"]]);
const SUPPORTED_LOCALES = ["en", "tr", "ar"];
function applyLocalePrefix(url, locale) {
  const loc = SUPPORTED_LOCALES.includes(locale) ? locale : "en";
  if (!url || url === "#") {
    return url;
  }
  try {
    const origin = typeof window !== "undefined" ? window.location.origin : "http://localhost";
    const parsed = new URL(url, origin);
    let segments = parsed.pathname.split("/").filter(Boolean);
    if (segments.length > 0 && SUPPORTED_LOCALES.includes(segments[0])) {
      segments.shift();
    }
    parsed.pathname = segments.length > 0 ? `/${loc}/${segments.join("/")}` : `/${loc}`;
    return `${parsed.pathname}${parsed.search}${parsed.hash}`;
  } catch {
    return localizedFallbackPath(url, loc);
  }
}
function localizedFallbackPath(path, locale = "en") {
  const loc = SUPPORTED_LOCALES.includes(locale) ? locale : "en";
  const normalized = !path || path === "/" ? "/" : path.startsWith("/") ? path : `/${path}`;
  const segments = normalized.split("/").filter(Boolean);
  if (segments.length > 0 && SUPPORTED_LOCALES.includes(segments[0])) {
    segments[0] = loc;
    return `/${segments.join("/")}`;
  }
  if (normalized === "/") {
    return `/${loc}`;
  }
  return `/${loc}${normalized}`;
}
function localizedRoute(name, params, locale, fallback = "#") {
  var _a, _b;
  const loc = SUPPORTED_LOCALES.includes(locale) ? locale : "en";
  let url = fallback;
  try {
    if (typeof route === "function" && ((_b = (_a = route()).has) == null ? void 0 : _b.call(_a, name))) {
      url = route(name, params);
    }
  } catch {
  }
  if (!url || url === "#") {
    return localizedFallbackPath(fallback, loc);
  }
  return applyLocalePrefix(url, loc);
}
const _sfc_main$7 = {
  __name: "NavbarSearchModal",
  __ssrInlineRender: true,
  props: {
    open: {
      type: Boolean,
      default: false
    }
  },
  emits: ["update:open"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const page = usePage();
    const inputRef = ref(null);
    const searchQuery = ref("");
    const activeLocale = computed(() => page.props.locale || "en");
    const isRtl = computed(
      () => page.props.text_direction === "rtl" || page.props.locale === "ar"
    );
    const mediaData = computed(() => {
      var _a;
      return ((_a = page.props.globals) == null ? void 0 : _a.media) || {};
    });
    const logoUrl = computed(() => {
      const m = mediaData.value;
      return m.transparent_logo || m.white_logo || "";
    });
    function trans(key) {
      var _a;
      return ((_a = page.props.translations) == null ? void 0 : _a[key]) ?? key;
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
        const indexPath = new URL(
          localizedRoute(
            "property.index",
            {},
            activeLocale.value,
            "/property"
          ),
          window.location.origin
        ).pathname.replace(/\/+$/, "") || "/";
        const currentPath = window.location.pathname.replace(/\/+$/, "") || "/";
        return currentPath === indexPath;
      } catch {
        return false;
      }
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
        var _a, _b, _c;
        setBodyScrollLock(!!isOpen);
        if (!isOpen) {
          return;
        }
        searchQuery.value = isPropertyIndexPath() ? readQueryFromUrl() : "";
        await nextTick();
        (_a = inputRef.value) == null ? void 0 : _a.focus();
        (_c = (_b = inputRef.value) == null ? void 0 : _b.select) == null ? void 0 : _c.call(_b);
      }
    );
    const mounted = ref(false);
    onMounted(() => {
      mounted.value = true;
      document.addEventListener("keydown", onKeydown);
    });
    onBeforeUnmount(() => {
      document.removeEventListener("keydown", onKeydown);
      setBodyScrollLock(false);
    });
    return (_ctx, _push, _parent, _attrs) => {
      if (mounted.value) {
        ssrRenderTeleport(_push, (_push2) => {
          if (__props.open) {
            _push2(`<div class="imas-navbar-search" role="dialog" aria-modal="true"${ssrRenderAttr("aria-label", trans("Search"))} data-v-bfb447fe><div class="${ssrRenderClass([{ "imas-navbar-search__bar--rtl": isRtl.value }, "imas-navbar-search__bar"])}" data-v-bfb447fe><div class="imas-navbar-search__brand" data-v-bfb447fe>`);
            if (logoUrl.value) {
              _push2(`<img${ssrRenderAttr("src", logoUrl.value)} alt="" class="imas-navbar-search__logo" data-v-bfb447fe>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div><button type="button" class="imas-navbar-search__back"${ssrRenderAttr("aria-label", trans("auth_modal.close"))} data-v-bfb447fe><i class="${ssrRenderClass([isRtl.value ? "fa-arrow-left" : "fa-arrow-left", "fa"])}" aria-hidden="true" data-v-bfb447fe></i></button><form class="imas-navbar-search__form" data-v-bfb447fe><label class="sr-only" for="imas-navbar-search-input" data-v-bfb447fe>${ssrInterpolate(trans("Search"))}</label><div class="imas-navbar-search__input-wrap" data-v-bfb447fe><input id="imas-navbar-search-input"${ssrRenderAttr("value", searchQuery.value)} type="search" class="imas-navbar-search__input"${ssrRenderAttr("placeholder", trans("Search"))} autocomplete="off" maxlength="255" enterkeyhint="search" data-v-bfb447fe>`);
            if (searchQuery.value) {
              _push2(`<button type="button" class="imas-navbar-search__clear"${ssrRenderAttr("aria-label", trans("auth_modal.close"))} data-v-bfb447fe><i class="fa fa-times" aria-hidden="true" data-v-bfb447fe></i></button>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div><button type="submit" class="imas-navbar-search__submit"${ssrRenderAttr("aria-label", trans("Search"))} data-v-bfb447fe><i class="fa fa-search" aria-hidden="true" data-v-bfb447fe></i></button></form></div><button type="button" class="imas-navbar-search__backdrop"${ssrRenderAttr("aria-label", trans("auth_modal.close"))} tabindex="-1" data-v-bfb447fe></button></div>`);
          } else {
            _push2(`<!---->`);
          }
        }, "body", false, _parent);
      } else {
        _push(`<!---->`);
      }
    };
  }
};
const _sfc_setup$7 = _sfc_main$7.setup;
_sfc_main$7.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/Findhouses/NavbarSearchModal.vue");
  return _sfc_setup$7 ? _sfc_setup$7(props, ctx) : void 0;
};
const NavbarSearchModal = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["__scopeId", "data-v-bfb447fe"]]);
const websiteSlogan$1 = "MOST ACCURATE SOLUTIONS";
const _sfc_main$6 = {
  __name: "UserNavbar",
  __ssrInlineRender: true,
  props: {
    navLinks: {
      type: Array,
      default: () => []
    },
    transparentNavbar: {
      type: Boolean,
      default: true
    }
  },
  setup(__props) {
    const props = __props;
    const page = usePage();
    const activeLocale = computed(() => page.props.locale || "en");
    const homeHref = computed(
      () => localizedRoute("home", {}, activeLocale.value, "/")
    );
    const authModalOpen = ref(false);
    const authStartTab = ref("login");
    const searchModalOpen = ref(false);
    const mounted = ref(false);
    function openAuthModal(tab = "login") {
      var _a;
      searchModalOpen.value = false;
      authStartTab.value = tab === "register" || tab === "reset" ? tab : "login";
      authModalOpen.value = true;
      (_a = mmenuApi == null ? void 0 : mmenuApi.close) == null ? void 0 : _a.call(mmenuApi);
    }
    function onDelegatedOpenAuth(e) {
      const el = e.target.closest("a[data-open-auth]");
      if (!el) {
        return;
      }
      e.preventDefault();
      const tab = el.getAttribute("data-open-auth") || "login";
      openAuthModal(tab === "register" || tab === "reset" ? tab : "login");
    }
    function onImasOpenAuthEvent(e) {
      var _a;
      const tab = ((_a = e.detail) == null ? void 0 : _a.tab) || "login";
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
    const { gsap: gsap2, context } = useGsap();
    const headerPinned = ref(false);
    const headerPinnedVisible = ref(false);
    const scrollPinSpacerPx = ref(0);
    let scrollPinRaf = 0;
    let scrollPinAnimToken = 0;
    let onScrollPinnedBound = null;
    let onResizePinnedBound = null;
    const websiteName = computed(() => {
      var _a, _b;
      const name = ((_b = (_a = page.props.globals) == null ? void 0 : _a.seo) == null ? void 0 : _b.website_name) || page.props.appName || "";
      return String(name).toUpperCase();
    });
    computed(() => page.props.theme_url || "");
    const auth = computed(() => page.props.auth);
    const isRtl = computed(
      () => page.props.text_direction === "rtl" || page.props.locale === "ar"
    );
    const accountGreeting = computed(() => {
      var _a;
      const hello = trans("Hi");
      const name = String(((_a = auth.value) == null ? void 0 : _a.nav_display_name) ?? "").trim();
      return name ? `${hello} ${name}` : hello;
    });
    const isAdmin = computed(() => {
      var _a;
      return ((_a = auth.value) == null ? void 0 : _a.type) === "admin";
    });
    const profileHref = computed(() => {
      if (isAdmin.value) {
        return route("admin.profile.edit");
      }
      return homeHref.value;
    });
    const favoritesHref = computed(
      () => localizedRoute(
        "property.favorites",
        {},
        activeLocale.value,
        "/favorite-properties"
      )
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
        const base = typeof window !== "undefined" ? window.location.origin : "http://localhost";
        const path = new URL(url, base).pathname.replace(/\/+$/, "") || "/";
        return path;
      } catch {
        return url.split("?")[0].replace(/\/+$/, "") || "/";
      }
    }
    function isNavLinkActive(item) {
      var _a, _b, _c, _d;
      if (!(item == null ? void 0 : item.href)) {
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
          if (typeof route === "function" && ((_b = (_a = route()).current) == null ? void 0 : _b.call(_a, "home"))) {
            return true;
          }
        } catch {
        }
        try {
          if (typeof route === "function" && ((_d = (_c = route()).has) == null ? void 0 : _d.call(_c, "home"))) {
            return current === normalizePath(route("home"));
          }
        } catch {
        }
      }
      if (target !== "/" && current.startsWith(`${target}/`)) {
        return true;
      }
      return false;
    }
    const localeSwitcher = computed(() => page.props.locale_switcher || []);
    const currentLocale = computed(() => page.props.locale || "en");
    computed(() => {
      const code = currentLocale.value;
      if (code === "en") {
        return "ENG";
      }
      return code.toUpperCase();
    });
    function trans(key) {
      var _a;
      return ((_a = page.props.translations) == null ? void 0 : _a[key]) ?? key;
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
      const isRtl2 = document.documentElement.getAttribute("dir") === "rtl" || document.documentElement.dir === "rtl";
      context(() => {
        const tl = gsap2.timeline({ defaults: { ease: "power2.out" } });
        if (logo) {
          tl.fromTo(
            logo,
            { opacity: 0, x: isRtl2 ? 16 : -16 },
            { opacity: 1, x: 0, duration: 0.5 },
            0
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
              stagger: 0.06
            },
            logo ? 0.1 : 0
          );
        }
        if (isDesktop && actions.length) {
          tl.fromTo(
            actions,
            { opacity: 0, x: isRtl2 ? -16 : 16 },
            {
              opacity: 1,
              x: 0,
              duration: 0.45,
              stagger: 0.08
            },
            logo ? 0.14 : 0.08
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
      const isRtl2 = document.documentElement.getAttribute("dir") === "rtl" || document.documentElement.dir === "rtl";
      gsap2.fromTo(
        items,
        {
          opacity: 0,
          x: isRtl2 ? 20 : -20
        },
        {
          opacity: 1,
          x: 0,
          duration: 0.4,
          stagger: 0.05,
          ease: "power2.out",
          overwrite: "auto"
        }
      );
    }
    const LOCALE_FLAG_SUFFIX = {
      en: "gb",
      tr: "tr",
      ar: "sa"
    };
    function flagCountryClass(localeCode) {
      const suffix = LOCALE_FLAG_SUFFIX[localeCode];
      return suffix ? `fi-${suffix}` : null;
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
    let mmenuApi = null;
    function customizeMmenuNavbar($) {
      const $page = $(".mm-page").first().length ? $(".mm-page").first() : $("#app");
      const pageId = $page.attr("id");
      if (!pageId) {
        return;
      }
      const isRtl2 = document.documentElement.getAttribute("dir") === "rtl" || document.documentElement.dir === "rtl";
      const closeLabel = isRtl2 ? "إغلاق القائمة" : "Close menu";
      $(".mm-menu.mm-offcanvas").each(function() {
        const $menu = $(this);
        const $rootPanel = $menu.find("> .mm-panels > .mm-panel").first();
        const $navbar = $rootPanel.children(".mm-navbar").first();
        if (!$navbar.length) {
          return;
        }
        $navbar.find(".mm-title").text(trans("Menu"));
        if (!$navbar.find("a.mm-close").length) {
          $navbar.prepend(
            `<a class="mm-btn mm-close" href="#${pageId}" aria-label="${closeLabel}"></a>`
          );
        }
      });
    }
    function stripMmenuAuthLinks($) {
      $(".mmenu-init").find("li.imas-mmenu-only").has(".imas-auth-nav-link").remove();
    }
    function initMobileMenuMmenu() {
      var _a;
      const $ = window.jQuery;
      if (!$ || !((_a = $.fn) == null ? void 0 : _a.mmenu)) {
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
      $navigation.clone().addClass("mmenu-init").insertBefore("#navigation").removeAttr("id").removeClass("style-1 style-2 imas-nav__menu").find("ul").removeAttr("id");
      $(".mmenu-init").find(".container").removeClass("container");
      $(".mmenu-init").find("li.imas-mmenu-only").has(".lang-switch-row").remove();
      if (auth.value) {
        stripMmenuAuthLinks($);
      }
      const isRtl2 = document.documentElement.getAttribute("dir") === "rtl" || document.documentElement.dir === "rtl";
      $(".mmenu-init").mmenu(
        {
          counters: true,
          navbar: {
            title: trans("Menu")
          }
        },
        {
          offCanvas: {
            // Inertia mounts inside a root element (usually `#app`).
            // Using `pageSelector` ensures the whole SPA (including header) slides out.
            pageSelector: "#app",
            // Drawer must open from the inline-start side (right in RTL).
            position: isRtl2 ? "right" : "left"
          }
        }
      );
      mmenuApi = $(".mmenu-init").data("mmenu") || null;
      if (!mmenuApi) {
        return;
      }
      const $icon = $(".hamburger");
      $(".mmenu-trigger").off("click.imasMmenu").on("click.imasMmenu", () => {
        var _a2;
        (_a2 = mmenuApi == null ? void 0 : mmenuApi.open) == null ? void 0 : _a2.call(mmenuApi);
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
    function removeLegacyHeaderClones() {
      const $ = window.jQuery;
      if ($) {
        $("#header.cloned").remove();
        $("#navigation.style-2.cloned").remove();
        return;
      }
      document.querySelectorAll("#header.cloned").forEach((el) => el.remove());
      document.querySelectorAll("#navigation.style-2.cloned").forEach((el) => el.remove());
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
      () => reinitHeaderChromeForLocale()
    );
    watch(
      () => props.transparentNavbar,
      () => reinitHeaderChromeForLocale()
    );
    watch(
      () => props.navLinks,
      () => {
        nextTick(() => playNavbarEnterAnimation());
      },
      { deep: true }
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
      }
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
      });
      const $ = window.jQuery;
      if ($) {
        $(window).off("resize.imasMmenu").on("resize.imasMmenu", () => {
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
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<header${ssrRenderAttrs(mergeProps({
        ref_key: "headerContainerRef",
        ref: headerContainerRef,
        id: "header-container",
        class: ["header imas-nav-shell", [
          __props.transparentNavbar ? "head-tr" : "imas-navbar-solid",
          { "imas-header-scroll-pinned": headerPinned.value }
        ]]
      }, _attrs))} data-v-75ddbaa3><div id="header" class="${ssrRenderClass([{
        "imas-scroll-pinned": headerPinned.value,
        "imas-scroll-pinned--in": headerPinned.value && headerPinnedVisible.value
      }, "imas-nav imas-nav__bar bottom"])}" data-v-75ddbaa3><div class="container imas-nav__container" data-v-75ddbaa3><div id="logo" class="imas-nav__logo" data-v-75ddbaa3>`);
      _push(ssrRenderComponent(unref(Link), {
        href: homeHref.value,
        class: "imas-nav__logo-link"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<img${ssrRenderAttr("src", logoUrl.value)}${ssrRenderAttr("data-sticky-logo", logoUrl.value)} alt="" data-v-75ddbaa3${_scopeId}><span class="imas-brand-text" data-v-75ddbaa3${_scopeId}><span class="website-name" data-v-75ddbaa3${_scopeId}>${ssrInterpolate(websiteName.value)}</span><span class="website-slogan" data-v-75ddbaa3${_scopeId}>${ssrInterpolate(websiteSlogan$1)}</span></span>`);
          } else {
            return [
              createVNode("img", {
                src: logoUrl.value,
                "data-sticky-logo": logoUrl.value,
                alt: ""
              }, null, 8, ["src", "data-sticky-logo"]),
              createVNode("span", { class: "imas-brand-text" }, [
                createVNode("span", { class: "website-name" }, toDisplayString(websiteName.value), 1),
                createVNode("span", { class: "website-slogan" }, toDisplayString(websiteSlogan$1))
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div><nav id="navigation" class="imas-nav__menu style-1" data-v-75ddbaa3><ul id="responsive" data-v-75ddbaa3><!--[-->`);
      ssrRenderList(__props.navLinks, (item) => {
        var _a, _b;
        _push(`<li class="${ssrRenderClass([{
          "has-submenu": (_a = item == null ? void 0 : item.children) == null ? void 0 : _a.length
        }, "imas-nav-item"])}" data-v-75ddbaa3>`);
        if (item.href) {
          _push(ssrRenderComponent(unref(Link), {
            href: item.href,
            class: { active: isNavLinkActive(item) }
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`${ssrInterpolate(item.label ?? trans(item.key))}`);
              } else {
                return [
                  createTextVNode(toDisplayString(item.label ?? trans(item.key)), 1)
                ];
              }
            }),
            _: 2
          }, _parent));
        } else {
          _push(`<a href="#" class="${ssrRenderClass({ active: isNavLinkActive(item) })}" data-v-75ddbaa3>${ssrInterpolate(item.label ?? trans(item.key))}</a>`);
        }
        if ((_b = item == null ? void 0 : item.children) == null ? void 0 : _b.length) {
          _push(`<ul class="imas-nav__submenu" data-v-75ddbaa3><!--[-->`);
          ssrRenderList(item.children, (child) => {
            _push(`<li class="imas-nav__submenu-item" data-v-75ddbaa3>`);
            _push(ssrRenderComponent(unref(Link), {
              href: child.href,
              class: ["imas-nav__submenu-link", {
                active: isNavLinkActive(child)
              }]
            }, {
              default: withCtx((_, _push2, _parent2, _scopeId) => {
                if (_push2) {
                  _push2(`${ssrInterpolate(child.label ?? trans(child.key))}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(child.label ?? trans(child.key)), 1)
                  ];
                }
              }),
              _: 2
            }, _parent));
            _push(`</li>`);
          });
          _push(`<!--]--></ul>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</li>`);
      });
      _push(`<!--]-->`);
      if (!auth.value && mounted.value) {
        _push(`<li class="imas-mmenu-only" data-v-75ddbaa3><a href="#" class="imas-auth-nav-link" data-open-auth="login" data-v-75ddbaa3>${ssrInterpolate(trans("Login"))}</a></li>`);
      } else {
        _push(`<!---->`);
      }
      if (!auth.value && mounted.value) {
        _push(`<li class="imas-mmenu-only" data-v-75ddbaa3><a href="#" class="imas-auth-nav-link" data-open-auth="register" data-v-75ddbaa3>${ssrInterpolate(trans("Register"))}</a></li>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</ul></nav><div class="imas-nav__end" data-v-75ddbaa3><div class="${ssrRenderClass([{ "imas-nav__actions--rtl": isRtl.value }, "imas-nav__actions right"])}" data-v-75ddbaa3><div class="header-user-menu user-menu add imas-nav__lang imas-header-action" data-v-75ddbaa3><div class="${ssrRenderClass([{ "lang-wrap--open": langMenuOpen.value }, "lang-wrap"])}" data-v-75ddbaa3><div class="show-lang imas-nav__lang-trigger" role="button" tabindex="0"${ssrRenderAttr("aria-expanded", langMenuOpen.value)} aria-haspopup="listbox"${ssrRenderAttr("aria-label", trans("Language"))} data-v-75ddbaa3><span class="show-lang-trigger-inner" data-v-75ddbaa3>`);
      if (flagCountryClass(currentLocale.value)) {
        _push(`<span class="${ssrRenderClass([
          flagCountryClass(currentLocale.value),
          "fi lang-switch-flag lang-switch-flag--trigger"
        ])}" aria-hidden="true" data-v-75ddbaa3></span>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</span><i class="fa fa-caret-down arrlan" data-v-75ddbaa3></i></div><ul class="lang-tooltip lang-action no-list-style" role="listbox" data-v-75ddbaa3><!--[-->`);
      ssrRenderList(localeSwitcher.value, (loc) => {
        _push(`<li data-v-75ddbaa3><a href="#" class="${ssrRenderClass([{
          "current-lan": loc.code === currentLocale.value
        }, "lang-switch-row"])}" role="option"${ssrRenderAttr(
          "aria-selected",
          loc.code === currentLocale.value
        )} data-v-75ddbaa3>`);
        if (flagCountryClass(loc.code)) {
          _push(`<span class="${ssrRenderClass([
            flagCountryClass(loc.code),
            "fi lang-switch-flag"
          ])}" aria-hidden="true" data-v-75ddbaa3></span>`);
        } else {
          _push(`<!---->`);
        }
        _push(`<span class="mx-2" data-v-75ddbaa3>${ssrInterpolate(loc.native)}</span></a></li>`);
      });
      _push(`<!--]--></ul></div></div><button type="button" class="imas-nav__search imas-header-action"${ssrRenderAttr("aria-label", trans("Search"))}${ssrRenderAttr("title", trans("Search"))} data-v-75ddbaa3><i class="fa fa-search" aria-hidden="true" data-v-75ddbaa3></i></button>`);
      if (auth.value && mounted.value) {
        _push(ssrRenderComponent(unref(Link), {
          href: favoritesHref.value,
          class: ["imas-nav__favorites imas-header-action", { "is-active": favoritesNavActive.value }],
          "aria-label": trans("properties.favorite_properties"),
          title: trans("properties.favorite_properties")
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`<i class="fa fa-heart" aria-hidden="true" data-v-75ddbaa3${_scopeId}></i>`);
            } else {
              return [
                createVNode("i", {
                  class: "fa fa-heart",
                  "aria-hidden": "true"
                })
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<!---->`);
      }
      if (mounted.value) {
        _push(`<div class="${ssrRenderClass([{ active: userMenuOpen.value }, "header-user-menu user-menu add UserMenu imas-header-action"])}" data-v-75ddbaa3>`);
        if (auth.value) {
          _push(`<!--[--><div class="${ssrRenderClass([{
            "imas-nav__account-trigger--rtl": isRtl.value
          }, "header-user-name imas-nav__account-trigger"])}" role="button" tabindex="0"${ssrRenderAttr("aria-expanded", userMenuOpen.value)} aria-haspopup="true"${ssrRenderAttr("aria-label", trans("Account menu"))} data-v-75ddbaa3><span class="imas-nav__avatar" data-v-75ddbaa3><img${ssrRenderAttr("src", auth.value.avatar)} alt="" data-v-75ddbaa3></span><span class="imas-nav__account-text imas-nav__desktop-only" data-v-75ddbaa3>${ssrInterpolate(accountGreeting.value)}</span><i class="fa fa-caret-down imas-nav__account-caret imas-nav__desktop-only" aria-hidden="true" data-v-75ddbaa3></i></div><ul class="imas-user-menu-dropdown text-start" data-v-75ddbaa3>`);
          if (isAdmin.value) {
            _push(`<li data-v-75ddbaa3>`);
            _push(ssrRenderComponent(unref(Link), {
              class: "imas-user-menu-dropdown__item",
              href: _ctx.route("admin.dashboard.index"),
              onClick: ($event) => userMenuOpen.value = false
            }, {
              default: withCtx((_, _push2, _parent2, _scopeId) => {
                if (_push2) {
                  _push2(`${ssrInterpolate(trans("Dashboard"))}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(trans("Dashboard")), 1)
                  ];
                }
              }),
              _: 1
            }, _parent));
            _push(`</li>`);
          } else {
            _push(`<!---->`);
          }
          _push(`<li data-v-75ddbaa3>`);
          _push(ssrRenderComponent(unref(Link), {
            class: "imas-user-menu-dropdown__item",
            href: profileHref.value,
            onClick: ($event) => userMenuOpen.value = false
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`${ssrInterpolate(trans("global.profile"))}`);
              } else {
                return [
                  createTextVNode(toDisplayString(trans("global.profile")), 1)
                ];
              }
            }),
            _: 1
          }, _parent));
          _push(`</li><li data-v-75ddbaa3><button type="button" class="imas-user-menu-dropdown__item dropdown-logout" data-v-75ddbaa3>${ssrInterpolate(trans("global.LogOut"))}</button></li></ul><!--]-->`);
        } else {
          _push(`<div class="imas-nav__sign-in imas-header-action" data-v-75ddbaa3><a href="#" class="imas-nav__sign-in-link show-reg-form modal-open" data-open-auth="login" data-v-75ddbaa3>${ssrInterpolate(trans("Sign In"))}</a></div>`);
        }
        _push(`</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div class="mmenu-trigger imas-nav__mmenu" data-v-75ddbaa3><button class="hamburger hamburger--collapse" type="button"${ssrRenderAttr("aria-label", trans("Menu"))} data-v-75ddbaa3><span class="hamburger-box" data-v-75ddbaa3><span class="hamburger-inner" data-v-75ddbaa3></span></span></button></div></div></div></div><div style="${ssrRenderStyle([
        headerPinned.value ? null : { display: "none" },
        { height: `${scrollPinSpacerPx.value}px` }
      ])}" class="imas-header-scroll-spacer" aria-hidden="true" data-v-75ddbaa3></div>`);
      _push(ssrRenderComponent(AuthModal, {
        open: authModalOpen.value,
        "onUpdate:open": ($event) => authModalOpen.value = $event,
        "start-tab": authStartTab.value
      }, null, _parent));
      _push(ssrRenderComponent(NavbarSearchModal, {
        open: searchModalOpen.value,
        "onUpdate:open": ($event) => searchModalOpen.value = $event
      }, null, _parent));
      _push(`</header>`);
    };
  }
};
const _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/Findhouses/UserNavbar.vue");
  return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
const UserNavbar = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["__scopeId", "data-v-75ddbaa3"]]);
function resolveLocale(locale) {
  if (locale && SUPPORTED_LOCALES.includes(locale)) {
    return locale;
  }
  if (typeof document !== "undefined") {
    const lang = String(
      document.documentElement.getAttribute("lang") || ""
    ).trim();
    if (SUPPORTED_LOCALES.includes(lang)) {
      return lang;
    }
  }
  return "en";
}
function cmsPageUrl(slug, locale) {
  var _a, _b;
  const s = String(slug || "").trim();
  const loc = resolveLocale(locale);
  if (!s) {
    return "#";
  }
  try {
    if (typeof route === "function" && ((_b = (_a = route()).has) == null ? void 0 : _b.call(_a, "page.show"))) {
      return applyLocalePrefix(route("page.show", { slug: s }), loc);
    }
  } catch {
  }
  return localizedFallbackPath(`/${s}`, loc);
}
const TR_COUNTRY_CODE = "90";
const TR_NATIONAL_LENGTH = 10;
function normalizeTurkishPhoneDigits(phone) {
  const digits = String(phone ?? "").replace(/\D/g, "");
  if (!digits) {
    return "";
  }
  let national = digits;
  if (national.startsWith(TR_COUNTRY_CODE)) {
    national = national.slice(TR_COUNTRY_CODE.length);
  }
  if (national.startsWith("0")) {
    national = national.slice(1);
  }
  if (national.length !== TR_NATIONAL_LENGTH) {
    return "";
  }
  return `${TR_COUNTRY_CODE}${national}`;
}
function formatTurkishPhone(phone) {
  const raw = String(phone ?? "").trim();
  if (!raw) {
    return "";
  }
  const e164 = normalizeTurkishPhoneDigits(raw);
  if (!e164) {
    return raw;
  }
  const national = e164.slice(TR_COUNTRY_CODE.length);
  return `+${TR_COUNTRY_CODE} ${national.slice(0, 3)} ${national.slice(3, 6)} ${national.slice(6, 8)} ${national.slice(8, 10)}`;
}
function buildWhatsAppContactUrl(phoneOrUrl, text = "") {
  const raw = String(phoneOrUrl ?? "").trim();
  if (!raw) {
    return "";
  }
  if (/^https?:\/\//i.test(raw)) {
    if (/api\.whatsapp\.com/i.test(raw)) {
      return raw;
    }
    const waMeFromUrl = raw.match(/wa\.me\/(\d+)/i);
    if (waMeFromUrl) {
      return buildWhatsAppContactUrl(waMeFromUrl[1], text);
    }
    return raw;
  }
  const digits = raw.replace(/\D/g, "");
  if (!digits) {
    return "";
  }
  const params = new URLSearchParams({ phone: digits });
  if (text) {
    params.set("text", text);
  }
  return `https://api.whatsapp.com/send/?${params.toString()}`;
}
function resolveWhatsAppContactHref({ whatsapp = "", phone = "" } = {}) {
  const dedicated = String(whatsapp).trim();
  if (dedicated) {
    return buildWhatsAppContactUrl(dedicated);
  }
  const contactPhone = String(phone).trim();
  if (contactPhone) {
    return buildWhatsAppContactUrl(contactPhone);
  }
  return "";
}
const fallbackPhone$1 = "+456 875 369 208";
const fallbackEmail$1 = "support@example.com";
const _sfc_main$5 = {
  __name: "UserTopBar",
  __ssrInlineRender: true,
  setup(__props) {
    const page = usePage();
    const activeLocale = computed(() => page.props.locale || "en");
    const settings = computed(() => page.props.settings || {});
    const globals = computed(() => page.props.globals ?? {});
    const topBarPages = computed(
      () => {
        var _a, _b;
        return ((_b = (_a = page.props.globals) == null ? void 0 : _a.pages) == null ? void 0 : _b.top_bar) ?? [];
      }
    );
    const rawPhone = computed(
      () => String(settings.value.contact_phone || settings.value.phone || "").trim()
    );
    const phoneDisplay = computed(() => {
      const raw = rawPhone.value;
      if (raw) {
        return formatTurkishPhone(raw);
      }
      return formatTurkishPhone(fallbackPhone$1) || fallbackPhone$1;
    });
    const emailDisplay = computed(
      () => String(settings.value.contact_email || settings.value.email || "").trim() || fallbackEmail$1
    );
    const hasContactInfo = computed(
      () => Boolean(phoneDisplay.value || emailDisplay.value)
    );
    const phoneHref = computed(() => {
      const social = globals.value.social ?? {};
      const contact = globals.value.contact ?? {};
      const raw = rawPhone.value || contact.phone || settings.value.contact_phone || settings.value.phone || fallbackPhone$1;
      const normalized = normalizeTurkishPhoneDigits(raw);
      const phoneForWhatsApp = normalized ? `+${normalized}` : raw;
      return resolveWhatsAppContactHref({
        whatsapp: social.whatsapp || settings.value.whatsapp,
        phone: phoneForWhatsApp
      });
    });
    const emailHref = computed(() => {
      const e = String(settings.value.contact_email || settings.value.email || "").trim();
      return `mailto:${e || fallbackEmail$1}`;
    });
    const topSocialLinks = computed(() => {
      const s = settings.value;
      const defs = [
        { key: "facebook", label: "Facebook", icon: "fa fa-facebook" },
        { key: "twitter", label: "Twitter", icon: "fa fa-twitter" },
        { key: "instagram", label: "Instagram", icon: "fab fa-instagram" },
        { key: "youtube", label: "YouTube", icon: "fa fa-youtube" },
        { key: "tiktok", label: "TikTok", icon: "fab fa-tiktok" }
      ];
      return defs.map((d) => {
        const raw = String(s[d.key] ?? "").trim();
        if (!raw) {
          return null;
        }
        return { ...d, href: raw };
      }).filter(Boolean);
    });
    function trans(key) {
      var _a;
      return ((_a = page.props.translations) == null ? void 0 : _a[key]) ?? key;
    }
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({
        class: "imas-top-bar topbar",
        role: "region",
        "aria-label": trans("Contacts")
      }, _attrs))} data-v-6d1345a8><div class="container imas-nav__container imas-top-bar__inner" data-v-6d1345a8><div class="imas-top-bar__contacts contact" data-v-6d1345a8>`);
      if (phoneDisplay.value && phoneHref.value) {
        _push(`<a class="imas-top-bar__link"${ssrRenderAttr("href", phoneHref.value)} target="_blank" rel="noopener noreferrer" data-v-6d1345a8><i class="fa fa-phone" aria-hidden="true" data-v-6d1345a8></i><span class="imas-top-bar__phone" dir="ltr" data-v-6d1345a8>${ssrInterpolate(phoneDisplay.value)}</span></a>`);
      } else {
        _push(`<!---->`);
      }
      if (emailDisplay.value) {
        _push(`<a class="imas-top-bar__link"${ssrRenderAttr("href", emailHref.value)} data-v-6d1345a8><i class="fa fa-envelope" aria-hidden="true" data-v-6d1345a8></i><span data-v-6d1345a8>${ssrInterpolate(emailDisplay.value)}</span></a>`);
      } else {
        _push(`<!---->`);
      }
      if (topBarPages.value.length && hasContactInfo.value) {
        _push(`<span class="imas-top-bar__separator" aria-hidden="true" data-v-6d1345a8>|</span>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<!--[-->`);
      ssrRenderList(topBarPages.value, (p) => {
        _push(ssrRenderComponent(unref(Link), {
          key: p.id,
          class: "imas-top-bar__link imas-top-bar__page-link",
          href: unref(cmsPageUrl)(p.slug, activeLocale.value)
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`${ssrInterpolate(p.title)}`);
            } else {
              return [
                createTextVNode(toDisplayString(p.title), 1)
              ];
            }
          }),
          _: 2
        }, _parent));
      });
      _push(`<!--]--></div>`);
      if (topSocialLinks.value.length) {
        _push(`<ul class="imas-top-bar__socials socials"${ssrRenderAttr("aria-label", trans("Social media"))} data-v-6d1345a8><!--[-->`);
        ssrRenderList(topSocialLinks.value, (item) => {
          _push(`<li data-v-6d1345a8><a${ssrRenderAttr("href", item.href)} target="_blank" rel="noopener noreferrer"${ssrRenderAttr("aria-label", item.label)} data-v-6d1345a8><i class="${ssrRenderClass(item.icon)}" aria-hidden="true" data-v-6d1345a8></i></a></li>`);
        });
        _push(`<!--]--></ul>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div>`);
    };
  }
};
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/Findhouses/UserTopBar.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const UserTopBar = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["__scopeId", "data-v-6d1345a8"]]);
const websiteSlogan = "MOST ACCURATE SOLUTIONS";
const fallbackAddress = "95 South Park Avenue, USA";
const fallbackPhone = "+456 875 369 208";
const fallbackEmail = "support@example.com";
const _sfc_main$4 = {
  __name: "UserFooter",
  __ssrInlineRender: true,
  props: {
    navLinks: {
      type: Array,
      default: () => []
    }
  },
  setup(__props) {
    const props = __props;
    const page = usePage();
    ref(null);
    const showSubscriptionSuccess = ref(false);
    const subscribeForm = useForm({
      email: ""
    });
    computed(() => {
      const url = page.props.subscribe_store_url;
      return typeof url === "string" ? url.trim() : "";
    });
    computed(() => page.props.theme_url || "");
    computed(() => page.props.auth);
    const appName = computed(() => page.props.appName);
    const settings = computed(() => page.props.settings || {});
    const globals = computed(() => page.props.globals ?? {});
    const mediaData = computed(() => page.props.globals.media || {});
    const logoUrl = computed(() => {
      const m = mediaData.value;
      return m.transparent_logo || m.white_logo || "";
    });
    const websiteName = computed(
      () => {
        var _a, _b, _c;
        return ((_c = (_b = (_a = page.props.globals) == null ? void 0 : _a.seo) == null ? void 0 : _b.website_name) == null ? void 0 : _c.toUpperCase()) || "";
      }
    );
    const year = computed(() => (/* @__PURE__ */ new Date()).getFullYear());
    const developedByPrefix = computed(() => {
      const full = trans("Developed By Symfonix");
      return full.replace(/\s*Symfonix\s*$/i, "").trim() || "Developed by";
    });
    computed(() => settings.value.tagline || page.props.appName);
    const rawPhone = computed(
      () => String(settings.value.contact_phone || settings.value.phone || "").trim()
    );
    const phoneDisplay = computed(() => {
      const raw = rawPhone.value;
      if (raw) {
        return formatTurkishPhone(raw);
      }
      return formatTurkishPhone(fallbackPhone) || fallbackPhone;
    });
    const phoneHref = computed(() => {
      const social = globals.value.social ?? {};
      const contact = globals.value.contact ?? {};
      const raw = rawPhone.value || contact.phone || settings.value.contact_phone || settings.value.phone || fallbackPhone;
      const normalized = normalizeTurkishPhoneDigits(raw);
      const phoneForWhatsApp = normalized ? `+${normalized}` : raw;
      return resolveWhatsAppContactHref({
        whatsapp: social.whatsapp || settings.value.whatsapp,
        phone: phoneForWhatsApp
      });
    });
    const mainNavLinks = computed(
      () => (props.navLinks || []).filter((l) => l == null ? void 0 : l.href)
    );
    computed(() => {
      const pages = (props.navLinks || []).find((l) => {
        var _a;
        return (_a = l == null ? void 0 : l.children) == null ? void 0 : _a.length;
      });
      return (pages == null ? void 0 : pages.children) || [];
    });
    const activeLocale = computed(() => page.props.locale || "en");
    const footerPagesLinks = computed(
      () => {
        var _a, _b;
        return (((_b = (_a = page.props.globals) == null ? void 0 : _a.pages) == null ? void 0 : _b.footer) ?? []).map((p) => ({
          key: `footer-page-${p.id}`,
          label: p.title,
          href: cmsPageUrl(p.slug, activeLocale.value)
        }));
      }
    );
    const bottomBarPages = computed(
      () => {
        var _a, _b;
        return ((_b = (_a = page.props.globals) == null ? void 0 : _a.pages) == null ? void 0 : _b.bottom_bar) ?? [];
      }
    );
    const footerSocialLinks = computed(() => {
      const s = settings.value;
      const defs = [
        { key: "facebook", label: "Facebook", icon: "fa fa-facebook" },
        { key: "twitter", label: "Twitter", icon: "fa fa-twitter" },
        { key: "instagram", label: "Instagram", icon: "fab fa-instagram" },
        { key: "youtube", label: "YouTube", icon: "fa fa-youtube" },
        { key: "tiktok", label: "TikTok", icon: "fab fa-tiktok" }
      ];
      return defs.map((d) => {
        const raw = String(s[d.key] ?? "").trim();
        if (!raw) {
          return null;
        }
        return { ...d, href: raw };
      }).filter(Boolean);
    });
    function trans(key) {
      var _a;
      return ((_a = page.props.translations) == null ? void 0 : _a[key]) ?? key;
    }
    onBeforeUnmount(() => {
    });
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<!--[--><footer class="first-footer rec-pro imas-blog-footer" data-v-dffb6a2a><div class="top-footer" data-v-dffb6a2a><div class="container imas-footer-wrap" data-v-dffb6a2a><div class="row imas-footer-grid" data-v-dffb6a2a><div class="col-lg-3 col-md-6 f-col imas-footer-col--brand" data-v-dffb6a2a><div class="netabout" data-v-dffb6a2a><div class="brand-line" data-v-dffb6a2a><div class="logo" data-v-dffb6a2a><img${ssrRenderAttr("src", logoUrl.value)} alt="logo" class="footer_logo" data-v-dffb6a2a></div><div class="imas-brand-text" data-v-dffb6a2a><span class="website-name" data-v-dffb6a2a>${ssrInterpolate(websiteName.value)}</span><span class="website-slogan" data-v-dffb6a2a>${ssrInterpolate(websiteSlogan)}</span></div></div></div><div class="contactus text-start" data-v-dffb6a2a><ul data-v-dffb6a2a><li class="contact-line" data-v-dffb6a2a><div class="info" data-v-dffb6a2a><span class="ic" aria-hidden="true" data-v-dffb6a2a><i class="fa fa-map-marker" data-v-dffb6a2a></i></span><p class="in-p" data-v-dffb6a2a>${ssrInterpolate(settings.value.contact_address || fallbackAddress)}</p></div></li><li class="contact-line" data-v-dffb6a2a><div class="info" data-v-dffb6a2a><span class="ic" aria-hidden="true" data-v-dffb6a2a><i class="fa fa-phone" data-v-dffb6a2a></i></span><p class="in-p in-p--phone" dir="ltr" data-v-dffb6a2a>`);
      if (phoneDisplay.value && phoneHref.value) {
        _push(`<span class="in-p-link-wrap" data-v-dffb6a2a><a${ssrRenderAttr("href", phoneHref.value)} target="_blank" rel="noopener noreferrer" data-v-dffb6a2a>${ssrInterpolate(phoneDisplay.value)}</a></span>`);
      } else if (phoneDisplay.value) {
        _push(`<!--[-->${ssrInterpolate(phoneDisplay.value)}<!--]-->`);
      } else {
        _push(`<!---->`);
      }
      _push(`</p></div></li><li class="contact-line" data-v-dffb6a2a><div class="info" data-v-dffb6a2a><span class="ic" aria-hidden="true" data-v-dffb6a2a><i class="fa fa-envelope" data-v-dffb6a2a></i></span><p class="in-p ti" data-v-dffb6a2a>${ssrInterpolate(settings.value.contact_email || fallbackEmail)}</p></div></li></ul></div></div><div class="col-lg-3 col-md-6 f-col" data-v-dffb6a2a><div class="navigation text-start" data-v-dffb6a2a><h3 data-v-dffb6a2a>${ssrInterpolate(trans("navBar.navigation"))}</h3><div class="nav-footer text-start" data-v-dffb6a2a><ul class="links" data-v-dffb6a2a><!--[-->`);
      ssrRenderList(mainNavLinks.value, (item) => {
        _push(`<li data-v-dffb6a2a>`);
        _push(ssrRenderComponent(unref(Link), {
          href: item.href
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`${ssrInterpolate(trans(item.key))}`);
            } else {
              return [
                createTextVNode(toDisplayString(trans(item.key)), 1)
              ];
            }
          }),
          _: 2
        }, _parent));
        _push(`</li>`);
      });
      _push(`<!--]--></ul></div></div></div><div class="col-lg-3 col-md-6 f-col" data-v-dffb6a2a><div class="navigation text-start" data-v-dffb6a2a><h3 data-v-dffb6a2a>${ssrInterpolate(trans("navBar.useful_links"))}</h3><ul class="links links--single" data-v-dffb6a2a><!--[-->`);
      ssrRenderList(footerPagesLinks.value, (item) => {
        _push(`<li data-v-dffb6a2a>`);
        _push(ssrRenderComponent(unref(Link), {
          href: item.href
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`${ssrInterpolate(item.label)}`);
            } else {
              return [
                createTextVNode(toDisplayString(item.label), 1)
              ];
            }
          }),
          _: 2
        }, _parent));
        _push(`</li>`);
      });
      _push(`<!--]--></ul></div></div><div class="col-lg-3 col-md-6 f-col" data-v-dffb6a2a><div class="newsletters text-start" data-v-dffb6a2a><h3 data-v-dffb6a2a>${ssrInterpolate(trans("navBar.newsLetters"))}</h3><p data-v-dffb6a2a>${ssrInterpolate(trans("navBar.signup_for_newsletters"))}</p></div><form class="bloq-email mailchimp form-inline newsletter" data-v-dffb6a2a><div class="email" data-v-dffb6a2a><input id="subscribeEmail"${ssrRenderAttr("value", unref(subscribeForm).email)} type="email" name="email" required maxlength="255"${ssrRenderAttr(
        "placeholder",
        trans("navBar.enter_your_email")
      )}${ssrIncludeBooleanAttr(unref(subscribeForm).processing) ? " disabled" : ""} class="${ssrRenderClass({
        "is-invalid": unref(subscribeForm).errors.email
      })}" data-v-dffb6a2a><button type="submit"${ssrIncludeBooleanAttr(unref(subscribeForm).processing) ? " disabled" : ""} data-v-dffb6a2a>${ssrInterpolate(trans("navBar.subscribe"))}</button></div>`);
      if (unref(subscribeForm).errors.email) {
        _push(`<p class="subscription-error" role="alert" data-v-dffb6a2a>${ssrInterpolate(unref(subscribeForm).errors.email)}</p>`);
      } else {
        _push(`<!---->`);
      }
      if (showSubscriptionSuccess.value) {
        _push(`<p class="subscription-success" role="status" data-v-dffb6a2a>${ssrInterpolate(trans("navBar.subscription_success"))}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</form>`);
      if (footerSocialLinks.value.length) {
        _push(`<div class="socials imas-footer-socials"${ssrRenderAttr("aria-label", trans("Social media"))} data-v-dffb6a2a><!--[-->`);
        ssrRenderList(footerSocialLinks.value, (item) => {
          _push(`<a${ssrRenderAttr("href", item.href)} target="_blank" rel="noopener noreferrer"${ssrRenderAttr("aria-label", item.label)} data-v-dffb6a2a><i class="${ssrRenderClass(item.icon)}" aria-hidden="true" data-v-dffb6a2a></i></a>`);
        });
        _push(`<!--]--></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div></div></div><div class="second-footer rec-pro copyright" data-v-dffb6a2a><div class="container imas-footer-wrap imas-second-footer__inner" data-v-dffb6a2a>`);
      if (bottomBarPages.value.length) {
        _push(`<nav class="imas-second-footer__bottom-bar"${ssrRenderAttr("aria-label", trans("navBar.useful_links"))} data-v-dffb6a2a><!--[-->`);
        ssrRenderList(bottomBarPages.value, (p, index) => {
          _push(`<!--[-->`);
          if (index > 0) {
            _push(`<span class="imas-second-footer__separator" aria-hidden="true" data-v-dffb6a2a>|</span>`);
          } else {
            _push(`<!---->`);
          }
          _push(ssrRenderComponent(unref(Link), {
            class: "imas-second-footer__page-link",
            href: unref(cmsPageUrl)(p.slug, activeLocale.value)
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`${ssrInterpolate(p.title)}`);
              } else {
                return [
                  createTextVNode(toDisplayString(p.title), 1)
                ];
              }
            }),
            _: 2
          }, _parent));
          _push(`<!--]-->`);
        });
        _push(`<!--]--></nav>`);
      } else {
        _push(`<div class="imas-second-footer__bottom-bar imas-second-footer__bottom-bar--empty" aria-hidden="true" data-v-dffb6a2a></div>`);
      }
      _push(`<p class="imas-second-footer__copy" data-v-dffb6a2a>${ssrInterpolate(year.value)} © ${ssrInterpolate(appName.value)} — ${ssrInterpolate(trans("navBar.All Rights Reserved"))}</p><p class="imas-second-footer__developer" data-v-dffb6a2a><span data-v-dffb6a2a>${ssrInterpolate(developedByPrefix.value)}</span><a href="https://symfonix.io/" target="_blank" rel="noopener noreferrer" class="imas-second-footer__developer-link" data-v-dffb6a2a>Symfonix</a></p></div></div></footer><a data-scroll href="#wrapper" class="go-up" data-v-dffb6a2a><i class="fa fa-angle-double-up" aria-hidden="true" data-v-dffb6a2a></i></a><!--]-->`);
    };
  }
};
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/Findhouses/UserFooter.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const UserFooter = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["__scopeId", "data-v-dffb6a2a"]]);
function buildGmailComposeUrl(email, options = {}) {
  const to = String(email ?? "").trim();
  if (!to) {
    return "";
  }
  const params = new URLSearchParams({
    view: "cm",
    fs: "1",
    to
  });
  const subject = String(options.subject ?? "").trim();
  const body = String(options.body ?? "").trim();
  if (subject) {
    params.set("su", subject);
  }
  if (body) {
    params.set("body", body);
  }
  return `https://mail.google.com/mail/?${params.toString()}`;
}
const MESSENGER_URL = "https://m.me/61584547460936";
const _sfc_main$3 = {
  __name: "FloatingContactButton",
  __ssrInlineRender: true,
  setup(__props) {
    const page = usePage();
    const isOpen = ref(false);
    const globals = computed(() => page.props.globals ?? {});
    const settings = computed(() => page.props.settings ?? {});
    function trans(key) {
      var _a;
      return ((_a = page.props.translations) == null ? void 0 : _a[key]) || key;
    }
    const contactPhone = computed(() => {
      const contact = globals.value.contact ?? {};
      return String(
        contact.phone || settings.value.contact_phone || settings.value.phone || ""
      ).trim();
    });
    const contactEmail = computed(() => {
      const contact = globals.value.contact ?? {};
      return String(
        contact.email || settings.value.contact_email || settings.value.email || ""
      ).trim();
    });
    const messengerHref = computed(() => MESSENGER_URL);
    const gmailHref = computed(() => buildGmailComposeUrl(contactEmail.value));
    const phoneHref = computed(() => {
      const raw = contactPhone.value;
      const digits = raw.replace(/[^\d+]/g, "");
      return digits ? `tel:${digits}` : "";
    });
    const hasAnyChannel = computed(
      () => Boolean(messengerHref.value || gmailHref.value || phoneHref.value)
    );
    const menuTitle = computed(
      () => trans("floating_contact.menu_title") || "Talk to us on your favorite channel"
    );
    const labelMessenger = computed(
      () => trans("floating_contact.messenger") || "Messenger chat"
    );
    const labelGmail = computed(
      () => trans("floating_contact.gmail") || "Gmail"
    );
    const labelDirectCall = computed(
      () => trans("floating_contact.direct_call") || "Direct call"
    );
    const menuAriaLabel = computed(
      () => trans("floating_contact.menu_aria") || "Contact channels"
    );
    const toggleAriaLabel = computed(
      () => isOpen.value ? trans("floating_contact.aria_close") || "Close contact menu" : trans("floating_contact.aria_open") || "Open contact menu"
    );
    function onDocumentClick(event) {
      var _a, _b;
      if (!isOpen.value) {
        return;
      }
      const root = (_b = (_a = event.target) == null ? void 0 : _a.closest) == null ? void 0 : _b.call(_a, ".imas-floating-contact");
      if (!root) {
        isOpen.value = false;
      }
    }
    function onEscape(event) {
      if (event.key === "Escape" && isOpen.value) {
        isOpen.value = false;
      }
    }
    onMounted(() => {
      document.addEventListener("click", onDocumentClick);
      document.addEventListener("keydown", onEscape);
    });
    onBeforeUnmount(() => {
      document.removeEventListener("click", onDocumentClick);
      document.removeEventListener("keydown", onEscape);
    });
    return (_ctx, _push, _parent, _attrs) => {
      if (hasAnyChannel.value) {
        _push(`<div${ssrRenderAttrs(mergeProps({
          class: ["imas-floating-contact", { "imas-floating-contact--open": isOpen.value }]
        }, _attrs))} data-v-b36ae6be><div style="${ssrRenderStyle(isOpen.value ? null : { display: "none" })}" id="imas-floating-contact-menu" class="imas-floating-contact__panel" role="dialog"${ssrRenderAttr("aria-label", menuAriaLabel.value)} data-v-b36ae6be><p class="imas-floating-contact__title text-md font-semibold" data-v-b36ae6be>${ssrInterpolate(menuTitle.value)}</p><ul class="imas-floating-contact__list" data-v-b36ae6be>`);
        if (messengerHref.value) {
          _push(`<li data-v-b36ae6be><a${ssrRenderAttr("href", messengerHref.value)} class="imas-floating-contact__item" target="_blank" rel="noopener noreferrer" data-v-b36ae6be><span class="imas-floating-contact__icon imas-floating-contact__icon--messenger" aria-hidden="true" data-v-b36ae6be><i class="fab fa-facebook-messenger" data-v-b36ae6be></i></span><span class="imas-floating-contact__label text-sm font-medium" data-v-b36ae6be>${ssrInterpolate(labelMessenger.value)}</span></a></li>`);
        } else {
          _push(`<!---->`);
        }
        if (gmailHref.value) {
          _push(`<li data-v-b36ae6be><a${ssrRenderAttr("href", gmailHref.value)} class="imas-floating-contact__item" target="_blank" rel="noopener noreferrer" data-v-b36ae6be><span class="imas-floating-contact__icon imas-floating-contact__icon--gmail" aria-hidden="true" data-v-b36ae6be><i class="fab fa-google" data-v-b36ae6be></i></span><span class="imas-floating-contact__label text-sm font-medium" data-v-b36ae6be>${ssrInterpolate(labelGmail.value)}</span></a></li>`);
        } else {
          _push(`<!---->`);
        }
        if (phoneHref.value) {
          _push(`<li data-v-b36ae6be><a${ssrRenderAttr("href", phoneHref.value)} class="imas-floating-contact__item" data-v-b36ae6be><span class="imas-floating-contact__icon imas-floating-contact__icon--phone" aria-hidden="true" data-v-b36ae6be><i class="fa fa-phone" data-v-b36ae6be></i></span><span class="imas-floating-contact__label text-sm font-medium" data-v-b36ae6be>${ssrInterpolate(labelDirectCall.value)}</span></a></li>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</ul></div><button type="button" class="imas-floating-contact__toggle"${ssrRenderAttr("aria-expanded", isOpen.value)}${ssrRenderAttr("aria-controls", isOpen.value ? "imas-floating-contact-menu" : void 0)}${ssrRenderAttr("aria-label", toggleAriaLabel.value)} data-v-b36ae6be><i class="${ssrRenderClass([isOpen.value ? "fa-times" : "fa-phone", "fa"])}" aria-hidden="true" data-v-b36ae6be></i></button></div>`);
      } else {
        _push(`<!---->`);
      }
    };
  }
};
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Global/FloatingContactButton.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const FloatingContactButton = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["__scopeId", "data-v-b36ae6be"]]);
const _sfc_main$2 = {
  __name: "FloatingWhatsAppButton",
  __ssrInlineRender: true,
  setup(__props) {
    const page = usePage();
    const globals = computed(() => page.props.globals ?? {});
    const settings = computed(() => page.props.settings ?? {});
    const whatsappHref = computed(() => {
      const social = globals.value.social ?? {};
      const contact = globals.value.contact ?? {};
      return resolveWhatsAppContactHref({
        whatsapp: social.whatsapp,
        phone: contact.phone || settings.value.contact_phone || settings.value.phone
      });
    });
    function trans(key) {
      var _a;
      return ((_a = page.props.translations) == null ? void 0 : _a[key]) || key;
    }
    const ariaLabel = computed(
      () => trans("floating_whatsapp.aria_label") || "Contact us on WhatsApp"
    );
    return (_ctx, _push, _parent, _attrs) => {
      if (whatsappHref.value) {
        _push(`<a${ssrRenderAttrs(mergeProps({
          href: whatsappHref.value,
          class: "imas-floating-whatsapp",
          target: "_blank",
          rel: "noopener noreferrer",
          "aria-label": ariaLabel.value
        }, _attrs))} data-v-56582279><i class="fa fa-whatsapp" aria-hidden="true" data-v-56582279></i></a>`);
      } else {
        _push(`<!---->`);
      }
    };
  }
};
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Global/FloatingWhatsAppButton.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const FloatingWhatsAppButton = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["__scopeId", "data-v-56582279"]]);
function syncZiggy(ziggy) {
  if (typeof window === "undefined" || !ziggy || typeof ziggy !== "object") {
    return;
  }
  if (!window.Ziggy) {
    window.Ziggy = ziggy;
    return;
  }
  if (ziggy.url) {
    window.Ziggy.url = ziggy.url;
  }
  if (ziggy.location) {
    window.Ziggy.location = ziggy.location;
  }
  if (ziggy.routes) {
    window.Ziggy.routes = ziggy.routes;
  }
}
function isBrowser() {
  return typeof window !== "undefined" && typeof document !== "undefined";
}
const _sfc_main$1 = {
  __name: "ClientOnly",
  __ssrInlineRender: true,
  setup(__props) {
    const mounted = ref(false);
    onMounted(() => {
      mounted.value = true;
    });
    return (_ctx, _push, _parent, _attrs) => {
      if (mounted.value) {
        ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
      } else {
        ssrRenderSlot(_ctx.$slots, "placeholder", {}, null, _push, _parent);
      }
    };
  }
};
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Global/ClientOnly.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = {
  __name: "App",
  __ssrInlineRender: true,
  setup(__props) {
    const page = usePage();
    const activeLocale = computed(() => page.props.locale || "en");
    const siteName = computed(() => String(page.props.appName || "").trim());
    const OG_LOCALE_MAP = {
      en: "en_US",
      tr: "tr_TR",
      ar: "ar_AR"
    };
    function toOgLocale(code) {
      const key = String(code || "").toLowerCase();
      return OG_LOCALE_MAP[key] || key;
    }
    const ogLocale = computed(() => toOgLocale(activeLocale.value));
    const ogLocaleAlternates = computed(() => {
      const switcher = page.props.locale_switcher ?? [];
      if (!Array.isArray(switcher)) {
        return [];
      }
      return switcher.map((item) => String((item == null ? void 0 : item.code) ?? "")).filter((code) => code !== "" && code !== activeLocale.value).map((code) => ({
        key: `og-locale-alt-${code}`,
        value: toOgLocale(code)
      }));
    });
    const hreflangAlternates = computed(() => {
      const switcher = page.props.locale_switcher ?? [];
      if (!Array.isArray(switcher) || switcher.length === 0) {
        return [];
      }
      const items = switcher.filter((item) => typeof (item == null ? void 0 : item.url) === "string" && item.url.trim() !== "").map((item) => ({
        hreflang: String(item.code ?? ""),
        url: item.url.trim(),
        key: `hreflang-${item.code}`
      }));
      const en = switcher.find((item) => item.code === "en");
      if ((en == null ? void 0 : en.url) && typeof en.url === "string" && en.url.trim() !== "") {
        items.push({
          hreflang: "x-default",
          url: en.url.trim(),
          key: "hreflang-x-default"
        });
      }
      return items;
    });
    const navbarTransparent = computed(() => {
      var _a, _b;
      try {
        if (typeof route === "function" && ((_b = (_a = route()).current) == null ? void 0 : _b.call(_a, "home"))) {
          return true;
        }
      } catch {
      }
      const c = String(page.component || "");
      return /^Base(::|\/)Index$/i.test(c);
    });
    function safeRoute(name, fallbackHref = "#") {
      return localizedRoute(name, {}, activeLocale.value, fallbackHref);
    }
    function blogCategoryUrl(categoryId) {
      const base = localizedRoute(
        "blog.index",
        {},
        activeLocale.value,
        "/blog"
      );
      const sep = base.includes("?") ? "&" : "?";
      return `${base}${sep}category_id=${categoryId}`;
    }
    const blogNavCategories = computed(
      () => {
        var _a;
        return ((_a = page.props.globals) == null ? void 0 : _a.blog_categories) ?? [];
      }
    );
    const navbarPages = computed(() => {
      var _a, _b;
      return ((_b = (_a = page.props.globals) == null ? void 0 : _a.pages) == null ? void 0 : _b.navbar) ?? [];
    });
    const navLinks = computed(() => {
      const loc = activeLocale.value;
      const blogCategoryChildren = blogNavCategories.value.map((c) => ({
        key: `blog-category-${c.id}`,
        label: c.name,
        href: blogCategoryUrl(c.id)
      }));
      const blogsNav = {
        key: "navBar.Blogs",
        href: safeRoute("blog.index", "/blog"),
        ...blogCategoryChildren.length > 0 ? { children: blogCategoryChildren } : {}
      };
      const pageNavChildren = navbarPages.value.map((p) => ({
        key: `page-${p.id}`,
        label: p.title,
        href: cmsPageUrl(p.slug, loc)
      }));
      const links = [
        { key: "navBar.Home", href: safeRoute("home", "/") },
        { key: "navBar.Buy Real Estate", href: safeRoute("property.index", "/property") },
        {
          key: "navBar.Turkish Citizenship",
          href: safeRoute("turkish-citizenship", "/turkish-citizenship")
        },
        blogsNav
      ];
      if (pageNavChildren.length > 0) {
        links.push({
          key: "navBar.Pages",
          children: pageNavChildren
        });
      }
      links.push({
        key: "about_us.title",
        href: safeRoute("about-us", "/about-us")
      });
      links.push({
        key: "navBar.Contact us",
        href: safeRoute("support.contact-us", "/contact-us")
      });
      return links;
    });
    function syncDocumentTextDirection() {
      if (!isBrowser()) {
        return;
      }
      const locale = activeLocale.value;
      const dir = page.props.text_direction || (locale === "ar" ? "rtl" : "ltr");
      document.documentElement.setAttribute("lang", String(locale));
      document.documentElement.setAttribute("dir", String(dir));
    }
    onMounted(() => {
      syncDocumentTextDirection();
    });
    watch(
      () => [activeLocale.value, page.props.text_direction],
      () => syncDocumentTextDirection(),
      { immediate: false }
    );
    watch(
      () => page.props.ziggy,
      (ziggy) => syncZiggy(ziggy),
      { immediate: true, deep: true }
    );
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<!--[-->`);
      _push(ssrRenderComponent(unref(Head), null, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (siteName.value) {
              _push2(`<meta head-key="og:site_name" property="og:site_name"${ssrRenderAttr("content", siteName.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<meta head-key="og:locale" property="og:locale"${ssrRenderAttr("content", ogLocale.value)}${_scopeId}><!--[-->`);
            ssrRenderList(ogLocaleAlternates.value, (alt) => {
              _push2(`<meta${ssrRenderAttr("head-key", alt.key)} property="og:locale:alternate"${ssrRenderAttr("content", alt.value)}${_scopeId}>`);
            });
            _push2(`<!--]--><!--[-->`);
            ssrRenderList(hreflangAlternates.value, (alt) => {
              _push2(`<link${ssrRenderAttr("head-key", alt.key)} rel="alternate"${ssrRenderAttr("hreflang", alt.hreflang)}${ssrRenderAttr("href", alt.url)}${_scopeId}>`);
            });
            _push2(`<!--]-->`);
          } else {
            return [
              siteName.value ? (openBlock(), createBlock("meta", {
                key: 0,
                "head-key": "og:site_name",
                property: "og:site_name",
                content: siteName.value
              }, null, 8, ["content"])) : createCommentVNode("", true),
              createVNode("meta", {
                "head-key": "og:locale",
                property: "og:locale",
                content: ogLocale.value
              }, null, 8, ["content"]),
              (openBlock(true), createBlock(Fragment, null, renderList(ogLocaleAlternates.value, (alt) => {
                return openBlock(), createBlock("meta", {
                  key: alt.key,
                  "head-key": alt.key,
                  property: "og:locale:alternate",
                  content: alt.value
                }, null, 8, ["head-key", "content"]);
              }), 128)),
              (openBlock(true), createBlock(Fragment, null, renderList(hreflangAlternates.value, (alt) => {
                return openBlock(), createBlock("link", {
                  key: alt.key,
                  "head-key": alt.key,
                  rel: "alternate",
                  hreflang: alt.hreflang,
                  href: alt.url
                }, null, 8, ["head-key", "hreflang", "href"]);
              }), 128))
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div id="wrapper" class="imas-theme-dark">`);
      _push(ssrRenderComponent(UserTopBar, null, null, _parent));
      _push(ssrRenderComponent(UserNavbar, {
        "nav-links": navLinks.value,
        "transparent-navbar": navbarTransparent.value
      }, null, _parent));
      _push(`<div class="clearfix"></div>`);
      ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
      _push(ssrRenderComponent(UserFooter, { "nav-links": navLinks.value }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$1, null, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(FloatingContactButton, null, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(FloatingWhatsAppButton, null, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(FloatingContactButton),
              createVNode(FloatingWhatsAppButton)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div><!--]-->`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/App.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as _,
  cmsPageUrl as c,
  formatTurkishPhone as f,
  localizedRoute as l,
  normalizeTurkishPhoneDigits as n,
  resolveWhatsAppContactHref as r,
  useGsap as u
};
