import { a as refreshScrollTrigger, f as _plugin_vue_export_helper_default, i as prefersReducedMotion, m as IMAS_OPEN_AUTH_EVENT, r as createGsapContext } from "../ssr.js";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { Fragment, computed, createBlock, createCommentVNode, createTextVNode, createVNode, inject, mergeProps, nextTick, onBeforeUnmount, onMounted, openBlock, ref, renderList, shallowRef, toDisplayString, unref, useSSRContext, watch, withCtx } from "vue";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrLooseContain, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderDynamicModel, ssrRenderList, ssrRenderSlot, ssrRenderStyle, ssrRenderTeleport } from "vue/server-renderer";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
//#region resources/js/composables/useGsap.js
/**
* Vue composable for GSAP in SFCs. Scopes animations to a template ref root
* and reverts them on unmount (safe with Inertia client navigations).
*
* @example
* const sectionRef = ref(null);
* const { context } = useGsap();
* onMounted(() => {
*   context(() => {
*     gsap.from(sectionRef.value, { opacity: 0, y: 24 });
*   }, sectionRef);
* });
*/
function useGsap() {
	const gsapInstance = inject("gsap", gsap);
	const scrollTrigger = inject("ScrollTrigger", ScrollTrigger);
	const ctxRef = shallowRef(null);
	/**
	* @param {() => void} fn
	* @param {import('vue').Ref | import('vue').ComponentPublicInstance | Element | null | undefined} scope
	*/
	function context(fn, scope) {
		ctxRef.value?.revert?.();
		ctxRef.value = createGsapContext(fn, scope?.value ?? scope ?? void 0);
		return ctxRef.value;
	}
	onBeforeUnmount(() => {
		ctxRef.value?.revert?.();
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
//#endregion
//#region resources/js/Layouts/Findhouses/AuthModal.vue
var _sfc_main$7 = {
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
			return page.props.translations?.[key] ?? key;
		}
		const authSubview = ref(null);
		const activeMainTab = ref("login");
		const authNoteText = computed(() => {
			if (authSubview.value === "forgot") return trans("Forgot Password");
			if (activeMainTab.value === "register") return trans("RegisterNote");
			if (activeMainTab.value === "reset") return trans("Reset Password");
			return trans("LoginNote");
		});
		const authStatusMessage = computed(() => page.props.flash?.status || "");
		const resetToken = ref("");
		const seo = computed(() => page.props.globals.seo || {});
		const appName = computed(() => String(seo.value.main_title || ""));
		computed(() => {
			const name = appName.value.trim();
			if (!name) return "<strong></strong>";
			const parts = name.split(/\s+/);
			if (parts.length >= 2) {
				const last = parts.pop();
				return `${escapeHtml(parts.join(" "))} <strong>${escapeHtml(last)}</strong>`;
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
		const countries = computed(() => page.props.globals?.countries ?? []);
		const countriesWithPhone = computed(() => {
			const list = countries.value.filter((c) => String(c.phone_code ?? "").trim() !== "");
			return list.length ? list : countries.value;
		});
		const selectedRegisterCountry = computed(() => {
			const list = countriesWithPhone.value;
			const id = registerCountryId.value;
			if (id == null || !list.length) return null;
			return list.find((c) => c.id === id) ?? null;
		});
		const countriesWithPhoneFiltered = computed(() => {
			const list = countriesWithPhone.value;
			const raw = registerCountrySearchQuery.value.trim();
			if (!raw) return list;
			const qDigits = digitsOnly(raw);
			const alphaQuery = raw.replace(/[\d+()\-\s]/g, "").trim().toLowerCase();
			return list.filter((c) => {
				const codeDigits = digitsOnly(c.phone_code);
				if (qDigits.length > 0 && codeDigits.startsWith(qDigits)) return true;
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
			if (registerCountryId.value != null && list.some((c) => c.id === registerCountryId.value)) return;
			const prefer = {
				tr: "TR",
				en: "US",
				ar: "SA"
			}[String(page.props.locale)] ?? "TR";
			registerCountryId.value = (list.find((c) => c.iso_code_2 === prefer) ?? list[0]).id;
		}
		watch(countriesWithPhone, pickDefaultRegisterCountry, { immediate: true });
		function digitsOnly(s) {
			return String(s ?? "").replace(/\D/g, "");
		}
		function displayCallingCode(phoneCode) {
			return digitsOnly(phoneCode) || "—";
		}
		function onRegisterCountryDocPointerDown(e) {
			if (!registerCountryDropdownOpen.value) return;
			const root = registerCountryDropdownRoot.value;
			if (root && !root.contains(e.target)) registerCountryDropdownOpen.value = false;
		}
		function onRegisterCountryDocKeydown(e) {
			if (e.key === "Escape") registerCountryDropdownOpen.value = false;
		}
		/**
		* Gate `<Teleport to="body">` until after mount. Inertia's Vue SSR does not emit
		* teleport-to-body content into the server HTML, so an active teleport during
		* hydration is matched against `<div id="app">` and warns. Rendering a plain
		* comment placeholder until mounted keeps server/client identical.
		*/
		const mounted = ref(false);
		onMounted(() => {
			mounted.value = true;
			document.addEventListener("pointerdown", onRegisterCountryDocPointerDown);
			document.addEventListener("keydown", onRegisterCountryDocKeydown);
		});
		const registerCountrySelectAriaLabel = computed(() => {
			const c = countriesWithPhone.value.find((x) => x.id === registerCountryId.value);
			const prefix = trans("auth_modal.country_calling_code");
			if (!c) return prefix;
			const cc = displayCallingCode(c.phone_code);
			const iso = String(c.iso_code_2 ?? "").trim().toUpperCase();
			return `${prefix}: +${cc}${iso ? `, ${iso}` : ""}`;
		});
		const forgotForm = useForm({ email: "" });
		const resetForm = useForm({
			token: "",
			email: "",
			password: "",
			password_confirmation: ""
		});
		function extractResetTokenFromLocation() {
			const match = (window.location.pathname || "").match(/\/reset-password\/([^/?#]+)/);
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
			if (em) resetForm.email = em;
		}
		watch(() => [props.open, props.startTab], ([isOpen]) => {
			document.documentElement.classList.toggle("hid-body", !!isOpen);
			document.body.classList.toggle("hid-body", !!isOpen);
			if (!isOpen) {
				authSubview.value = null;
				registerCountryDropdownOpen.value = false;
				registerCountrySearchQuery.value = "";
				return;
			}
			const start = props.startTab;
			if (start === "forgot") {
				authSubview.value = "forgot";
				activeMainTab.value = "login";
			} else {
				authSubview.value = null;
				if (start === "register") activeMainTab.value = "register";
				else if (start === "reset") activeMainTab.value = "reset";
				else activeMainTab.value = "login";
			}
			syncResetFromUrl();
		});
		watch(() => activeMainTab.value, (tab) => {
			if (tab === "reset") syncResetFromUrl();
		});
		onBeforeUnmount(() => {
			document.documentElement.classList.remove("hid-body");
			document.body.classList.remove("hid-body");
			document.removeEventListener("pointerdown", onRegisterCountryDocPointerDown);
			document.removeEventListener("keydown", onRegisterCountryDocKeydown);
		});
		const mediaData = computed(() => page.props.globals.media || {});
		const logoUrl = computed(() => {
			const m = mediaData.value;
			return m.transparent_logo || m.white_logo || "";
		});
		return (_ctx, _push, _parent, _attrs) => {
			if (mounted.value) ssrRenderTeleport(_push, (_push) => {
				if (__props.open) {
					_push(`<div class="login-and-register-form modal imas-auth-modal" role="dialog" aria-modal="true"${ssrRenderAttr("aria-label", trans("auth_modal.dialog_label"))} data-v-7a968354><div class="main-overlay" tabindex="-1" data-v-7a968354></div><div class="main-register-holder" data-v-7a968354><div class="main-register fl-wrap" data-v-7a968354><div class="close-reg" role="button" tabindex="0"${ssrRenderAttr("aria-label", trans("auth_modal.close"))} data-v-7a968354><i class="fa fa-times" aria-hidden="true" data-v-7a968354></i></div>`);
					if (logoUrl.value) _push(`<div class="app_logo" data-v-7a968354><img${ssrRenderAttr("src", logoUrl.value)}${ssrRenderAttr("data-sticky-logo", logoUrl.value)} alt="" data-v-7a968354></div>`);
					else _push(`<!---->`);
					_push(`<h3 class="text-center" data-v-7a968354>${ssrInterpolate(authNoteText.value)}</h3>`);
					if (authSubview.value === "forgot") {
						_push(`<div class="custom-form" data-v-7a968354><p class="mb-3 text-start px-0" data-v-7a968354></p><a href="#" class="imas-auth-modal__back text-start" data-v-7a968354><i class="fa fa-angle-left fa-lg imas-auth-modal__back-icon" aria-hidden="true" data-v-7a968354></i><span class="imas-auth-modal__back-label" data-v-7a968354>${ssrInterpolate(trans("auth_modal.back_to_login"))}</span></a>`);
						if (authStatusMessage.value) _push(`<p class="imas-auth-modal__status" role="status" data-v-7a968354>${ssrInterpolate(authStatusMessage.value)}</p>`);
						else _push(`<!---->`);
						_push(`<form class="forgot-password-form" data-v-7a968354><div data-v-7a968354><label for="imas-auth-forgot-email" data-v-7a968354>${ssrInterpolate(trans("Email"))} *</label><input id="imas-auth-forgot-email"${ssrRenderAttr("value", unref(forgotForm).email)} type="email" autocomplete="email" required data-v-7a968354>`);
						if (unref(forgotForm).errors.email) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(unref(forgotForm).errors.email)}</span>`);
						else _push(`<!---->`);
						_push(`<button type="submit" class="log-submit-btn"${ssrIncludeBooleanAttr(unref(forgotForm).processing) ? " disabled" : ""} data-v-7a968354><span data-v-7a968354>${ssrInterpolate(trans("Send Email Verification"))}</span></button></div></form></div>`);
					} else {
						_push(`<div id="tabs-container" data-v-7a968354>`);
						if (authStatusMessage.value && activeMainTab.value === "login") _push(`<p class="imas-auth-modal__status" role="status" data-v-7a968354>${ssrInterpolate(authStatusMessage.value)}</p>`);
						else _push(`<!---->`);
						_push(`<ul class="tabs-menu" data-v-7a968354><li class="${ssrRenderClass({ current: activeMainTab.value === "login" })}" data-v-7a968354><a href="#tab-imas-login" data-v-7a968354>${ssrInterpolate(trans("Login"))}</a></li><li class="${ssrRenderClass({ current: activeMainTab.value === "register" })}" data-v-7a968354><a href="#tab-imas-register" data-v-7a968354>${ssrInterpolate(trans("Register"))}</a></li>`);
						if (activeMainTab.value === "reset" || resetToken.value) _push(`<li class="${ssrRenderClass({ current: activeMainTab.value === "reset" })}" data-v-7a968354><a href="#tab-imas-reset" data-v-7a968354>${ssrInterpolate(trans("Reset Password"))}</a></li>`);
						else _push(`<!---->`);
						_push(`</ul><div class="tab" data-v-7a968354><div id="tab-imas-login" class="${ssrRenderClass([{ "imas-auth-tab--active": activeMainTab.value === "login" }, "tab-contents"])}" data-v-7a968354><div class="custom-form" data-v-7a968354><form data-v-7a968354><label for="imas-auth-login-email" data-v-7a968354>${ssrInterpolate(trans("Email"))} *</label><input id="imas-auth-login-email"${ssrRenderAttr("value", unref(loginForm).email)} type="email" autocomplete="username" required data-v-7a968354>`);
						if (unref(loginForm).errors.email) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(unref(loginForm).errors.email)}</span>`);
						else _push(`<!---->`);
						_push(`<label for="imas-auth-login-password" data-v-7a968354>${ssrInterpolate(trans("Password"))} *</label><div class="imas-auth-password-field" data-v-7a968354><input id="imas-auth-login-password"${ssrRenderDynamicModel(passwordVisible.value.login ? "text" : "password", unref(loginForm).password, null)}${ssrRenderAttr("type", passwordVisible.value.login ? "text" : "password")} autocomplete="current-password" required data-v-7a968354><button type="button" class="imas-auth-password-toggle"${ssrRenderAttr("aria-label", passwordToggleAria("login"))}${ssrRenderAttr("aria-pressed", passwordVisible.value.login)} data-v-7a968354><i class="${ssrRenderClass(passwordVisible.value.login ? "fa fa-eye-slash" : "fa fa-eye")}" aria-hidden="true" data-v-7a968354></i></button></div>`);
						if (unref(loginForm).errors.password) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(unref(loginForm).errors.password)}</span>`);
						else _push(`<!---->`);
						_push(`<button type="submit" class="log-submit-btn"${ssrIncludeBooleanAttr(unref(loginForm).processing) ? " disabled" : ""} data-v-7a968354><span data-v-7a968354>${ssrInterpolate(trans("Login"))}</span></button><div class="clearfix" data-v-7a968354></div><div class="filter-tags" data-v-7a968354><input id="imas-auth-remember"${ssrIncludeBooleanAttr(Array.isArray(unref(loginForm).remember) ? ssrLooseContain(unref(loginForm).remember, null) : unref(loginForm).remember) ? " checked" : ""} type="checkbox" class="mx-2 remember-me-checkbox" data-v-7a968354><label for="imas-auth-remember" data-v-7a968354>${ssrInterpolate(trans("Remember Me"))}</label></div></form><div class="lost_password" data-v-7a968354><a href="#" data-v-7a968354>${ssrInterpolate(trans("Forgot Password"))}</a></div></div></div><div class="tab" data-v-7a968354><div id="tab-imas-register" class="${ssrRenderClass([{ "imas-auth-tab--active": activeMainTab.value === "register" }, "tab-contents"])}" data-v-7a968354><div class="custom-form main-register-form" data-v-7a968354><form data-v-7a968354><div class="imas-auth-form-field-row" data-v-7a968354><div class="imas-auth-form-field" data-v-7a968354><label for="imas-auth-reg-first-name" data-v-7a968354>${ssrInterpolate(trans("contact_us.first_name"))} *</label><input id="imas-auth-reg-first-name"${ssrRenderAttr("value", unref(registerForm).first_name)} type="text" autocomplete="given-name" required maxlength="120" data-v-7a968354>`);
						if (unref(registerForm).errors.first_name) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(unref(registerForm).errors.first_name)}</span>`);
						else _push(`<!---->`);
						_push(`</div><div class="imas-auth-form-field" data-v-7a968354><label for="imas-auth-reg-last-name" data-v-7a968354>${ssrInterpolate(trans("contact_us.last_name"))} *</label><input id="imas-auth-reg-last-name"${ssrRenderAttr("value", unref(registerForm).last_name)} type="text" autocomplete="family-name" required maxlength="120" data-v-7a968354>`);
						if (unref(registerForm).errors.last_name) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(unref(registerForm).errors.last_name)}</span>`);
						else _push(`<!---->`);
						_push(`</div></div><div class="imas-auth-form-field" data-v-7a968354><label for="imas-auth-reg-email" data-v-7a968354>${ssrInterpolate(trans("Email"))} *</label><input id="imas-auth-reg-email"${ssrRenderAttr("value", unref(registerForm).email)} type="email" autocomplete="email" required data-v-7a968354>`);
						if (unref(registerForm).errors.email) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(unref(registerForm).errors.email)}</span>`);
						else _push(`<!---->`);
						_push(`</div><div class="imas-auth-form-field" data-v-7a968354><label for="imas-auth-reg-mobile" data-v-7a968354>${ssrInterpolate(trans("Mobile"))} *</label><div class="${ssrRenderClass([{ "imas-auth-phone-field--country-open": registerCountryDropdownOpen.value }, "imas-auth-phone-field"])}" dir="ltr" data-v-7a968354><div class="imas-auth-country-select-shell" data-v-7a968354><button id="imas-auth-reg-country-code" type="button" class="imas-auth-country-trigger"${ssrRenderAttr("aria-expanded", registerCountryDropdownOpen.value)} aria-haspopup="listbox"${ssrRenderAttr("aria-label", registerCountrySelectAriaLabel.value)} data-v-7a968354>`);
						if (selectedRegisterCountry.value?.flag) _push(`<img class="imas-auth-country-flag-img"${ssrRenderAttr("src", selectedRegisterCountry.value.flag)} alt="" width="22" height="16" decoding="async" loading="lazy" data-v-7a968354>`);
						else _push(`<!---->`);
						_push(`<span class="imas-auth-country-code-label" aria-hidden="true" data-v-7a968354>+${ssrInterpolate(displayCallingCode(selectedRegisterCountry.value?.phone_code))}</span></button><div class="imas-auth-country-dropdown-panel" style="${ssrRenderStyle(registerCountryDropdownOpen.value ? null : { display: "none" })}" data-v-7a968354><div class="imas-auth-country-dropdown-search-wrap text-start" data-v-7a968354><input${ssrRenderAttr("value", registerCountrySearchQuery.value)} type="search" enterkeyhint="search" autocomplete="off" autocorrect="off" spellcheck="false" class="imas-auth-country-dropdown-search"${ssrRenderAttr("placeholder", trans("Search"))}${ssrRenderAttr("aria-label", trans("Search"))} data-v-7a968354></div><ul class="imas-auth-country-dropdown-scroll" role="listbox" tabindex="-1" data-v-7a968354>`);
						if (countriesWithPhoneFiltered.value.length === 0) _push(`<li class="imas-auth-country-option imas-auth-country-option--empty" aria-live="polite" data-v-7a968354>${ssrInterpolate(trans("auth_modal.country_code_search_empty"))}</li>`);
						else _push(`<!---->`);
						_push(`<!--[-->`);
						ssrRenderList(countriesWithPhoneFiltered.value, (c) => {
							_push(`<li role="option" class="${ssrRenderClass([{ "imas-auth-country-option--selected": c.id === registerCountryId.value }, "imas-auth-country-option"])}"${ssrRenderAttr("aria-selected", c.id === registerCountryId.value)} data-v-7a968354>`);
							if (c.flag) _push(`<img class="imas-auth-country-flag-img imas-auth-country-flag-img--option"${ssrRenderAttr("src", c.flag)} alt="" width="22" height="16" decoding="async" loading="lazy" data-v-7a968354>`);
							else _push(`<!---->`);
							_push(`<span class="imas-auth-country-option-code" data-v-7a968354>+${ssrInterpolate(displayCallingCode(c.phone_code))}</span></li>`);
						});
						_push(`<!--]--></ul></div></div><span class="imas-auth-phone-sep" aria-hidden="true" data-v-7a968354></span><input id="imas-auth-reg-mobile"${ssrRenderAttr("value", registerMobileLocal.value)} type="tel" inputmode="numeric" autocomplete="tel-national" class="imas-auth-phone-input" required${ssrRenderAttr("placeholder", trans("auth_modal.mobile_national_placeholder"))} data-v-7a968354></div>`);
						if (registerMobileClientError.value) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(registerMobileClientError.value)}</span>`);
						else _push(`<!---->`);
						if (unref(registerForm).errors.mobile) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(unref(registerForm).errors.mobile)}</span>`);
						else _push(`<!---->`);
						_push(`</div><div class="imas-auth-form-field" data-v-7a968354><label for="imas-auth-reg-password" data-v-7a968354>${ssrInterpolate(trans("Password"))} *</label><div class="imas-auth-password-field" data-v-7a968354><input id="imas-auth-reg-password"${ssrRenderDynamicModel(passwordVisible.value.register ? "text" : "password", unref(registerForm).password, null)}${ssrRenderAttr("type", passwordVisible.value.register ? "text" : "password")} autocomplete="new-password" required data-v-7a968354><button type="button" class="imas-auth-password-toggle"${ssrRenderAttr("aria-label", passwordToggleAria("register"))}${ssrRenderAttr("aria-pressed", passwordVisible.value.register)} data-v-7a968354><i class="${ssrRenderClass(passwordVisible.value.register ? "fa fa-eye-slash" : "fa fa-eye")}" aria-hidden="true" data-v-7a968354></i></button></div>`);
						if (unref(registerForm).errors.password) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(unref(registerForm).errors.password)}</span>`);
						else _push(`<!---->`);
						_push(`</div><div class="imas-auth-form-field" data-v-7a968354><label for="imas-auth-reg-password-confirmation" data-v-7a968354>${ssrInterpolate(trans("Confirm Password"))} *</label><div class="imas-auth-password-field" data-v-7a968354><input id="imas-auth-reg-password-confirmation"${ssrRenderDynamicModel(passwordVisible.value.registerConfirm ? "text" : "password", unref(registerForm).password_confirmation, null)}${ssrRenderAttr("type", passwordVisible.value.registerConfirm ? "text" : "password")} autocomplete="new-password" required data-v-7a968354><button type="button" class="imas-auth-password-toggle"${ssrRenderAttr("aria-label", passwordToggleAria("registerConfirm"))}${ssrRenderAttr("aria-pressed", passwordVisible.value.registerConfirm)} data-v-7a968354><i class="${ssrRenderClass(passwordVisible.value.registerConfirm ? "fa fa-eye-slash" : "fa fa-eye")}" aria-hidden="true" data-v-7a968354></i></button></div>`);
						if (unref(registerForm).errors.password_confirmation) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(unref(registerForm).errors.password_confirmation)}</span>`);
						else _push(`<!---->`);
						_push(`</div><div class="imas-auth-terms-wrap" data-v-7a968354><div class="filter-tags imas-auth-terms" data-v-7a968354><input id="imas-auth-terms"${ssrIncludeBooleanAttr(Array.isArray(registerTermsAccepted.value) ? ssrLooseContain(registerTermsAccepted.value, null) : registerTermsAccepted.value) ? " checked" : ""} type="checkbox" class="mx-2 remember-me-checkbox" data-v-7a968354><label for="imas-auth-terms" class="imas-auth-terms__label" data-v-7a968354>${ssrInterpolate(trans("auth_modal.agree_terms_prefix"))} <a href="#" class="imas-auth-terms__link" data-v-7a968354>${ssrInterpolate(trans("auth_modal.terms_and_conditions"))}</a> ${ssrInterpolate(trans("auth_modal.agree_terms_suffix"))}</label></div>`);
						if (registerTermsClientError.value) _push(`<p class="imas-auth-terms__error" role="alert" data-v-7a968354>${ssrInterpolate(registerTermsClientError.value)}</p>`);
						else _push(`<!---->`);
						_push(`</div><div class="imas-auth-form-field imas-auth-form-field--actions" data-v-7a968354><button type="submit" class="log-submit-btn"${ssrIncludeBooleanAttr(unref(registerForm).processing) ? " disabled" : ""} data-v-7a968354><span data-v-7a968354>${ssrInterpolate(trans("Register"))}</span></button></div></form></div></div></div><div class="tab" data-v-7a968354><div id="tab-imas-reset" class="${ssrRenderClass([{ "imas-auth-tab--active": activeMainTab.value === "reset" }, "tab-contents"])}" data-v-7a968354><div class="custom-form" data-v-7a968354>`);
						if (!resetToken.value) _push(`<p class="imas-auth-modal__hint" data-v-7a968354>${ssrInterpolate(trans("auth_modal.reset_hint"))}</p>`);
						else _push(`<!---->`);
						_push(`<form data-v-7a968354><label for="imas-auth-reset-email" data-v-7a968354>${ssrInterpolate(trans("Email"))} *</label><input id="imas-auth-reset-email"${ssrRenderAttr("value", unref(resetForm).email)} type="email" autocomplete="email" required data-v-7a968354>`);
						if (unref(resetForm).errors.email) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(unref(resetForm).errors.email)}</span>`);
						else _push(`<!---->`);
						_push(`<label for="imas-auth-reset-password" data-v-7a968354>${ssrInterpolate(trans("Password"))} *</label><input id="imas-auth-reset-password"${ssrRenderAttr("value", unref(resetForm).password)} type="password" autocomplete="new-password" required data-v-7a968354>`);
						if (unref(resetForm).errors.password) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(unref(resetForm).errors.password)}</span>`);
						else _push(`<!---->`);
						_push(`<label for="imas-auth-reset-password-confirmation" data-v-7a968354>${ssrInterpolate(trans("Confirm Password"))} *</label><input id="imas-auth-reset-password-confirmation"${ssrRenderAttr("value", unref(resetForm).password_confirmation)} type="password" autocomplete="new-password" required data-v-7a968354>`);
						if (unref(resetForm).errors.password_confirmation) _push(`<span class="imas-auth-field-error" data-v-7a968354>${ssrInterpolate(unref(resetForm).errors.password_confirmation)}</span>`);
						else _push(`<!---->`);
						_push(`<button type="submit" class="log-submit-btn"${ssrIncludeBooleanAttr(unref(resetForm).processing || !resetToken.value) ? " disabled" : ""} data-v-7a968354><span data-v-7a968354>${ssrInterpolate(trans("Reset Password"))}</span></button></form></div></div></div></div></div>`);
					}
					_push(`</div></div></div>`);
				} else _push(`<!---->`);
			}, "body", false, _parent);
			else _push(`<!---->`);
		};
	}
};
var _sfc_setup$7 = _sfc_main$7.setup;
_sfc_main$7.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/Findhouses/AuthModal.vue");
	return _sfc_setup$7 ? _sfc_setup$7(props, ctx) : void 0;
};
var AuthModal_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$7, [["__scopeId", "data-v-7a968354"]]);
//#endregion
//#region resources/js/utils/localizedRoute.js
var SUPPORTED_LOCALES = [
	"en",
	"tr",
	"ar"
];
/**
* Prefix or swap the locale segment on a front-office path/URL.
*/
function applyLocalePrefix(url, locale) {
	const loc = SUPPORTED_LOCALES.includes(locale) ? locale : "en";
	if (!url || url === "#") return url;
	try {
		const origin = typeof window !== "undefined" ? window.location.origin : "http://localhost";
		const parsed = new URL(url, origin);
		let segments = parsed.pathname.split("/").filter(Boolean);
		if (segments.length > 0 && SUPPORTED_LOCALES.includes(segments[0])) segments.shift();
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
	if (normalized === "/") return `/${loc}`;
	return `/${loc}${normalized}`;
}
/**
* Named Laravel route for the active locale (uses Ziggy when available).
*/
function localizedRoute(name, params, locale, fallback = "#") {
	const loc = SUPPORTED_LOCALES.includes(locale) ? locale : "en";
	let url = fallback;
	try {
		if (typeof route === "function" && route().has?.(name)) url = route(name, params);
	} catch {}
	if (!url || url === "#") return localizedFallbackPath(fallback, loc);
	return applyLocalePrefix(url, loc);
}
//#endregion
//#region resources/js/Layouts/Findhouses/NavbarSearchModal.vue
var _sfc_main$6 = {
	__name: "NavbarSearchModal",
	__ssrInlineRender: true,
	props: { open: {
		type: Boolean,
		default: false
	} },
	emits: ["update:open"],
	setup(__props, { emit: __emit }) {
		const props = __props;
		const emit = __emit;
		const page = usePage();
		const inputRef = ref(null);
		const searchQuery = ref("");
		const activeLocale = computed(() => page.props.locale || "en");
		const isRtl = computed(() => page.props.text_direction === "rtl" || page.props.locale === "ar");
		const mediaData = computed(() => page.props.globals?.media || {});
		const logoUrl = computed(() => {
			const m = mediaData.value;
			return m.transparent_logo || m.white_logo || "";
		});
		function trans(key) {
			return page.props.translations?.[key] ?? key;
		}
		function close() {
			emit("update:open", false);
		}
		function readQueryFromUrl() {
			if (typeof window === "undefined") return "";
			try {
				return (new URLSearchParams(window.location.search).get("q") || "").trim();
			} catch {
				return "";
			}
		}
		function isPropertyIndexPath() {
			if (typeof window === "undefined") return false;
			try {
				const indexPath = new URL(localizedRoute("property.index", {}, activeLocale.value, "/property"), window.location.origin).pathname.replace(/\/+$/, "") || "/";
				return (window.location.pathname.replace(/\/+$/, "") || "/") === indexPath;
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
		watch(() => props.open, async (isOpen) => {
			setBodyScrollLock(!!isOpen);
			if (!isOpen) return;
			searchQuery.value = isPropertyIndexPath() ? readQueryFromUrl() : "";
			await nextTick();
			inputRef.value?.focus();
			inputRef.value?.select?.();
		});
		/**
		* Gate `<Teleport to="body">` until after mount. Inertia's Vue SSR does not emit
		* teleport-to-body content into the server HTML, so an active teleport during
		* hydration is matched against `<div id="app">` and warns. Rendering a plain
		* comment placeholder until mounted keeps server/client identical.
		*/
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
			if (mounted.value) ssrRenderTeleport(_push, (_push) => {
				if (__props.open) {
					_push(`<div class="imas-navbar-search" role="dialog" aria-modal="true"${ssrRenderAttr("aria-label", trans("Search"))} data-v-bfb447fe><div class="${ssrRenderClass([{ "imas-navbar-search__bar--rtl": isRtl.value }, "imas-navbar-search__bar"])}" data-v-bfb447fe><div class="imas-navbar-search__brand" data-v-bfb447fe>`);
					if (logoUrl.value) _push(`<img${ssrRenderAttr("src", logoUrl.value)} alt="" class="imas-navbar-search__logo" data-v-bfb447fe>`);
					else _push(`<!---->`);
					_push(`</div><button type="button" class="imas-navbar-search__back"${ssrRenderAttr("aria-label", trans("auth_modal.close"))} data-v-bfb447fe><i class="${ssrRenderClass([isRtl.value ? "fa-arrow-left" : "fa-arrow-left", "fa"])}" aria-hidden="true" data-v-bfb447fe></i></button><form class="imas-navbar-search__form" data-v-bfb447fe><label class="sr-only" for="imas-navbar-search-input" data-v-bfb447fe>${ssrInterpolate(trans("Search"))}</label><div class="imas-navbar-search__input-wrap" data-v-bfb447fe><input id="imas-navbar-search-input"${ssrRenderAttr("value", searchQuery.value)} type="search" class="imas-navbar-search__input"${ssrRenderAttr("placeholder", trans("Search"))} autocomplete="off" maxlength="255" enterkeyhint="search" data-v-bfb447fe>`);
					if (searchQuery.value) _push(`<button type="button" class="imas-navbar-search__clear"${ssrRenderAttr("aria-label", trans("auth_modal.close"))} data-v-bfb447fe><i class="fa fa-times" aria-hidden="true" data-v-bfb447fe></i></button>`);
					else _push(`<!---->`);
					_push(`</div><button type="submit" class="imas-navbar-search__submit"${ssrRenderAttr("aria-label", trans("Search"))} data-v-bfb447fe><i class="fa fa-search" aria-hidden="true" data-v-bfb447fe></i></button></form></div><button type="button" class="imas-navbar-search__backdrop"${ssrRenderAttr("aria-label", trans("auth_modal.close"))} tabindex="-1" data-v-bfb447fe></button></div>`);
				} else _push(`<!---->`);
			}, "body", false, _parent);
			else _push(`<!---->`);
		};
	}
};
var _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/Findhouses/NavbarSearchModal.vue");
	return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
var NavbarSearchModal_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$6, [["__scopeId", "data-v-bfb447fe"]]);
//#endregion
//#region resources/js/Layouts/Findhouses/UserNavbar.vue
var websiteSlogan$1 = "MOST ACCURATE SOLUTIONS";
var _sfc_main$5 = {
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
		const homeHref = computed(() => localizedRoute("home", {}, activeLocale.value, "/"));
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
		function normalizeAuthTab(tab) {
			if (tab === "register" || tab === "reset" || tab === "forgot") return tab;
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
			if (!el) return;
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
			if (page.props.flash?.status && !authModalOpen.value) openAuthModal("login");
		}
		watch(() => page.props.flash?.status, (status, prev) => {
			if (!status || status === prev || authModalOpen.value) return;
			const path = window.location.pathname || "";
			if (/\/reset-password\//.test(path) || /\/forgot-password\/?$/.test(path)) return;
			openAuthModal("login");
		});
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
			const name = page.props.globals?.seo?.website_name || page.props.appName || "";
			return String(name).toUpperCase();
		});
		computed(() => page.props.theme_url || "");
		const auth = computed(() => page.props.auth);
		const isRtl = computed(() => page.props.text_direction === "rtl" || page.props.locale === "ar");
		const accountGreeting = computed(() => {
			const hello = trans("Hi");
			const name = String(auth.value?.nav_display_name ?? "").trim();
			return name ? `${hello} ${name}` : hello;
		});
		const isAdmin = computed(() => auth.value?.type === "admin");
		const profileHref = computed(() => {
			if (isAdmin.value) return route("admin.profile.edit");
			return homeHref.value;
		});
		const favoritesHref = computed(() => localizedRoute("property.favorites", {}, activeLocale.value, "/favorite-properties"));
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
			if (typeof url !== "string" || url.trim() === "") return "";
			try {
				const base = typeof window !== "undefined" ? window.location.origin : "http://localhost";
				return new URL(url, base).pathname.replace(/\/+$/, "") || "/";
			} catch {
				return url.split("?")[0].replace(/\/+$/, "") || "/";
			}
		}
		function isNavLinkActive(item) {
			if (!item?.href) return false;
			const current = normalizePath(page.url);
			const target = normalizePath(item.href);
			if (!target || target === "#") return false;
			if (current === target) return true;
			if (item.key === "navBar.Home") {
				try {
					if (typeof route === "function" && route().current?.("home")) return true;
				} catch {}
				try {
					if (typeof route === "function" && route().has?.("home")) return current === normalizePath(route("home"));
				} catch {}
			}
			if (target !== "/" && current.startsWith(`${target}/`)) return true;
			return false;
		}
		const localeSwitcher = computed(() => page.props.locale_switcher || []);
		const currentLocale = computed(() => page.props.locale || "en");
		computed(() => {
			const code = currentLocale.value;
			if (code === "en") return "ENG";
			return code.toUpperCase();
		});
		function trans(key) {
			return page.props.translations?.[key] ?? key;
		}
		function isDesktopNavViewport() {
			return window.matchMedia("(min-width: 1025px)").matches;
		}
		function playNavbarEnterAnimation() {
			if (prefersReducedMotion()) return;
			const list = navListRef.value;
			const logo = logoRef.value;
			const header = headerContainerRef.value;
			if (!list || !header) return;
			const navItems = list.querySelectorAll(":scope > li.imas-nav-item");
			const actions = header.querySelectorAll(".imas-header-action");
			const isDesktop = isDesktopNavViewport();
			const isRtl = document.documentElement.getAttribute("dir") === "rtl" || document.documentElement.dir === "rtl";
			context(() => {
				const tl = gsap.timeline({ defaults: { ease: "power2.out" } });
				if (logo) tl.fromTo(logo, {
					opacity: 0,
					x: isRtl ? 16 : -16
				}, {
					opacity: 1,
					x: 0,
					duration: .5
				}, 0);
				if (isDesktop && navItems.length) tl.fromTo(navItems, {
					opacity: 0,
					y: -20
				}, {
					opacity: 1,
					y: 0,
					duration: .45,
					stagger: .06
				}, logo ? .1 : 0);
				if (isDesktop && actions.length) tl.fromTo(actions, {
					opacity: 0,
					x: isRtl ? -16 : 16
				}, {
					opacity: 1,
					x: 0,
					duration: .45,
					stagger: .08
				}, logo ? .14 : .08);
			}, headerContainerRef);
		}
		function playMobileNavEnterAnimation() {
			if (prefersReducedMotion()) return;
			const $ = window.jQuery;
			if (!$) return;
			const items = $(".mmenu-init").find("li.imas-nav-item").toArray();
			if (!items.length) return;
			const isRtl = document.documentElement.getAttribute("dir") === "rtl" || document.documentElement.dir === "rtl";
			gsap.fromTo(items, {
				opacity: 0,
				x: isRtl ? 20 : -20
			}, {
				opacity: 1,
				x: 0,
				duration: .4,
				stagger: .05,
				ease: "power2.out",
				overwrite: "auto"
			});
		}
		/** ISO 3166-1 alpha-2 for flag-icons (`fi-xx`). Not every locale maps 1:1 to a flag — adjust as needed. */
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
			if (langEl && langMenuOpen.value && !langEl.contains(event.target)) langMenuOpen.value = false;
			const userEl = userMenuWrapRef.value;
			if (userEl && userMenuOpen.value && !userEl.contains(event.target)) userMenuOpen.value = false;
		}
		let mmenuApi = null;
		/**
		* Root panel navbar: translated title, theme chrome, close control.
		*/
		function customizeMmenuNavbar($) {
			const pageId = ($(".mm-page").first().length ? $(".mm-page").first() : $("#app")).attr("id");
			if (!pageId) return;
			const closeLabel = document.documentElement.getAttribute("dir") === "rtl" || document.documentElement.dir === "rtl" ? "إغلاق القائمة" : "Close menu";
			$(".mm-menu.mm-offcanvas").each(function() {
				const $navbar = $(this).find("> .mm-panels > .mm-panel").first().children(".mm-navbar").first();
				if (!$navbar.length) return;
				$navbar.find(".mm-title").text(trans("Menu"));
				if (!$navbar.find("a.mm-close").length) $navbar.prepend(`<a class="mm-btn mm-close" href="#${pageId}" aria-label="${closeLabel}"></a>`);
			});
		}
		/** Login/Register rows are mobile-only; drop from drawer when session is active. */
		function stripMmenuAuthLinks($) {
			$(".mmenu-init").find("li.imas-mmenu-only").has(".imas-auth-nav-link").remove();
		}
		function initMobileMenuMmenu() {
			const $ = window.jQuery;
			if (!$ || !$.fn?.mmenu) return;
			if ($(window).width() > 1024) {
				teardownMobileMenuMmenu();
				return;
			}
			$(".mmenu-init").remove();
			const $navigation = $("#navigation").first();
			if (!$navigation.length) return;
			$navigation.clone().addClass("mmenu-init").insertBefore("#navigation").removeAttr("id").removeClass("style-1 style-2 imas-nav__menu").find("ul").removeAttr("id");
			$(".mmenu-init").find(".container").removeClass("container");
			$(".mmenu-init").find("li.imas-mmenu-only").has(".lang-switch-row").remove();
			if (auth.value) stripMmenuAuthLinks($);
			const isRtl = document.documentElement.getAttribute("dir") === "rtl" || document.documentElement.dir === "rtl";
			$(".mmenu-init").mmenu({
				counters: true,
				navbar: { title: trans("Menu") }
			}, { offCanvas: {
				pageSelector: "#app",
				position: isRtl ? "right" : "left"
			} });
			mmenuApi = $(".mmenu-init").data("mmenu") || null;
			if (!mmenuApi) return;
			const $icon = $(".hamburger");
			$(".mmenu-trigger").off("click.imasMmenu").on("click.imasMmenu", () => {
				mmenuApi?.open?.();
			});
			customizeMmenuNavbar($);
			mmenuApi.bind("open:finish", () => {
				setTimeout(() => {
					$icon.addClass("is-active");
					customizeMmenuNavbar($);
					if (auth.value) stripMmenuAuthLinks($);
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
			if (!$) return;
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
			document.querySelectorAll("#navigation.style-2.cloned").forEach((el) => el.remove());
		}
		function updateScrollPinnedHeader() {
			const bar = headerBarRef.value;
			if (!bar) return;
			const h = bar.offsetHeight || 0;
			const threshold = Math.max(h * 2, 1);
			const next = window.scrollY >= threshold;
			if (next === headerPinned.value) {
				if (next) scrollPinSpacerPx.value = h;
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
							if (scrollPinAnimToken !== token || !headerPinned.value) return;
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
			if (scrollPinRaf) return;
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
		watch(() => page.props.locale, () => reinitHeaderChromeForLocale());
		watch(() => props.transparentNavbar, () => reinitHeaderChromeForLocale());
		watch(() => props.navLinks, () => {
			nextTick(() => playNavbarEnterAnimation());
		}, { deep: true });
		watch(() => auth.value, () => {
			nextTick(() => {
				const $ = window.jQuery;
				if ($ && $(window).width() <= 1024) initMobileMenuMmenu();
			});
		});
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
			if ($) $(window).off("resize.imasMmenu").on("resize.imasMmenu", () => {
				initMobileMenuMmenu();
			});
		});
		onBeforeUnmount(() => {
			document.removeEventListener(IMAS_OPEN_AUTH_EVENT, onImasOpenAuthEvent);
			document.removeEventListener("click", closeHeaderDropdownsOnOutsideClick);
			document.removeEventListener("click", onDelegatedOpenAuth, true);
			teardownScrollPinnedHeader();
			teardownMobileMenuMmenu();
			const $ = window.jQuery;
			if ($) $(window).off("resize.imasMmenu");
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<header${ssrRenderAttrs(mergeProps({
				ref_key: "headerContainerRef",
				ref: headerContainerRef,
				id: "header-container",
				class: ["header imas-nav-shell", [__props.transparentNavbar ? "head-tr" : "imas-navbar-solid", { "imas-header-scroll-pinned": headerPinned.value }]]
			}, _attrs))} data-v-0a7a998a><div id="header" class="${ssrRenderClass([{
				"imas-scroll-pinned": headerPinned.value,
				"imas-scroll-pinned--in": headerPinned.value && headerPinnedVisible.value
			}, "imas-nav imas-nav__bar bottom"])}" data-v-0a7a998a><div class="container imas-nav__container" data-v-0a7a998a><div id="logo" class="imas-nav__logo" data-v-0a7a998a>`);
			_push(ssrRenderComponent(unref(Link), {
				href: homeHref.value,
				class: "imas-nav__logo-link"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<img${ssrRenderAttr("src", logoUrl.value)}${ssrRenderAttr("data-sticky-logo", logoUrl.value)} alt="" data-v-0a7a998a${_scopeId}><span class="imas-brand-text" data-v-0a7a998a${_scopeId}><span class="website-name" data-v-0a7a998a${_scopeId}>${ssrInterpolate(websiteName.value)}</span><span class="website-slogan" data-v-0a7a998a${_scopeId}>${ssrInterpolate(websiteSlogan$1)}</span></span>`);
					else return [createVNode("img", {
						src: logoUrl.value,
						"data-sticky-logo": logoUrl.value,
						alt: ""
					}, null, 8, ["src", "data-sticky-logo"]), createVNode("span", { class: "imas-brand-text" }, [createVNode("span", { class: "website-name" }, toDisplayString(websiteName.value), 1), createVNode("span", { class: "website-slogan" }, toDisplayString(websiteSlogan$1))])];
				}),
				_: 1
			}, _parent));
			_push(`</div><nav id="navigation" class="imas-nav__menu style-1" data-v-0a7a998a><ul id="responsive" data-v-0a7a998a><!--[-->`);
			ssrRenderList(__props.navLinks, (item) => {
				_push(`<li class="${ssrRenderClass([{ "has-submenu": item?.children?.length }, "imas-nav-item"])}" data-v-0a7a998a>`);
				if (item.href) _push(ssrRenderComponent(unref(Link), {
					href: item.href,
					class: { active: isNavLinkActive(item) }
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`${ssrInterpolate(item.label ?? trans(item.key))}`);
						else return [createTextVNode(toDisplayString(item.label ?? trans(item.key)), 1)];
					}),
					_: 2
				}, _parent));
				else _push(`<a href="#" class="${ssrRenderClass({ active: isNavLinkActive(item) })}" data-v-0a7a998a>${ssrInterpolate(item.label ?? trans(item.key))}</a>`);
				if (item?.children?.length) {
					_push(`<ul class="imas-nav__submenu" data-v-0a7a998a><!--[-->`);
					ssrRenderList(item.children, (child) => {
						_push(`<li class="imas-nav__submenu-item" data-v-0a7a998a>`);
						_push(ssrRenderComponent(unref(Link), {
							href: child.href,
							class: ["imas-nav__submenu-link", { active: isNavLinkActive(child) }]
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`${ssrInterpolate(child.label ?? trans(child.key))}`);
								else return [createTextVNode(toDisplayString(child.label ?? trans(child.key)), 1)];
							}),
							_: 2
						}, _parent));
						_push(`</li>`);
					});
					_push(`<!--]--></ul>`);
				} else _push(`<!---->`);
				_push(`</li>`);
			});
			_push(`<!--]-->`);
			if (!auth.value && mounted.value) _push(`<li class="imas-mmenu-only" data-v-0a7a998a><a href="#" class="imas-auth-nav-link" data-open-auth="login" data-v-0a7a998a>${ssrInterpolate(trans("Login"))}</a></li>`);
			else _push(`<!---->`);
			if (!auth.value && mounted.value) _push(`<li class="imas-mmenu-only" data-v-0a7a998a><a href="#" class="imas-auth-nav-link" data-open-auth="register" data-v-0a7a998a>${ssrInterpolate(trans("Register"))}</a></li>`);
			else _push(`<!---->`);
			_push(`</ul></nav><div class="imas-nav__end" data-v-0a7a998a><div class="${ssrRenderClass([{ "imas-nav__actions--rtl": isRtl.value }, "imas-nav__actions right"])}" data-v-0a7a998a><div class="header-user-menu user-menu add imas-nav__lang imas-header-action" data-v-0a7a998a><div class="${ssrRenderClass([{ "lang-wrap--open": langMenuOpen.value }, "lang-wrap"])}" data-v-0a7a998a><div class="show-lang imas-nav__lang-trigger" role="button" tabindex="0"${ssrRenderAttr("aria-expanded", langMenuOpen.value)} aria-haspopup="listbox"${ssrRenderAttr("aria-label", trans("Language"))} data-v-0a7a998a><span class="show-lang-trigger-inner" data-v-0a7a998a>`);
			if (flagCountryClass(currentLocale.value)) _push(`<span class="${ssrRenderClass([flagCountryClass(currentLocale.value), "fi lang-switch-flag lang-switch-flag--trigger"])}" aria-hidden="true" data-v-0a7a998a></span>`);
			else _push(`<!---->`);
			_push(`</span><i class="fa fa-caret-down arrlan" data-v-0a7a998a></i></div><ul class="lang-tooltip lang-action no-list-style" role="listbox" data-v-0a7a998a><!--[-->`);
			ssrRenderList(localeSwitcher.value, (loc) => {
				_push(`<li data-v-0a7a998a><a href="#" class="${ssrRenderClass([{ "current-lan": loc.code === currentLocale.value }, "lang-switch-row"])}" role="option"${ssrRenderAttr("aria-selected", loc.code === currentLocale.value)} data-v-0a7a998a>`);
				if (flagCountryClass(loc.code)) _push(`<span class="${ssrRenderClass([flagCountryClass(loc.code), "fi lang-switch-flag"])}" aria-hidden="true" data-v-0a7a998a></span>`);
				else _push(`<!---->`);
				_push(`<span class="mx-2" data-v-0a7a998a>${ssrInterpolate(loc.native)}</span></a></li>`);
			});
			_push(`<!--]--></ul></div></div><button type="button" class="imas-nav__search imas-header-action"${ssrRenderAttr("aria-label", trans("Search"))}${ssrRenderAttr("title", trans("Search"))} data-v-0a7a998a><i class="fa fa-search" aria-hidden="true" data-v-0a7a998a></i></button>`);
			if (auth.value && mounted.value) _push(ssrRenderComponent(unref(Link), {
				href: favoritesHref.value,
				class: ["imas-nav__favorites imas-header-action", { "is-active": favoritesNavActive.value }],
				"aria-label": trans("properties.favorite_properties"),
				title: trans("properties.favorite_properties")
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<i class="fa fa-heart" aria-hidden="true" data-v-0a7a998a${_scopeId}></i>`);
					else return [createVNode("i", {
						class: "fa fa-heart",
						"aria-hidden": "true"
					})];
				}),
				_: 1
			}, _parent));
			else _push(`<!---->`);
			if (mounted.value) {
				_push(`<div class="${ssrRenderClass([{ active: userMenuOpen.value }, "header-user-menu user-menu add UserMenu imas-header-action"])}" data-v-0a7a998a>`);
				if (auth.value) {
					_push(`<!--[--><div class="${ssrRenderClass([{ "imas-nav__account-trigger--rtl": isRtl.value }, "header-user-name imas-nav__account-trigger"])}" role="button" tabindex="0"${ssrRenderAttr("aria-expanded", userMenuOpen.value)} aria-haspopup="true"${ssrRenderAttr("aria-label", trans("Account menu"))} data-v-0a7a998a><span class="imas-nav__avatar" data-v-0a7a998a><img${ssrRenderAttr("src", auth.value.avatar)} alt="" data-v-0a7a998a></span><span class="imas-nav__account-text imas-nav__desktop-only" data-v-0a7a998a>${ssrInterpolate(accountGreeting.value)}</span><i class="fa fa-caret-down imas-nav__account-caret imas-nav__desktop-only" aria-hidden="true" data-v-0a7a998a></i></div><ul class="imas-user-menu-dropdown text-start" data-v-0a7a998a>`);
					if (isAdmin.value) {
						_push(`<li data-v-0a7a998a>`);
						_push(ssrRenderComponent(unref(Link), {
							class: "imas-user-menu-dropdown__item",
							href: _ctx.route("admin.dashboard.index"),
							onClick: ($event) => userMenuOpen.value = false
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`${ssrInterpolate(trans("Dashboard"))}`);
								else return [createTextVNode(toDisplayString(trans("Dashboard")), 1)];
							}),
							_: 1
						}, _parent));
						_push(`</li>`);
					} else _push(`<!---->`);
					if (isAdmin.value) {
						_push(`<li data-v-0a7a998a>`);
						_push(ssrRenderComponent(unref(Link), {
							class: "imas-user-menu-dropdown__item",
							href: profileHref.value,
							onClick: ($event) => userMenuOpen.value = false
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`${ssrInterpolate(trans("global.profile"))}`);
								else return [createTextVNode(toDisplayString(trans("global.profile")), 1)];
							}),
							_: 1
						}, _parent));
						_push(`</li>`);
					} else _push(`<!---->`);
					_push(`<li data-v-0a7a998a><button type="button" class="imas-user-menu-dropdown__item dropdown-logout" data-v-0a7a998a>${ssrInterpolate(trans("global.LogOut"))}</button></li></ul><!--]-->`);
				} else _push(`<div class="imas-nav__sign-in imas-header-action" data-v-0a7a998a><a href="#" class="imas-nav__sign-in-link show-reg-form modal-open" data-open-auth="login" data-v-0a7a998a>${ssrInterpolate(trans("Sign In"))}</a></div>`);
				_push(`</div>`);
			} else _push(`<!---->`);
			_push(`</div><div class="mmenu-trigger imas-nav__mmenu" data-v-0a7a998a><button class="hamburger hamburger--collapse" type="button"${ssrRenderAttr("aria-label", trans("Menu"))} data-v-0a7a998a><span class="hamburger-box" data-v-0a7a998a><span class="hamburger-inner" data-v-0a7a998a></span></span></button></div></div></div></div><div class="imas-header-scroll-spacer" style="${ssrRenderStyle([{ height: `${scrollPinSpacerPx.value}px` }, headerPinned.value ? null : { display: "none" }])}" aria-hidden="true" data-v-0a7a998a></div>`);
			_push(ssrRenderComponent(AuthModal_default, {
				open: authModalOpen.value,
				"onUpdate:open": ($event) => authModalOpen.value = $event,
				"start-tab": authStartTab.value
			}, null, _parent));
			_push(ssrRenderComponent(NavbarSearchModal_default, {
				open: searchModalOpen.value,
				"onUpdate:open": ($event) => searchModalOpen.value = $event
			}, null, _parent));
			_push(`</header>`);
		};
	}
};
var _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/Findhouses/UserNavbar.vue");
	return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
