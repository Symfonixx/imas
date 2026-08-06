import { f as _plugin_vue_export_helper_default } from "../ssr.js";
import { usePage } from "@inertiajs/vue3";
import { computed, mergeProps, nextTick, onBeforeUnmount, onMounted, ref, useSSRContext, watch } from "vue";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
//#region resources/js/components/Global/PhoneCountryInput.vue
var _sfc_main = {
	__name: "PhoneCountryInput",
	__ssrInlineRender: true,
	props: {
		modelValue: {
			type: String,
			default: ""
		},
		inputId: {
			type: String,
			required: true
		},
		placeholder: {
			type: String,
			default: ""
		},
		invalid: {
			type: Boolean,
			default: false
		},
		required: {
			type: Boolean,
			default: false
		}
	},
	emits: ["update:modelValue"],
	setup(__props, { emit: __emit }) {
		const props = __props;
		const emit = __emit;
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
			const list = countries.value.filter((c) => String(c.phone_code ?? "").trim() !== "");
			return list.length ? list : countries.value;
		});
		const selectedCountry = computed(() => {
			const list = countriesWithPhone.value;
			const id = countryId.value;
			if (id == null || !list.length) return null;
			return list.find((c) => c.id === id) ?? null;
		});
		const countriesWithPhoneFiltered = computed(() => {
			const list = countriesWithPhone.value;
			const raw = countrySearchQuery.value.trim();
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
		const countrySelectAriaLabel = computed(() => {
			const c = countriesWithPhone.value.find((x) => x.id === countryId.value);
			const prefix = trans("auth_modal.country_calling_code");
			if (!c) return prefix;
			const cc = displayCallingCode(c.phone_code);
			const iso = String(c.iso_code_2 ?? "").trim().toUpperCase();
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
			if (countryId.value != null && list.some((c) => c.id === countryId.value)) return;
			const prefer = {
				tr: "TR",
				en: "US",
				ar: "SA"
			}[String(page.props.locale)] ?? "TR";
			countryId.value = (list.find((c) => c.iso_code_2 === prefer) ?? list[0]).id;
		}
		watch(countriesWithPhone, pickDefaultCountry, { immediate: true });
		function digitsOnly(s) {
			return String(s ?? "").replace(/\D/g, "");
		}
		function displayCallingCode(phoneCode) {
			return digitsOnly(phoneCode) || "—";
		}
		function normalizeNationalDigits(raw) {
			let x = digitsOnly(raw);
			while (x.startsWith("0")) x = x.slice(1);
			return x;
		}
		function buildMobilePayload() {
			const c = countriesWithPhone.value.find((x) => x.id === countryId.value);
			return (c ? digitsOnly(c.phone_code) : "") + normalizeNationalDigits(mobileLocal.value);
		}
		function syncModelValue() {
			if (!normalizeNationalDigits(mobileLocal.value)) {
				emit("update:modelValue", "");
				return;
			}
			emit("update:modelValue", buildMobilePayload());
		}
		function parseMobileWithCountries(fullMobile, list) {
			const digits = digitsOnly(fullMobile);
			if (!digits) return {
				countryId: null,
				national: ""
			};
			const sorted = [...list].sort((a, b) => digitsOnly(b.phone_code).length - digitsOnly(a.phone_code).length);
			for (const country of sorted) {
				const code = digitsOnly(country.phone_code);
				if (code && digits.startsWith(code)) return {
					countryId: country.id,
					national: digits.slice(code.length)
				};
			}
			return {
				countryId: null,
				national: digits
			};
		}
		function applyIncomingMobile(value) {
			const incoming = digitsOnly(value);
			const current = buildMobilePayload();
			if (incoming && incoming === current) return;
			if (!incoming) {
				mobileLocal.value = "";
				return;
			}
			const parsed = parseMobileWithCountries(incoming, countriesWithPhone.value);
			if (parsed.countryId != null) {
				countryId.value = parsed.countryId;
				mobileLocal.value = parsed.national;
				return;
			}
			pickDefaultCountry();
			mobileLocal.value = parsed.national;
		}
		watch([mobileLocal, countryId], syncModelValue);
		watch(() => props.modelValue, (value) => {
			applyIncomingMobile(value);
		}, { immediate: true });
		function onCountryDocPointerDown(e) {
			if (!countryDropdownOpen.value) return;
			const root = countryDropdownRoot.value;
			if (root && !root.contains(e.target)) countryDropdownOpen.value = false;
		}
		function onCountryDocKeydown(e) {
			if (e.key === "Escape") countryDropdownOpen.value = false;
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
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({
				class: ["imas-auth-phone-field", {
					"imas-auth-phone-field--country-open": countryDropdownOpen.value,
					"is-invalid": __props.invalid
				}],
				dir: "ltr"
			}, _attrs))} data-v-3f1b4306><div class="imas-auth-country-select-shell" data-v-3f1b4306><button${ssrRenderAttr("id", countryTriggerId.value)} type="button" class="imas-auth-country-trigger"${ssrRenderAttr("aria-expanded", countryDropdownOpen.value)} aria-haspopup="listbox"${ssrRenderAttr("aria-label", countrySelectAriaLabel.value)} data-v-3f1b4306>`);
			if (selectedCountry.value?.flag) _push(`<img class="imas-auth-country-flag-img"${ssrRenderAttr("src", selectedCountry.value.flag)} alt="" width="22" height="16" decoding="async" loading="lazy" data-v-3f1b4306>`);
			else _push(`<!---->`);
			_push(`<span class="imas-auth-country-code-label" aria-hidden="true" data-v-3f1b4306>+${ssrInterpolate(displayCallingCode(selectedCountry.value?.phone_code))}</span></button><div class="imas-auth-country-dropdown-panel" style="${ssrRenderStyle(countryDropdownOpen.value ? null : { display: "none" })}" data-v-3f1b4306><div class="imas-auth-country-dropdown-search-wrap text-start" data-v-3f1b4306><input${ssrRenderAttr("value", countrySearchQuery.value)} type="search" enterkeyhint="search" autocomplete="off" autocorrect="off" spellcheck="false" class="imas-auth-country-dropdown-search"${ssrRenderAttr("placeholder", trans("Search"))}${ssrRenderAttr("aria-label", trans("Search"))} data-v-3f1b4306></div><ul class="imas-auth-country-dropdown-scroll" role="listbox" tabindex="-1" data-v-3f1b4306>`);
			if (countriesWithPhoneFiltered.value.length === 0) _push(`<li class="imas-auth-country-option imas-auth-country-option--empty" aria-live="polite" data-v-3f1b4306>${ssrInterpolate(trans("auth_modal.country_code_search_empty"))}</li>`);
			else _push(`<!---->`);
			_push(`<!--[-->`);
			ssrRenderList(countriesWithPhoneFiltered.value, (c) => {
				_push(`<li role="option" class="${ssrRenderClass([{ "imas-auth-country-option--selected": c.id === countryId.value }, "imas-auth-country-option"])}"${ssrRenderAttr("aria-selected", c.id === countryId.value)} data-v-3f1b4306>`);
				if (c.flag) _push(`<img class="imas-auth-country-flag-img imas-auth-country-flag-img--option"${ssrRenderAttr("src", c.flag)} alt="" width="22" height="16" decoding="async" loading="lazy" data-v-3f1b4306>`);
				else _push(`<!---->`);
				_push(`<span class="imas-auth-country-option-code" data-v-3f1b4306>+${ssrInterpolate(displayCallingCode(c.phone_code))}</span></li>`);
			});
			_push(`<!--]--></ul></div></div><span class="imas-auth-phone-sep" aria-hidden="true" data-v-3f1b4306></span><input${ssrRenderAttr("id", __props.inputId)}${ssrRenderAttr("value", mobileLocal.value)} type="tel" inputmode="numeric" autocomplete="tel-national" class="imas-auth-phone-input"${ssrIncludeBooleanAttr(__props.required) ? " required" : ""}${ssrRenderAttr("placeholder", __props.placeholder)} data-v-3f1b4306></div>`);
		};
	}
};
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Global/PhoneCountryInput.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var PhoneCountryInput_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-3f1b4306"]]);
//#endregion
export { PhoneCountryInput_default as t };
