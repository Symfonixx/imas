import { d as _plugin_vue_export_helper_default } from "../ssr.js";
import { usePage } from "@inertiajs/vue3";
import { computed, mergeProps, nextTick, onBeforeUnmount, onMounted, ref, toValue, unref, useSSRContext, watch } from "vue";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderList, ssrRenderStyle, ssrRenderTeleport } from "vue/server-renderer";
//#region resources/js/utils/initHeroRangeSliders.js
var jqueryUiPromise = null;
function snapToStep(value, min, max, step) {
	const snapped = min + Math.round((Number(value) - min) / step) * step;
	return Math.min(max, Math.max(min, snapped));
}
/**
* Pick a human-friendly price step from catalog min/max (1/2/5 × 10^n ladder).
* Large ranges use more target stops so steps stay at sensible increments (e.g. 500k for ~10M span).
*/
function resolvePriceStep(min, max, targetSteps) {
	const range = Number(max) - Number(min);
	if (!Number.isFinite(range) || range <= 0) return 1;
	const raw = range / (targetSteps ?? (range >= 5e6 ? 21 : 10));
	const magnitude = 10 ** Math.floor(Math.log10(raw));
	const normalized = raw / magnitude;
	let factor = 10;
	if (normalized <= 1) factor = 1;
	else if (normalized <= 2) factor = 2;
	else if (normalized <= 5) factor = 5;
	return Math.max(1, Math.round(factor * magnitude));
}
/**
* Load Find Houses jQuery UI (slider widget) once.
*/
function loadJqueryUi(themeUrl) {
	if (window.jQuery?.fn?.slider) return Promise.resolve();
	if (jqueryUiPromise) return jqueryUiPromise;
	jqueryUiPromise = new Promise((resolve, reject) => {
		const script = document.createElement("script");
		script.src = `${themeUrl}/js/jquery-ui.js`;
		script.async = true;
		script.onload = () => resolve();
		script.onerror = () => reject(/* @__PURE__ */ new Error("Failed to load jQuery UI for range sliders"));
		document.body.appendChild(script);
	});
	return jqueryUiPromise;
}
function removeValueInputs($slider) {
	$slider.find(".first-slider-value, .second-slider-value").remove();
}
function mountValueInputs($slider) {
	$slider.append("<input type='text' class='first-slider-value' disabled/><input type='text' class='second-slider-value' disabled/>");
	return {
		$first: $slider.children(".first-slider-value"),
		$second: $slider.children(".second-slider-value")
	};
}
function initAreaSlider($slider, { min, max, unit, values, onChange }) {
	if (!$slider.length) return;
	if ($slider.hasClass("ui-slider")) $slider.slider("destroy");
	$slider.empty();
	removeValueInputs($slider);
	const { $first, $second } = mountValueInputs($slider);
	const dataMin = Number(min);
	const dataMax = Number(max);
	const dataUnit = unit || "";
	$slider.slider({
		range: true,
		min: dataMin,
		max: dataMax,
		step: 10,
		values: values ?? [dataMin, dataMax],
		slide(_event, ui) {
			$first.val(`${ui.values[0]} ${dataUnit}`);
			$second.val(`${ui.values[1]} ${dataUnit}`);
			onChange?.(ui.values[0], ui.values[1]);
		}
	});
	const current = $slider.slider("values");
	$first.val(`${current[0]} ${dataUnit}`);
	$second.val(`${current[1]} ${dataUnit}`);
	onChange?.(current[0], current[1]);
}
function initPriceSlider($slider, { min, max, unit, values, step, onChange }) {
	if (!$slider.length) return;
	if ($slider.hasClass("ui-slider")) $slider.slider("destroy");
	$slider.empty();
	removeValueInputs($slider);
	const { $first, $second } = mountValueInputs($slider);
	const dataMin = Number(min);
	const dataMax = Number(max);
	const dataUnit = unit || "$";
	const dataStep = step != null && Number(step) > 0 ? Number(step) : resolvePriceStep(dataMin, dataMax);
	const format = (n) => dataUnit + Number(n).toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
	const rawValues = values ?? [dataMin, dataMax];
	const snappedValues = [snapToStep(rawValues[0], dataMin, dataMax, dataStep), snapToStep(rawValues[1], dataMin, dataMax, dataStep)];
	$slider.slider({
		range: true,
		min: dataMin,
		max: dataMax,
		step: dataStep,
		values: snappedValues,
		slide(_event, ui) {
			$first.val(format(ui.values[0]));
			$second.val(format(ui.values[1]));
			onChange?.(ui.values[0], ui.values[1]);
		}
	});
	const current = $slider.slider("values");
	$first.val(format(current[0]));
	$second.val(format(current[1]));
	onChange?.(current[0], current[1]);
}
/**
* Initialize theme-style area + price range sliders (Find Houses range.js behaviour).
* Value inputs are appended inside each slider element (same as home hero).
*/
function initHeroRangeSliders({ areaSelector = "#imas-hero-area-range", priceSelector = "#imas-hero-price-range", areaMin = 0, areaMax = 1e3, areaUnit = "m²", priceMin = 0, priceMax = 6e5, priceUnit = "$", priceStep = null, initialArea = null, initialPrice = null, onAreaChange, onPriceChange } = {}) {
	const $ = window.jQuery;
	if (!$?.fn?.slider) return;
	initAreaSlider($(areaSelector), {
		min: areaMin,
		max: areaMax,
		unit: areaUnit,
		values: initialArea,
		onChange: onAreaChange
	});
	initPriceSlider($(priceSelector), {
		min: priceMin,
		max: priceMax,
		unit: priceUnit,
		step: priceStep,
		values: initialPrice,
		onChange: onPriceChange
	});
}
function destroyHeroRangeSliders({ areaSelector = "#imas-hero-area-range", priceSelector = "#imas-hero-price-range" } = {}) {
	const $ = window.jQuery;
	if (!$?.fn?.slider) return;
	[areaSelector, priceSelector].forEach((selector) => {
		const $slider = $(selector);
		if (!$slider.length) return;
		if ($slider.hasClass("ui-slider")) $slider.slider("destroy");
		$slider.empty();
		removeValueInputs($slider);
	});
}
//#endregion
//#region resources/js/composables/useLocationPickerPanel.js
var MOBILE_PANEL_MQ = "(max-width: 991.98px)";
/**
* Shared panel positioning and open/close behavior for location pickers.
*
* @param {import('vue').MaybeRefOrGetter<'hero' | 'sidebar'>} layout
*/
function useLocationPickerPanel(layout) {
	const page = usePage();
	const rootRef = ref(null);
	const triggerRef = ref(null);
	const panelRef = ref(null);
	const open = ref(false);
	const useMobilePanel = ref(false);
	const panelStyle = ref({});
	/**
	* Gate `<Teleport to="body">` until after mount (`v-if="mounted"`). Inertia's
	* Vue SSR does not emit teleport-to-body content into the server HTML, so
	* rendering the teleport during SSR / initial hydration causes a node
	* mismatch. Staying `false` through SSR and the first client render makes both
	* emit a single placeholder comment; the panel is created and teleported to
	* <body> only after hydration completes.
	*/
	const mounted = ref(false);
	let mobileMq = null;
	function isRtlDocument() {
		return document.documentElement.getAttribute("dir") === "rtl" || page.props.text_direction === "rtl" || page.props.locale === "ar";
	}
	function syncMobilePanelMode() {
		useMobilePanel.value = typeof window !== "undefined" && window.matchMedia("(max-width: 991.98px)").matches;
		if (open.value) schedulePanelPositionUpdate();
	}
	function schedulePanelPositionUpdate() {
		nextTick(() => {
			updatePanelPosition();
			requestAnimationFrame(updatePanelPosition);
		});
	}
	function resolvePanelWidth(triggerWidth, viewportMargin) {
		const base = Math.round(triggerWidth);
		const widened = Math.max(Math.round(base * 1.4), base + 48);
		return Math.min(widened, window.innerWidth - viewportMargin, 352);
	}
	function resolveDesktopPanelWidth(triggerRect, viewportMargin) {
		if (toValue(layout) === "sidebar") return Math.min(Math.round(triggerRect.width), window.innerWidth - viewportMargin);
		return Math.min(230, window.innerWidth - viewportMargin);
	}
	function updatePanelPosition() {
		if (!open.value || !triggerRef.value) {
			panelStyle.value = {};
			return;
		}
		const triggerRect = triggerRef.value.getBoundingClientRect();
		const margin = 12;
		const top = `${Math.round(triggerRect.bottom + 6)}px`;
		const viewportMargin = useMobilePanel.value && window.innerWidth <= 576 ? 24 : margin * 2;
		if (useMobilePanel.value) {
			panelStyle.value = {
				position: "fixed",
				top,
				left: "50%",
				right: "auto",
				transform: "translateX(-50%)",
				width: `${resolvePanelWidth(triggerRect.width, viewportMargin)}px`,
				maxWidth: `calc(100vw - ${viewportMargin}px)`
			};
			return;
		}
		const panelWidth = resolveDesktopPanelWidth(triggerRect, margin * 2);
		if (isRtlDocument()) {
			let right = window.innerWidth - triggerRect.right;
			const maxRight = window.innerWidth - panelWidth - margin;
			right = Math.min(Math.max(right, margin), maxRight);
			panelStyle.value = {
				position: "fixed",
				top,
				right: `${Math.round(right)}px`,
				left: "auto",
				width: `${panelWidth}px`,
				maxWidth: `${panelWidth}px`,
				transform: "none"
			};
			return;
		}
		let left = triggerRect.left;
		left = Math.max(margin, Math.min(left, window.innerWidth - panelWidth - margin));
		panelStyle.value = {
			position: "fixed",
			top,
			left: `${Math.round(left)}px`,
			right: "auto",
			width: `${panelWidth}px`,
			maxWidth: `${panelWidth}px`,
			transform: "none"
		};
	}
	function onViewportChange() {
		syncMobilePanelMode();
		updatePanelPosition();
	}
	function onOutsideClick(event) {
		if (!open.value) return;
		const root = rootRef.value;
		const panel = panelRef.value;
		if (root && root.contains(event.target) || panel && panel.contains(event.target)) return;
		open.value = false;
	}
	function onKeydown(event) {
		if (event.key === "Escape" && open.value) open.value = false;
	}
	function toggle(onOpen) {
		open.value = !open.value;
		if (open.value) {
			onOpen?.();
			schedulePanelPositionUpdate();
		} else panelStyle.value = {};
	}
	function close() {
		open.value = false;
		panelStyle.value = {};
	}
	onMounted(() => {
		mounted.value = true;
		document.addEventListener("click", onOutsideClick, true);
		document.addEventListener("keydown", onKeydown);
		window.addEventListener("resize", onViewportChange);
		window.addEventListener("scroll", onViewportChange, true);
		if (typeof window !== "undefined") {
			mobileMq = window.matchMedia(MOBILE_PANEL_MQ);
			syncMobilePanelMode();
			mobileMq.addEventListener("change", syncMobilePanelMode);
		}
	});
	onBeforeUnmount(() => {
		document.removeEventListener("click", onOutsideClick, true);
		document.removeEventListener("keydown", onKeydown);
		window.removeEventListener("resize", onViewportChange);
		window.removeEventListener("scroll", onViewportChange, true);
		mobileMq?.removeEventListener("change", syncMobilePanelMode);
	});
	watch(open, (isOpen) => {
		if (!isOpen) panelStyle.value = {};
	});
	return {
		rootRef,
		triggerRef,
		panelRef,
		open,
		useMobilePanel,
		panelStyle,
		mounted,
		toggle,
		close,
		schedulePanelPositionUpdate
	};
}
//#endregion
//#region resources/js/components/Global/LocationAreaPicker.vue
var _sfc_main$1 = {
	__name: "LocationAreaPicker",
	__ssrInlineRender: true,
	props: {
		modelValue: {
			type: Array,
			default: () => []
		},
		districts: {
			type: Array,
			default: () => []
		},
		areas: {
			type: Array,
			default: () => []
		},
		name: {
			type: String,
			default: "location_id[]"
		},
		placeholder: {
			type: String,
			default: ""
		},
		/** hero: fixed 230px panel on desktop; sidebar: match trigger width */
		layout: {
			type: String,
			default: "hero",
			validator: (value) => ["hero", "sidebar"].includes(value)
		}
	},
	emits: ["update:modelValue"],
	setup(__props, { emit: __emit }) {
		const props = __props;
		const page = usePage();
		const expandedSections = ref({
			areas: true,
			districts: true
		});
		const { rootRef, triggerRef, panelRef, open, useMobilePanel, panelStyle, mounted, toggle, schedulePanelPositionUpdate } = useLocationPickerPanel(() => props.layout);
		function trans(key) {
			return page.props.translations?.[key] || key;
		}
		const selected = computed(() => props.modelValue.map((v) => String(v)));
		const districtIds = computed(() => new Set(props.districts.map((d) => String(d.id))));
		const areaIds = computed(() => new Set(props.areas.map((a) => String(a.id))));
		const districtSelectedCount = computed(() => selected.value.filter((id) => districtIds.value.has(id)).length);
		const areaSelectedCount = computed(() => selected.value.filter((id) => areaIds.value.has(id)).length);
		const triggerLabel = computed(() => {
			const count = selected.value.length;
			if (count === 0) return props.placeholder || trans("Location");
			if (count === 1) {
				const match = [...props.districts, ...props.areas].find((x) => String(x.id) === selected.value[0]);
				if (match?.name) return match.name;
			}
			return `${count} ${trans("selected")}`;
		});
		function isSelected(id) {
			return selected.value.includes(String(id));
		}
		function expandAllSections() {
			expandedSections.value = {
				areas: true,
				districts: true
			};
		}
		function isSectionExpanded(section) {
			return expandedSections.value[section] === true;
		}
		watch(open, (isOpen) => {
			if (isOpen) {
				expandAllSections();
				schedulePanelPositionUpdate();
			}
		});
		watch(expandedSections, () => {
			if (open.value) schedulePanelPositionUpdate();
		}, { deep: true });
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({
				ref_key: "rootRef",
				ref: rootRef,
				class: ["imas-loc-picker", {
					"is-open": unref(open),
					"imas-loc-picker--sidebar": __props.layout === "sidebar"
				}]
			}, _attrs))} data-v-f094e2b9><button type="button" class="imas-loc-picker__trigger"${ssrRenderAttr("aria-expanded", unref(open))} aria-haspopup="listbox" data-v-f094e2b9><i class="fa fa-map-marker" aria-hidden="true" data-v-f094e2b9></i><span class="imas-loc-picker__trigger-label" data-v-f094e2b9>${ssrInterpolate(triggerLabel.value)}</span><i class="${ssrRenderClass([unref(open) ? "fa-angle-up" : "fa-angle-down", "fa imas-loc-picker__caret"])}" aria-hidden="true" data-v-f094e2b9></i></button>`);
			if (unref(mounted)) ssrRenderTeleport(_push, (_push) => {
				_push(`<div class="${ssrRenderClass([{
					"imas-loc-picker__panel--mobile": unref(useMobilePanel),
					"imas-loc-picker__panel--sidebar": __props.layout === "sidebar"
				}, "imas-loc-picker__panel"])}" style="${ssrRenderStyle([unref(panelStyle), unref(open) ? null : { display: "none" }])}" data-v-f094e2b9><div class="imas-loc-picker__columns" data-v-f094e2b9><div class="imas-loc-picker__section" data-v-f094e2b9><button type="button" class="imas-loc-picker__column-head"${ssrRenderAttr("aria-expanded", isSectionExpanded("areas"))} data-v-f094e2b9><span class="imas-loc-picker__column-head-main" data-v-f094e2b9><span class="imas-loc-picker__column-title" data-v-f094e2b9>${ssrInterpolate(trans("Areas"))}</span>`);
				if (areaSelectedCount.value) _push(`<span class="imas-loc-picker__column-count" data-v-f094e2b9>${ssrInterpolate(areaSelectedCount.value)}</span>`);
				else _push(`<!---->`);
				_push(`</span><i class="${ssrRenderClass([isSectionExpanded("areas") ? "fa-angle-up" : "fa-angle-down", "fa imas-loc-picker__section-caret"])}" aria-hidden="true" data-v-f094e2b9></i></button><div class="imas-loc-picker__grid" style="${ssrRenderStyle(isSectionExpanded("areas") ? null : { display: "none" })}" data-v-f094e2b9><!--[-->`);
				ssrRenderList(__props.areas, (a) => {
					_push(`<label class="${ssrRenderClass([{ "is-checked": isSelected(a.id) }, "imas-loc-picker__item"])}" data-v-f094e2b9><input type="checkbox"${ssrIncludeBooleanAttr(isSelected(a.id)) ? " checked" : ""} data-v-f094e2b9><span class="imas-loc-picker__item-label" data-v-f094e2b9>${ssrInterpolate(a.name)}</span></label>`);
				});
				_push(`<!--]-->`);
				if (!__props.areas.length) _push(`<p class="imas-loc-picker__empty text-dim" data-v-f094e2b9>${ssrInterpolate(trans("No results found"))}</p>`);
				else _push(`<!---->`);
				_push(`</div></div><div class="imas-loc-picker__section" data-v-f094e2b9><button type="button" class="imas-loc-picker__column-head"${ssrRenderAttr("aria-expanded", isSectionExpanded("districts"))} data-v-f094e2b9><span class="imas-loc-picker__column-head-main" data-v-f094e2b9><span class="imas-loc-picker__column-title" data-v-f094e2b9>${ssrInterpolate(trans("Municipalities"))}</span>`);
				if (districtSelectedCount.value) _push(`<span class="imas-loc-picker__column-count" data-v-f094e2b9>${ssrInterpolate(districtSelectedCount.value)}</span>`);
				else _push(`<!---->`);
				_push(`</span><i class="${ssrRenderClass([isSectionExpanded("districts") ? "fa-angle-up" : "fa-angle-down", "fa imas-loc-picker__section-caret"])}" aria-hidden="true" data-v-f094e2b9></i></button><div class="imas-loc-picker__grid" style="${ssrRenderStyle(isSectionExpanded("districts") ? null : { display: "none" })}" data-v-f094e2b9><!--[-->`);
				ssrRenderList(__props.districts, (d) => {
					_push(`<label class="${ssrRenderClass([{ "is-checked": isSelected(d.id) }, "imas-loc-picker__item"])}" data-v-f094e2b9><input type="checkbox"${ssrIncludeBooleanAttr(isSelected(d.id)) ? " checked" : ""} data-v-f094e2b9><span class="imas-loc-picker__item-label" data-v-f094e2b9>${ssrInterpolate(d.name)}</span></label>`);
				});
				_push(`<!--]-->`);
				if (!__props.districts.length) _push(`<p class="imas-loc-picker__empty text-dim" data-v-f094e2b9>${ssrInterpolate(trans("No results found"))}</p>`);
				else _push(`<!---->`);
				_push(`</div></div></div>`);
				if (selected.value.length) _push(`<div class="imas-loc-picker__footer" data-v-f094e2b9><button type="button" class="imas-loc-picker__clear" data-v-f094e2b9>${ssrInterpolate(trans("Clear"))}</button></div>`);
				else _push(`<!---->`);
				_push(`</div>`);
			}, "body", false, _parent);
			else _push(`<!---->`);
			_push(`<!--[-->`);
			ssrRenderList(selected.value, (id) => {
				_push(`<input type="hidden"${ssrRenderAttr("name", __props.name)}${ssrRenderAttr("value", id)} data-v-f094e2b9>`);
			});
			_push(`<!--]--></div>`);
		};
	}
};
var _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Global/LocationAreaPicker.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var LocationAreaPicker_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$1, [["__scopeId", "data-v-f094e2b9"]]);
//#endregion
//#region resources/js/components/Global/LocationCityPicker.vue
var _sfc_main = {
	__name: "LocationCityPicker",
	__ssrInlineRender: true,
	props: {
		modelValue: {
			type: Array,
			default: () => []
		},
		cities: {
			type: Array,
			default: () => []
		},
		name: {
			type: String,
			default: "location_id[]"
		},
		placeholder: {
			type: String,
			default: ""
		},
		layout: {
			type: String,
			default: "hero",
			validator: (value) => ["hero", "sidebar"].includes(value)
		}
	},
	emits: ["update:modelValue"],
	setup(__props, { emit: __emit }) {
		const props = __props;
		const page = usePage();
		const { rootRef, triggerRef, panelRef, open, useMobilePanel, panelStyle, mounted, toggle } = useLocationPickerPanel(() => props.layout);
		function trans(key) {
			return page.props.translations?.[key] || key;
		}
		const selected = computed(() => props.modelValue.map((v) => String(v)));
		const triggerLabel = computed(() => {
			const count = selected.value.length;
			if (count === 0) return props.placeholder || trans("Cities");
			if (count === 1) {
				const match = props.cities.find((x) => String(x.id) === selected.value[0]);
				if (match?.name) return match.name;
			}
			return `${count} ${trans("selected")}`;
		});
		function isSelected(id) {
			return selected.value.includes(String(id));
		}
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({
				ref_key: "rootRef",
				ref: rootRef,
				class: ["imas-loc-picker", {
					"is-open": unref(open),
					"imas-loc-picker--sidebar": __props.layout === "sidebar"
				}]
			}, _attrs))} data-v-ca390c6b><button type="button" class="imas-loc-picker__trigger"${ssrRenderAttr("aria-expanded", unref(open))} aria-haspopup="listbox" data-v-ca390c6b><i class="fa fa-map-marker" aria-hidden="true" data-v-ca390c6b></i><span class="imas-loc-picker__trigger-label" data-v-ca390c6b>${ssrInterpolate(triggerLabel.value)}</span><i class="${ssrRenderClass([unref(open) ? "fa-angle-up" : "fa-angle-down", "fa imas-loc-picker__caret"])}" aria-hidden="true" data-v-ca390c6b></i></button>`);
			if (unref(mounted)) ssrRenderTeleport(_push, (_push) => {
				_push(`<div class="${ssrRenderClass([{
					"imas-loc-picker__panel--mobile": unref(useMobilePanel),
					"imas-loc-picker__panel--sidebar": __props.layout === "sidebar"
				}, "imas-loc-picker__panel"])}" style="${ssrRenderStyle([unref(panelStyle), unref(open) ? null : { display: "none" }])}" data-v-ca390c6b><div class="imas-loc-picker__columns" data-v-ca390c6b><div class="imas-loc-picker__section" data-v-ca390c6b><div class="imas-loc-picker__column-head imas-loc-picker__column-head--static" data-v-ca390c6b><span class="imas-loc-picker__column-head-main" data-v-ca390c6b><span class="imas-loc-picker__column-title" data-v-ca390c6b>${ssrInterpolate(trans("Cities"))}</span>`);
				if (selected.value.length) _push(`<span class="imas-loc-picker__column-count" data-v-ca390c6b>${ssrInterpolate(selected.value.length)}</span>`);
				else _push(`<!---->`);
				_push(`</span></div><div class="imas-loc-picker__grid" data-v-ca390c6b><!--[-->`);
				ssrRenderList(__props.cities, (city) => {
					_push(`<label class="${ssrRenderClass([{ "is-checked": isSelected(city.id) }, "imas-loc-picker__item"])}" data-v-ca390c6b><input type="checkbox"${ssrIncludeBooleanAttr(isSelected(city.id)) ? " checked" : ""} data-v-ca390c6b><span class="imas-loc-picker__item-label" data-v-ca390c6b>${ssrInterpolate(city.name)}</span></label>`);
				});
				_push(`<!--]-->`);
				if (!__props.cities.length) _push(`<p class="imas-loc-picker__empty text-dim" data-v-ca390c6b>${ssrInterpolate(trans("No results found"))}</p>`);
				else _push(`<!---->`);
				_push(`</div></div></div>`);
				if (selected.value.length) _push(`<div class="imas-loc-picker__footer" data-v-ca390c6b><button type="button" class="imas-loc-picker__clear" data-v-ca390c6b>${ssrInterpolate(trans("Clear"))}</button></div>`);
				else _push(`<!---->`);
				_push(`</div>`);
			}, "body", false, _parent);
			else _push(`<!---->`);
			_push(`<!--[-->`);
			ssrRenderList(selected.value, (id) => {
				_push(`<input type="hidden"${ssrRenderAttr("name", __props.name)}${ssrRenderAttr("value", id)} data-v-ca390c6b>`);
			});
			_push(`<!--]--></div>`);
		};
	}
};
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Global/LocationCityPicker.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var LocationCityPicker_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-ca390c6b"]]);
//#endregion
//#region resources/js/utils/locationSearchFilters.js
function toIdSet(ids) {
	return new Set((ids ?? []).map((id) => String(id)));
}
/**
* @param {Array<{ id: number|string, parent_id?: number|string|null }>} districts
* @param {Array<string|number>} selectedCityIds
*/
function filterDistrictsByCities(districts, selectedCityIds) {
	if (!selectedCityIds?.length) return districts ?? [];
	const citySet = toIdSet(selectedCityIds);
	return (districts ?? []).filter((district) => citySet.has(String(district.parent_id)));
}
/**
* @param {Array<{ id: number|string, parent_id?: number|string|null }>} areas
* @param {Array<{ id: number|string, parent_id?: number|string|null }>} districts
* @param {Array<string|number>} selectedCityIds
*/
function filterAreasByCities(areas, districts, selectedCityIds) {
	if (!selectedCityIds?.length) return areas ?? [];
	const visibleDistrictIds = toIdSet(filterDistrictsByCities(districts, selectedCityIds).map((d) => d.id));
	return (areas ?? []).filter((area) => visibleDistrictIds.has(String(area.parent_id)));
}
/**
* @param {Array<string|number>} ids
* @param {Array<{ id: number|string }>} cities
* @param {Array<{ id: number|string }>} districts
* @param {Array<{ id: number|string }>} areas
* @returns {{ cityIds: string[], districtAreaIds: string[] }}
*/
function splitLocationIds(ids, cities, districts, areas) {
	const normalized = (ids ?? []).filter((id) => id != null && id !== "").map((id) => String(id));
	const cityIdSet = toIdSet((cities ?? []).map((c) => c.id));
	const districtAreaIdSet = toIdSet([...(districts ?? []).map((d) => d.id), ...(areas ?? []).map((a) => a.id)]);
	const cityIds = [];
	const districtAreaIds = [];
	for (const id of normalized) if (cityIdSet.has(id)) cityIds.push(id);
	else if (districtAreaIdSet.has(id)) districtAreaIds.push(id);
	return {
		cityIds,
		districtAreaIds
	};
}
/**
* @param {Array<string|number>} ids
* @param {Array<{ id: number|string }>} districts
* @param {Array<{ id: number|string }>} areas
*/
function pruneDistrictAreaIds(ids, districts, areas) {
	const allowed = toIdSet([...(districts ?? []).map((d) => d.id), ...(areas ?? []).map((a) => a.id)]);
	return (ids ?? []).map((id) => String(id)).filter((id) => allowed.has(id));
}
//#endregion
//#region resources/js/composables/useLocationSearchFilters.js
/**
* @param {import('vue').MaybeRefOrGetter<Array<{ id: number|string, parent_id?: number|string|null }>>} cities
* @param {import('vue').MaybeRefOrGetter<Array<{ id: number|string, parent_id?: number|string|null }>>} districts
* @param {import('vue').MaybeRefOrGetter<Array<{ id: number|string, parent_id?: number|string|null }>>} areas
*/
function useLocationSearchFilters(cities, districts, areas) {
	const searchCityIds = ref([]);
	const searchLocationIds = ref([]);
	const filteredDistricts = computed(() => filterDistrictsByCities(toValue(districts), searchCityIds.value));
	const filteredAreas = computed(() => filterAreasByCities(toValue(areas), toValue(districts), searchCityIds.value));
	watch(searchCityIds, () => {
		searchLocationIds.value = pruneDistrictAreaIds(searchLocationIds.value, filteredDistricts.value, filteredAreas.value);
	});
	return {
		searchCityIds,
		searchLocationIds,
		filteredDistricts,
		filteredAreas
	};
}
//#endregion
export { destroyHeroRangeSliders as a, LocationAreaPicker_default as i, splitLocationIds as n, initHeroRangeSliders as o, LocationCityPicker_default as r, loadJqueryUi as s, useLocationSearchFilters as t };