var UserNavbar_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$5, [["__scopeId", "data-v-0a7a998a"]]);
//#endregion
//#region resources/js/utils/cmsPageUrl.js
function resolveLocale(locale) {
	if (locale && SUPPORTED_LOCALES.includes(locale)) return locale;
	if (typeof document !== "undefined") {
		const lang = String(document.documentElement.getAttribute("lang") || "").trim();
		if (SUPPORTED_LOCALES.includes(lang)) return lang;
	}
	return "en";
}
/**
* Front-office URL for a CMS page slug (localized for the active locale).
*/
function cmsPageUrl(slug, locale) {
	const s = String(slug || "").trim();
	const loc = resolveLocale(locale);
	if (!s) return "#";
	try {
		if (typeof route === "function" && route().has?.("page.show")) return applyLocalePrefix(route("page.show", { slug: s }), loc);
	} catch {}
	return localizedFallbackPath(`/${s}`, loc);
}
//#endregion
//#region resources/js/utils/turkishPhone.js
/** Turkish mobile display: +90 536 910 46 89 (3-3-2-2 after country code). */
var TR_COUNTRY_CODE = "90";
var TR_NATIONAL_LENGTH = 10;
/**
* Normalize to E.164 digits without "+" (e.g. "905369104689").
*
* Accepts +90…, 90…, 0…, or 10-digit national numbers.
*
* @param {string} phone
* @returns {string}
*/
function normalizeTurkishPhoneDigits(phone) {
	const digits = String(phone ?? "").replace(/\D/g, "");
	if (!digits) return "";
	let national = digits;
	if (national.startsWith(TR_COUNTRY_CODE)) national = national.slice(2);
	if (national.startsWith("0")) national = national.slice(1);
	if (national.length !== TR_NATIONAL_LENGTH) return "";
	return `${TR_COUNTRY_CODE}${national}`;
}
/**
* Format a Turkish phone for display: "+90 536 910 46 89".
*
* Non-Turkish or invalid input is returned trimmed unchanged.
*
* @param {string} phone
* @returns {string}
*/
function formatTurkishPhone(phone) {
	const raw = String(phone ?? "").trim();
	if (!raw) return "";
	const e164 = normalizeTurkishPhoneDigits(raw);
	if (!e164) return raw;
	const national = e164.slice(2);
	return `+${TR_COUNTRY_CODE} ${national.slice(0, 3)} ${national.slice(3, 6)} ${national.slice(6, 8)} ${national.slice(8, 10)}`;
}
//#endregion
//#region resources/js/utils/whatsappUrl.js
/**
* Build a WhatsApp chat URL from admin settings (digits or full URL).
*
* @param {string} phoneOrUrl Raw phone, wa.me link, or api.whatsapp.com URL
* @param {string} [text] Optional pre-filled message
* @returns {string}
*/
function buildWhatsAppContactUrl(phoneOrUrl, text = "") {
	const raw = String(phoneOrUrl ?? "").trim();
	if (!raw) return "";
	if (/^https?:\/\//i.test(raw)) {
		if (/api\.whatsapp\.com/i.test(raw)) return raw;
		const waMeFromUrl = raw.match(/wa\.me\/(\d+)/i);
		if (waMeFromUrl) return buildWhatsAppContactUrl(waMeFromUrl[1], text);
		return raw;
	}
	const digits = raw.replace(/\D/g, "");
	if (!digits) return "";
	const params = new URLSearchParams({ phone: digits });
	if (text) params.set("text", text);
	return `https://api.whatsapp.com/send/?${params.toString()}`;
}
/**
* Prefer dedicated WhatsApp number/URL, then site contact phone.
*
* @param {{ whatsapp?: string, phone?: string }} sources
* @returns {string}
*/
function resolveWhatsAppContactHref({ whatsapp = "", phone = "" } = {}) {
	const dedicated = String(whatsapp).trim();
	if (dedicated) return buildWhatsAppContactUrl(dedicated);
	const contactPhone = String(phone).trim();
	if (contactPhone) return buildWhatsAppContactUrl(contactPhone);
	return "";
}
//#endregion
//#region resources/js/Layouts/Findhouses/UserTopBar.vue
var fallbackPhone$1 = "+456 875 369 208";
var fallbackEmail$1 = "support@example.com";
var _sfc_main$4 = {
	__name: "UserTopBar",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		const activeLocale = computed(() => page.props.locale || "en");
		const settings = computed(() => page.props.settings || {});
		const globals = computed(() => page.props.globals ?? {});
		const topBarPages = computed(() => page.props.globals?.pages?.top_bar ?? []);
		const rawPhone = computed(() => String(settings.value.contact_phone || settings.value.phone || "").trim());
		const phoneDisplay = computed(() => {
			const raw = rawPhone.value;
			if (raw) return formatTurkishPhone(raw);
			return formatTurkishPhone(fallbackPhone$1) || fallbackPhone$1;
		});
		const emailDisplay = computed(() => String(settings.value.contact_email || settings.value.email || "").trim() || fallbackEmail$1);
		const hasContactInfo = computed(() => Boolean(phoneDisplay.value || emailDisplay.value));
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
			return `mailto:${String(settings.value.contact_email || settings.value.email || "").trim() || fallbackEmail$1}`;
		});
		/** Same network list as `UserFooter.vue` `footerSocialLinks`. */
		const topSocialLinks = computed(() => {
			const s = settings.value;
			return [
				{
					key: "facebook",
					label: "Facebook",
					icon: "fa fa-facebook"
				},
				{
					key: "twitter",
					label: "Twitter",
					icon: "fa fa-twitter"
				},
				{
					key: "instagram",
					label: "Instagram",
					icon: "fab fa-instagram"
				},
				{
					key: "youtube",
					label: "YouTube",
					icon: "fa fa-youtube"
				},
				{
					key: "tiktok",
					label: "TikTok",
					icon: "fab fa-tiktok"
				}
			].map((d) => {
				const raw = String(s[d.key] ?? "").trim();
				if (!raw) return null;
				return {
					...d,
					href: raw
				};
			}).filter(Boolean);
		});
		function trans(key) {
			return page.props.translations?.[key] ?? key;
		}
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({
				class: "imas-top-bar topbar",
				role: "region",
				"aria-label": trans("Contacts")
			}, _attrs))} data-v-6d1345a8><div class="container imas-nav__container imas-top-bar__inner" data-v-6d1345a8><div class="imas-top-bar__contacts contact" data-v-6d1345a8>`);
			if (phoneDisplay.value && phoneHref.value) _push(`<a class="imas-top-bar__link"${ssrRenderAttr("href", phoneHref.value)} target="_blank" rel="noopener noreferrer" data-v-6d1345a8><i class="fa fa-phone" aria-hidden="true" data-v-6d1345a8></i><span class="imas-top-bar__phone" dir="ltr" data-v-6d1345a8>${ssrInterpolate(phoneDisplay.value)}</span></a>`);
			else _push(`<!---->`);
			if (emailDisplay.value) _push(`<a class="imas-top-bar__link"${ssrRenderAttr("href", emailHref.value)} data-v-6d1345a8><i class="fa fa-envelope" aria-hidden="true" data-v-6d1345a8></i><span data-v-6d1345a8>${ssrInterpolate(emailDisplay.value)}</span></a>`);
			else _push(`<!---->`);
			if (topBarPages.value.length && hasContactInfo.value) _push(`<span class="imas-top-bar__separator" aria-hidden="true" data-v-6d1345a8>|</span>`);
			else _push(`<!---->`);
			_push(`<!--[-->`);
			ssrRenderList(topBarPages.value, (p) => {
				_push(ssrRenderComponent(unref(Link), {
					key: p.id,
					class: "imas-top-bar__link imas-top-bar__page-link",
					href: unref(cmsPageUrl)(p.slug, activeLocale.value)
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`${ssrInterpolate(p.title)}`);
						else return [createTextVNode(toDisplayString(p.title), 1)];
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
			} else _push(`<!---->`);
			_push(`</div></div>`);
		};
	}
};
var _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/Findhouses/UserTopBar.vue");
	return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
var UserTopBar_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$4, [["__scopeId", "data-v-6d1345a8"]]);
//#endregion
//#region resources/js/Layouts/Findhouses/UserFooter.vue
var websiteSlogan = "MOST ACCURATE SOLUTIONS";
var fallbackAddress = "95 South Park Avenue, USA";
var fallbackPhone = "+456 875 369 208";
var fallbackEmail = "support@example.com";
var _sfc_main$3 = {
	__name: "UserFooter",
	__ssrInlineRender: true,
	props: { navLinks: {
		type: Array,
		default: () => []
	} },
	setup(__props) {
		const props = __props;
		const page = usePage();
		ref(null);
		const showSubscriptionSuccess = ref(false);
		let subscriptionSuccessTimer = null;
		const subscribeForm = useForm({ email: "" });
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
		const websiteName = computed(() => page.props.globals?.seo?.website_name?.toUpperCase() || "");
		const year = computed(() => (/* @__PURE__ */ new Date()).getFullYear());
		computed(() => settings.value.tagline || page.props.appName);
		const rawPhone = computed(() => String(settings.value.contact_phone || settings.value.phone || "").trim());
		const phoneDisplay = computed(() => {
			const raw = rawPhone.value;
			if (raw) return formatTurkishPhone(raw);
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
		const mainNavLinks = computed(() => (props.navLinks || []).filter((l) => l?.href));
		computed(() => {
			return (props.navLinks || []).find((l) => l?.children?.length)?.children || [];
		});
		const activeLocale = computed(() => page.props.locale || "en");
		const footerPagesLinks = computed(() => (page.props.globals?.pages?.footer ?? []).map((p) => ({
			key: `footer-page-${p.id}`,
			label: p.title,
			href: cmsPageUrl(p.slug, activeLocale.value)
		})));
		const bottomBarPages = computed(() => page.props.globals?.pages?.bottom_bar ?? []);
		const footerSocialLinks = computed(() => {
			const s = settings.value;
			return [
				{
					key: "facebook",
					label: "Facebook",
					icon: "fa fa-facebook"
				},
				{
					key: "twitter",
					label: "Twitter",
					icon: "fa fa-twitter"
				},
				{
					key: "instagram",
					label: "Instagram",
					icon: "fab fa-instagram"
				},
				{
					key: "youtube",
					label: "YouTube",
					icon: "fa fa-youtube"
				},
				{
					key: "tiktok",
					label: "TikTok",
					icon: "fab fa-tiktok"
				}
			].map((d) => {
				const raw = String(s[d.key] ?? "").trim();
				if (!raw) return null;
				return {
					...d,
					href: raw
				};
			}).filter(Boolean);
		});
		function trans(key) {
			return page.props.translations?.[key] ?? key;
		}
		function clearSubscriptionSuccessTimer() {
			if (subscriptionSuccessTimer !== null) {
				clearTimeout(subscriptionSuccessTimer);
				subscriptionSuccessTimer = null;
			}
		}
		onBeforeUnmount(() => {
			clearSubscriptionSuccessTimer();
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[--><footer class="first-footer rec-pro imas-blog-footer" data-v-865e9317><div class="top-footer" data-v-865e9317><div class="container imas-footer-wrap" data-v-865e9317><div class="row imas-footer-grid" data-v-865e9317><div class="col-lg-3 col-md-6 f-col imas-footer-col--brand" data-v-865e9317><div class="netabout" data-v-865e9317><div class="brand-line" data-v-865e9317><div class="logo" data-v-865e9317><img${ssrRenderAttr("src", logoUrl.value)} alt="logo" class="footer_logo" data-v-865e9317></div><div class="imas-brand-text" data-v-865e9317><span class="website-name" data-v-865e9317>${ssrInterpolate(websiteName.value)}</span><span class="website-slogan" data-v-865e9317>${ssrInterpolate(websiteSlogan)}</span></div></div></div><div class="contactus text-start" data-v-865e9317><ul data-v-865e9317><li class="contact-line" data-v-865e9317><div class="info" data-v-865e9317><span class="ic" aria-hidden="true" data-v-865e9317><i class="fa fa-map-marker" data-v-865e9317></i></span><p class="in-p" data-v-865e9317>${ssrInterpolate(settings.value.contact_address || fallbackAddress)}</p></div></li><li class="contact-line" data-v-865e9317><div class="info" data-v-865e9317><span class="ic" aria-hidden="true" data-v-865e9317><i class="fa fa-phone" data-v-865e9317></i></span><p class="in-p in-p--phone" dir="ltr" data-v-865e9317>`);
			if (phoneDisplay.value && phoneHref.value) _push(`<span class="in-p-link-wrap" data-v-865e9317><a${ssrRenderAttr("href", phoneHref.value)} target="_blank" rel="noopener noreferrer" data-v-865e9317>${ssrInterpolate(phoneDisplay.value)}</a></span>`);
			else if (phoneDisplay.value) _push(`<!--[-->${ssrInterpolate(phoneDisplay.value)}<!--]-->`);
			else _push(`<!---->`);
			_push(`</p></div></li><li class="contact-line" data-v-865e9317><div class="info" data-v-865e9317><span class="ic" aria-hidden="true" data-v-865e9317><i class="fa fa-envelope" data-v-865e9317></i></span><p class="in-p ti" data-v-865e9317>${ssrInterpolate(settings.value.contact_email || fallbackEmail)}</p></div></li></ul></div></div><div class="col-lg-3 col-md-6 f-col" data-v-865e9317><div class="navigation text-start" data-v-865e9317><h3 data-v-865e9317>${ssrInterpolate(trans("navBar.navigation"))}</h3><div class="nav-footer text-start" data-v-865e9317><ul class="links" data-v-865e9317><!--[-->`);
			ssrRenderList(mainNavLinks.value, (item) => {
				_push(`<li data-v-865e9317>`);
				_push(ssrRenderComponent(unref(Link), { href: item.href }, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`${ssrInterpolate(trans(item.key))}`);
						else return [createTextVNode(toDisplayString(trans(item.key)), 1)];
					}),
					_: 2
				}, _parent));
				_push(`</li>`);
			});
			_push(`<!--]--></ul></div></div></div><div class="col-lg-3 col-md-6 f-col" data-v-865e9317><div class="navigation text-start" data-v-865e9317><h3 data-v-865e9317>${ssrInterpolate(trans("navBar.useful_links"))}</h3><ul class="links links--single" data-v-865e9317><!--[-->`);
			ssrRenderList(footerPagesLinks.value, (item) => {
				_push(`<li data-v-865e9317>`);
				_push(ssrRenderComponent(unref(Link), { href: item.href }, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`${ssrInterpolate(item.label)}`);
						else return [createTextVNode(toDisplayString(item.label), 1)];
					}),
					_: 2
				}, _parent));
				_push(`</li>`);
			});
			_push(`<!--]--></ul></div></div><div class="col-lg-3 col-md-6 f-col" data-v-865e9317><div class="newsletters text-start" data-v-865e9317><h3 data-v-865e9317>${ssrInterpolate(trans("navBar.newsLetters"))}</h3><p data-v-865e9317>${ssrInterpolate(trans("navBar.signup_for_newsletters"))}</p></div><form class="bloq-email mailchimp form-inline newsletter" data-v-865e9317><div class="email" data-v-865e9317><input id="subscribeEmail"${ssrRenderAttr("value", unref(subscribeForm).email)} type="email" name="email" required maxlength="255"${ssrRenderAttr("placeholder", trans("navBar.enter_your_email"))}${ssrIncludeBooleanAttr(unref(subscribeForm).processing) ? " disabled" : ""} class="${ssrRenderClass({ "is-invalid": unref(subscribeForm).errors.email })}" data-v-865e9317><button type="submit"${ssrIncludeBooleanAttr(unref(subscribeForm).processing) ? " disabled" : ""} data-v-865e9317>${ssrInterpolate(trans("navBar.subscribe"))}</button></div>`);
			if (unref(subscribeForm).errors.email) _push(`<p class="subscription-error" role="alert" data-v-865e9317>${ssrInterpolate(unref(subscribeForm).errors.email)}</p>`);
			else _push(`<!---->`);
			if (showSubscriptionSuccess.value) _push(`<p class="subscription-success" role="status" data-v-865e9317>${ssrInterpolate(trans("navBar.subscription_success"))}</p>`);
			else _push(`<!---->`);
			_push(`</form>`);
			if (footerSocialLinks.value.length) {
				_push(`<div class="socials imas-footer-socials"${ssrRenderAttr("aria-label", trans("Social media"))} data-v-865e9317><!--[-->`);
				ssrRenderList(footerSocialLinks.value, (item) => {
					_push(`<a${ssrRenderAttr("href", item.href)} target="_blank" rel="noopener noreferrer"${ssrRenderAttr("aria-label", item.label)} data-v-865e9317><i class="${ssrRenderClass(item.icon)}" aria-hidden="true" data-v-865e9317></i></a>`);
				});
				_push(`<!--]--></div>`);
			} else _push(`<!---->`);
			_push(`</div></div></div></div><div class="second-footer rec-pro copyright" data-v-865e9317><div class="container imas-footer-wrap imas-second-footer__inner" data-v-865e9317>`);
			if (bottomBarPages.value.length) {
				_push(`<nav class="imas-second-footer__bottom-bar"${ssrRenderAttr("aria-label", trans("navBar.useful_links"))} data-v-865e9317><!--[-->`);
				ssrRenderList(bottomBarPages.value, (p, index) => {
					_push(`<!--[-->`);
					if (index > 0) _push(`<span class="imas-second-footer__separator" aria-hidden="true" data-v-865e9317>|</span>`);
					else _push(`<!---->`);
					_push(ssrRenderComponent(unref(Link), {
						class: "imas-second-footer__page-link",
						href: unref(cmsPageUrl)(p.slug, activeLocale.value)
					}, {
						default: withCtx((_, _push, _parent, _scopeId) => {
							if (_push) _push(`${ssrInterpolate(p.title)}`);
							else return [createTextVNode(toDisplayString(p.title), 1)];
						}),
						_: 2
					}, _parent));
					_push(`<!--]-->`);
				});
				_push(`<!--]--></nav>`);
			} else _push(`<div class="imas-second-footer__bottom-bar imas-second-footer__bottom-bar--empty" aria-hidden="true" data-v-865e9317></div>`);
			_push(`<p class="imas-second-footer__copy" data-v-865e9317>${ssrInterpolate(year.value)} © ${ssrInterpolate(appName.value)} — ${ssrInterpolate(trans("navBar.All Rights Reserved"))}</p><a href="https://symfonix.io/en" target="_blank" class="text-decoration-none" data-v-865e9317><p class="imas-second-footer__developer" data-v-865e9317><span data-v-865e9317>${ssrInterpolate(trans("Developed By Symfonix"))}</span></p></a></div></div></footer><a data-scroll href="#wrapper" class="go-up" data-v-865e9317><i class="fa fa-angle-double-up" aria-hidden="true" data-v-865e9317></i></a><!--]-->`);
		};
	}
};
var _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/Findhouses/UserFooter.vue");
	return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
var UserFooter_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$3, [["__scopeId", "data-v-865e9317"]]);
//#endregion
//#region resources/js/utils/gmailUrl.js
/**
* Open Gmail compose with a pre-filled recipient (site contact email).
*
* @param {string} email Recipient address
* @param {{ subject?: string, body?: string }} [options]
* @returns {string}
*/
function buildGmailComposeUrl(email, options = {}) {
	const to = String(email ?? "").trim();
	if (!to) return "";
	const params = new URLSearchParams({
		view: "cm",
		fs: "1",
		to
	});
	const subject = String(options.subject ?? "").trim();
	const body = String(options.body ?? "").trim();
	if (subject) params.set("su", subject);
	if (body) params.set("body", body);
	return `https://mail.google.com/mail/?${params.toString()}`;
}
//#endregion
//#region resources/js/components/Global/FloatingContactButton.vue
var MESSENGER_URL = "https://m.me/61584547460936";
var _sfc_main$2 = {
	__name: "FloatingContactButton",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		const isOpen = ref(false);
		const globals = computed(() => page.props.globals ?? {});
		const settings = computed(() => page.props.settings ?? {});
		function trans(key) {
			return page.props.translations?.[key] || key;
		}
		const contactPhone = computed(() => {
			const contact = globals.value.contact ?? {};
			return String(contact.phone || settings.value.contact_phone || settings.value.phone || "").trim();
		});
		const contactEmail = computed(() => {
			const contact = globals.value.contact ?? {};
			return String(contact.email || settings.value.contact_email || settings.value.email || "").trim();
		});
		const whatsappHref = computed(() => {
			return resolveWhatsAppContactHref({
				whatsapp: (globals.value.social ?? {}).whatsapp,
				phone: contactPhone.value
			});
		});
		const messengerHref = computed(() => MESSENGER_URL);
		const gmailHref = computed(() => buildGmailComposeUrl(contactEmail.value));
		const phoneHref = computed(() => {
			const digits = contactPhone.value.replace(/[^\d+]/g, "");
			return digits ? `tel:${digits}` : "";
		});
		const hasAnyChannel = computed(() => Boolean(whatsappHref.value || messengerHref.value || gmailHref.value || phoneHref.value));
		const menuTitle = computed(() => trans("floating_contact.menu_title") || "Talk to us on your favorite channel");
		computed(() => trans("floating_contact.messenger") || "Messenger chat");
		const labelWhatsApp = computed(() => trans("floating_whatsapp.aria_label") || "Contact us on WhatsApp");
		const labelGmail = computed(() => trans("floating_contact.gmail") || "Gmail");
		const labelDirectCall = computed(() => trans("floating_contact.direct_call") || "Direct call");
		const menuAriaLabel = computed(() => trans("floating_contact.menu_aria") || "Contact channels");
		const toggleAriaLabel = computed(() => isOpen.value ? trans("floating_contact.aria_close") || "Close contact menu" : trans("floating_contact.aria_open") || "Open contact menu");
		function onDocumentClick(event) {
			if (!isOpen.value) return;
			if (!event.target?.closest?.(".imas-floating-contact")) isOpen.value = false;
		}
		function onEscape(event) {
			if (event.key === "Escape" && isOpen.value) isOpen.value = false;
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
				_push(`<div${ssrRenderAttrs(mergeProps({ class: ["imas-floating-contact", { "imas-floating-contact--open": isOpen.value }] }, _attrs))} data-v-d00e0268><div id="imas-floating-contact-menu" class="imas-floating-contact__panel" role="dialog"${ssrRenderAttr("aria-label", menuAriaLabel.value)} style="${ssrRenderStyle(isOpen.value ? null : { display: "none" })}" data-v-d00e0268><p class="imas-floating-contact__title text-md font-semibold" data-v-d00e0268>${ssrInterpolate(menuTitle.value)}</p><ul class="imas-floating-contact__list" data-v-d00e0268>`);
				if (whatsappHref.value) _push(`<li data-v-d00e0268><a${ssrRenderAttr("href", whatsappHref.value)} class="imas-floating-contact__item" target="_blank" rel="noopener noreferrer" data-v-d00e0268><span class="imas-floating-contact__icon imas-floating-contact__icon--whatsapp" aria-hidden="true" data-v-d00e0268><i class="fa fa-whatsapp" data-v-d00e0268></i></span><span class="imas-floating-contact__label text-sm font-medium" data-v-d00e0268>${ssrInterpolate(labelWhatsApp.value)}</span></a></li>`);
				else _push(`<!---->`);
				if (gmailHref.value) _push(`<li data-v-d00e0268><a${ssrRenderAttr("href", gmailHref.value)} class="imas-floating-contact__item" target="_blank" rel="noopener noreferrer" data-v-d00e0268><span class="imas-floating-contact__icon imas-floating-contact__icon--gmail" aria-hidden="true" data-v-d00e0268><i class="fab fa-google" data-v-d00e0268></i></span><span class="imas-floating-contact__label text-sm font-medium" data-v-d00e0268>${ssrInterpolate(labelGmail.value)}</span></a></li>`);
				else _push(`<!---->`);
				if (phoneHref.value) _push(`<li data-v-d00e0268><a${ssrRenderAttr("href", phoneHref.value)} class="imas-floating-contact__item" data-v-d00e0268><span class="imas-floating-contact__icon imas-floating-contact__icon--phone" aria-hidden="true" data-v-d00e0268><i class="fa fa-phone" data-v-d00e0268></i></span><span class="imas-floating-contact__label text-sm font-medium" data-v-d00e0268>${ssrInterpolate(labelDirectCall.value)}</span></a></li>`);
				else _push(`<!---->`);
				_push(`</ul></div><button type="button" class="imas-floating-contact__toggle"${ssrRenderAttr("aria-expanded", isOpen.value)}${ssrRenderAttr("aria-controls", isOpen.value ? "imas-floating-contact-menu" : void 0)}${ssrRenderAttr("aria-label", toggleAriaLabel.value)} data-v-d00e0268><i class="${ssrRenderClass([isOpen.value ? "fa-times" : "fa-comment", "fa callIcon"])}" aria-hidden="true" data-v-d00e0268></i></button></div>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Global/FloatingContactButton.vue");
	return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
var FloatingContactButton_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$2, [["__scopeId", "data-v-d00e0268"]]);
//#endregion
//#region resources/js/utils/syncZiggy.js
/**
* Keep global Ziggy config in sync with the current Inertia page locale.
* @routes in app.blade.php only runs on full document load; Inertia visits must merge fresh routes.
*/
function syncZiggy(ziggy) {
	if (typeof window === "undefined" || !ziggy || typeof ziggy !== "object") return;
	if (!window.Ziggy) {
		window.Ziggy = ziggy;
		return;
	}
	if (ziggy.url) window.Ziggy.url = ziggy.url;
	if (ziggy.location) window.Ziggy.location = ziggy.location;
	if (ziggy.routes) window.Ziggy.routes = ziggy.routes;
}
//#endregion
//#region resources/js/utils/isBrowser.js
/**
* True when code runs in the browser (not during Inertia SSR in Node).
*/
function isBrowser() {
	return typeof window !== "undefined" && typeof document !== "undefined";
}
//#endregion
//#region resources/js/components/Global/ClientOnly.vue
var _sfc_main$1 = {
	__name: "ClientOnly",
	__ssrInlineRender: true,
	setup(__props) {
		const mounted = ref(false);
		onMounted(() => {
			mounted.value = true;
		});
		return (_ctx, _push, _parent, _attrs) => {
			if (mounted.value) ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
			else ssrRenderSlot(_ctx.$slots, "placeholder", {}, null, _push, _parent);
		};
	}
};
var _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Global/ClientOnly.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
//#endregion
//#region resources/js/Layouts/App.vue
var _sfc_main = {
	__name: "App",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		const activeLocale = computed(() => page.props.locale || "en");
		const siteName = computed(() => String(page.props.appName || "").trim());
		/** Map short locale codes to Open Graph locale identifiers (e.g. `en` → `en_US`). */
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
		/** Other supported locales as og:locale:alternate values. */
		const ogLocaleAlternates = computed(() => {
			const switcher = page.props.locale_switcher ?? [];
			if (!Array.isArray(switcher)) return [];
			return switcher.map((item) => String(item?.code ?? "")).filter((code) => code !== "" && code !== activeLocale.value).map((code) => ({
				key: `og-locale-alt-${code}`,
				value: toOgLocale(code)
			}));
		});
		/** Multilingual SEO: alternate URLs for the current page (en / tr / ar + x-default). */
		const hreflangAlternates = computed(() => {
			const switcher = page.props.locale_switcher ?? [];
			if (!Array.isArray(switcher) || switcher.length === 0) return [];
			const items = switcher.filter((item) => typeof item?.url === "string" && item.url.trim() !== "").map((item) => ({
				hreflang: String(item.code ?? ""),
				url: item.url.trim(),
				key: `hreflang-${item.code}`
			}));
			const en = switcher.find((item) => item.code === "en");
			if (en?.url && typeof en.url === "string" && en.url.trim() !== "") items.push({
				hreflang: "x-default",
				url: en.url.trim(),
				key: "hreflang-x-default"
			});
			return items;
		});
		/** Home hero uses overlay header (white logo / light links). Inner pages need solid bar + in-flow height. */
		const navbarTransparent = computed(() => {
			try {
				if (typeof route === "function" && route().current?.("home")) return true;
			} catch {}
			const c = String(page.component || "");
			return /^Base(::|\/)Index$/i.test(c);
		});
		function safeRoute(name, fallbackHref = "#") {
			return localizedRoute(name, {}, activeLocale.value, fallbackHref);
		}
		function blogCategoryUrl(categorySlug) {
			const base = localizedRoute("blog.index", {}, activeLocale.value, "/blog");
			return `${base}${base.includes("?") ? "&" : "?"}category=${encodeURIComponent(categorySlug)}`;
		}
		const blogNavCategories = computed(() => page.props.globals?.blog_categories ?? []);
		const navbarPages = computed(() => page.props.globals?.pages?.navbar ?? []);
		const navLinks = computed(() => {
			const loc = activeLocale.value;
			const blogCategoryChildren = blogNavCategories.value.map((c) => ({
				key: `blog-category-${c.id}`,
				label: c.name,
				href: blogCategoryUrl(c.slug)
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
				{
					key: "navBar.Home",
					href: safeRoute("home", "/")
				},
				{
					key: "navBar.Buy Real Estate",
					href: safeRoute("property.index", "/property")
				},
				{
					key: "navBar.Turkish Citizenship",
					href: safeRoute("turkish-citizenship", "/turkish-citizenship")
				},
				blogsNav
			];
			if (pageNavChildren.length > 0) links.push({
				key: "navBar.Pages",
				children: pageNavChildren
			});
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
			if (!isBrowser()) return;
			const locale = activeLocale.value;
			const dir = page.props.text_direction || (locale === "ar" ? "rtl" : "ltr");
			document.documentElement.setAttribute("lang", String(locale));
			document.documentElement.setAttribute("dir", String(dir));
		}
		onMounted(() => {
			syncDocumentTextDirection();
		});
		watch(() => [activeLocale.value, page.props.text_direction], () => syncDocumentTextDirection(), { immediate: false });
		watch(() => page.props.ziggy, (ziggy) => syncZiggy(ziggy), {
			immediate: true,
			deep: true
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (siteName.value) _push(`<meta head-key="og:site_name" property="og:site_name"${ssrRenderAttr("content", siteName.value)}${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="og:locale" property="og:locale"${ssrRenderAttr("content", ogLocale.value)}${_scopeId}><!--[-->`);
						ssrRenderList(ogLocaleAlternates.value, (alt) => {
							_push(`<meta${ssrRenderAttr("head-key", alt.key)} property="og:locale:alternate"${ssrRenderAttr("content", alt.value)}${_scopeId}>`);
						});
						_push(`<!--]--><!--[-->`);
						ssrRenderList(hreflangAlternates.value, (alt) => {
							_push(`<link${ssrRenderAttr("head-key", alt.key)} rel="alternate"${ssrRenderAttr("hreflang", alt.hreflang)}${ssrRenderAttr("href", alt.url)}${_scopeId}>`);
						});
						_push(`<!--]-->`);
					} else return [
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
							}, null, 8, [
								"head-key",
								"hreflang",
								"href"
							]);
						}), 128))
					];
				}),
				_: 1
			}, _parent));
			_push(`<div id="wrapper" class="imas-theme-dark">`);
			_push(ssrRenderComponent(UserTopBar_default, null, null, _parent));
			_push(ssrRenderComponent(UserNavbar_default, {
				"nav-links": navLinks.value,
				"transparent-navbar": navbarTransparent.value
			}, null, _parent));
			_push(`<div class="clearfix"></div>`);
			ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
			_push(ssrRenderComponent(UserFooter_default, { "nav-links": navLinks.value }, null, _parent));
			_push(ssrRenderComponent(_sfc_main$1, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(ssrRenderComponent(FloatingContactButton_default, null, null, _parent, _scopeId));
					else return [createVNode(FloatingContactButton_default)];
				}),
				_: 1
			}, _parent));
			_push(`</div><!--]-->`);
		};
	}
};
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/App.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
//#endregion
export { cmsPageUrl as a, normalizeTurkishPhoneDigits as i, resolveWhatsAppContactHref as n, localizedRoute as o, formatTurkishPhone as r, useGsap as s, _sfc_main as t };
