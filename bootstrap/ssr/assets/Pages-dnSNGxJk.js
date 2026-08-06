import { f as _plugin_vue_export_helper_default, o as formatPropertyMoney, s as propertyStartPrice } from "../ssr.js";
import { o as localizedRoute, t as _sfc_main$3 } from "./App-D9ZDchZP.js";
import { t as useScrollReveal } from "./useScrollReveal-DA9XaX_4.js";
import { t as useDocumentSeo } from "./useDocumentSeo-DHpxlNQ5.js";
import { t as _sfc_main$4 } from "./InnerPageHeadingHero-B4myItxi.js";
import { t as _sfc_main$5 } from "./FeaturedPropertiesSidebar-BvXhqFk0.js";
import { a as destroyHeroRangeSliders, i as LocationAreaPicker_default, n as splitLocationIds, o as initHeroRangeSliders, r as LocationCityPicker_default, s as loadJqueryUi, t as useLocationSearchFilters } from "./useLocationSearchFilters-wPn96EyU.js";
import { n as _sfc_main$6, t as _sfc_main$7 } from "./PropertyListingPagination-BiNZbPQF.js";
import { Head, usePage } from "@inertiajs/vue3";
import { computed, createBlock, createCommentVNode, createVNode, isRef, mergeProps, nextTick, onBeforeUnmount, onMounted, openBlock, ref, toDisplayString, unref, useId, useSSRContext, watch, withCtx } from "vue";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrLooseContain, ssrLooseEqual, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
//#region Modules/Property/resources/assets/js/components/PropertyListingToolbar.vue
var _sfc_main$2 = {
	__name: "PropertyListingToolbar",
	__ssrInlineRender: true,
	props: {
		properties: {
			type: Object,
			required: true
		},
		filters: {
			type: Object,
			required: true
		},
		sort: {
			type: String,
			required: true
		}
	},
	setup(__props) {
		const props = __props;
		const page = usePage();
		computed(() => page.props.locale || "en");
		const sortToggleId = `property-sort-${useId()}`;
		const sortMenuOpen = ref(false);
		const sortRootRef = ref(null);
		const sortOptions = [{
			value: "price_asc",
			labelKey: "listing_page.price_low_high"
		}, {
			value: "price_desc",
			labelKey: "listing_page.price_high_low"
		}];
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const currentSortLabel = computed(() => {
			const opt = sortOptions.find((o) => o.value === props.sort);
			return opt ? trans(opt.labelKey) : trans("listing_page.price_low_high");
		});
		const resultsLabel = computed(() => {
			const tpl = trans("listing_page.results_count");
			const n = props.properties?.total ?? 0;
			return tpl.replace(":count", String(n));
		});
		function onDocClick(event) {
			const root = sortRootRef.value;
			if (!root || !sortMenuOpen.value) return;
			if (root.contains(event.target)) return;
			sortMenuOpen.value = false;
		}
		onMounted(() => {
			document.addEventListener("click", onDocClick);
		});
		onBeforeUnmount(() => {
			document.removeEventListener("click", onDocClick);
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({
				ref_key: "sortRootRef",
				ref: sortRootRef,
				class: ["imas-property-listings-toolbar", { "imas-property-listings-toolbar--open": sortMenuOpen.value }]
			}, _attrs))}><p class="imas-property-listings-toolbar__results text-dim">${ssrInterpolate(resultsLabel.value)}</p><div class="imas-property-listings-toolbar__sort"><button${ssrRenderAttr("id", sortToggleId)} type="button" class="imas-property-listings-toolbar__sort-toggle"${ssrRenderAttr("aria-expanded", sortMenuOpen.value)} aria-haspopup="listbox"><span class="imas-property-listings-toolbar__sort-value">${ssrInterpolate(currentSortLabel.value)}</span><i class="${ssrRenderClass([sortMenuOpen.value ? "fa-angle-up" : "fa-angle-down", "fas"])}" aria-hidden="true"></i></button><ul class="imas-property-listings-toolbar__sort-menu" role="listbox" style="${ssrRenderStyle(sortMenuOpen.value ? null : { display: "none" })}"><!--[-->`);
			ssrRenderList(sortOptions, (opt) => {
				_push(`<li role="none"><button type="button" class="${ssrRenderClass([{ "is-active": props.sort === opt.value }, "imas-property-listings-toolbar__sort-option"])}" role="option"${ssrRenderAttr("aria-selected", props.sort === opt.value)}>${ssrInterpolate(trans(opt.labelKey))}</button></li>`);
			});
			_push(`<!--]--></ul></div></div>`);
		};
	}
};
var _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyListingToolbar.vue");
	return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
//#endregion
//#region Modules/Property/resources/assets/js/components/PropertyListingSidebar.vue
var LISTING_AREA_SELECTOR = "#imas-listing-area-range";
var LISTING_PRICE_SELECTOR = "#imas-listing-price-range";
var _sfc_main$1 = {
	__name: "PropertyListingSidebar",
	__ssrInlineRender: true,
	props: {
		searchAction: {
			type: String,
			required: true
		},
		filters: {
			type: Object,
			required: true
		},
		sort: {
			type: String,
			required: true
		},
		cities: {
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
		propertyTypes: {
			type: Array,
			default: () => []
		},
		recentProperties: {
			type: Array,
			default: () => []
		},
		featuredProperties: {
			type: Array,
			default: () => []
		}
	},
	setup(__props) {
		const props = __props;
		const page = usePage();
		const searchPropertyTypeId = ref("");
		const searchUnitTypeId = ref("");
		const rangesDirty = ref(false);
		const slidersReady = ref(false);
		const { searchCityIds, searchLocationIds, filteredDistricts, filteredAreas } = useLocationSearchFilters(() => props.cities, () => props.districts, () => props.areas);
		const propertySearch = computed(() => page.props.property_search ?? page.props.globals?.property_search ?? {});
		const priceBounds = computed(() => {
			const p = propertySearch.value.price ?? {};
			return {
				min: Number(p.min ?? 0),
				max: Number(p.max ?? 1) || 1,
				currency: String(p.currency ?? "$")
			};
		});
		const areaBounds = computed(() => {
			const a = propertySearch.value.area ?? {};
			return {
				min: Number(a.min ?? 0),
				max: Number(a.max ?? 1) || 1,
				unit: String(a.unit ?? "m²")
			};
		});
		const projectUnitTypes = computed(() => propertySearch.value.project_unit_types ?? []);
		const priceRange = ref([0, 1]);
		const areaRange = ref([0, 1]);
		const includeAdvancedParams = computed(() => rangesDirty.value);
		function trans(key) {
			return page.props.translations[key] || key;
		}
		function locale() {
			return page.props.locale || "en";
		}
		function syncFromFilters(f) {
			const locationIds = f.location_id;
			let rawIds = [];
			if (Array.isArray(locationIds)) rawIds = locationIds.filter((id) => id != null && id !== "").map((id) => String(id));
			else if (locationIds != null && locationIds !== "") rawIds = [String(locationIds)];
			const { cityIds, districtAreaIds } = splitLocationIds(rawIds, props.cities, props.districts, props.areas);
			searchLocationIds.value = districtAreaIds;
			searchCityIds.value = cityIds;
			searchPropertyTypeId.value = f.property_type_id != null && f.property_type_id !== "" ? String(f.property_type_id) : "";
			const unitIds = f.project_unit_type_id;
			if (Array.isArray(unitIds) && unitIds.length > 0) searchUnitTypeId.value = String(unitIds[0]);
			else if (unitIds != null && unitIds !== "") searchUnitTypeId.value = String(unitIds);
			else searchUnitTypeId.value = "";
			const hasPrice = f.min_price != null && f.min_price !== "" && f.max_price != null && f.max_price !== "";
			const hasArea = f.min_area != null && f.min_area !== "" && f.max_area != null && f.max_area !== "";
			if (hasPrice) priceRange.value = [Number(f.min_price), Number(f.max_price)];
			else priceRange.value = [priceBounds.value.min, priceBounds.value.max];
			if (hasArea) areaRange.value = [Number(f.min_area), Number(f.max_area)];
			else areaRange.value = [areaBounds.value.min, areaBounds.value.max];
			markRangesDirty();
		}
		function syncRangeDefaults() {
			priceRange.value = [priceBounds.value.min, priceBounds.value.max];
			areaRange.value = [areaBounds.value.min, areaBounds.value.max];
		}
		function markRangesDirty() {
			const priceDefault = priceRange.value[0] === priceBounds.value.min && priceRange.value[1] === priceBounds.value.max;
			const areaDefault = areaRange.value[0] === areaBounds.value.min && areaRange.value[1] === areaBounds.value.max;
			rangesDirty.value = !priceDefault || !areaDefault;
		}
		async function ensureSliders() {
			if (slidersReady.value) return;
			const themeUrl = page.props.theme_url;
			if (!themeUrl) return;
			try {
				await loadJqueryUi(themeUrl);
				await nextTick();
				initHeroRangeSliders({
					areaSelector: LISTING_AREA_SELECTOR,
					priceSelector: LISTING_PRICE_SELECTOR,
					areaMin: areaBounds.value.min,
					areaMax: areaBounds.value.max,
					areaUnit: areaBounds.value.unit,
					priceMin: priceBounds.value.min,
					priceMax: priceBounds.value.max,
					priceUnit: priceBounds.value.currency,
					initialArea: areaRange.value,
					initialPrice: priceRange.value,
					onAreaChange(min, max) {
						areaRange.value = [min, max];
						markRangesDirty();
					},
					onPriceChange(min, max) {
						priceRange.value = [min, max];
						markRangesDirty();
					}
				});
				slidersReady.value = true;
			} catch {}
		}
		watch(() => props.filters, (f) => syncFromFilters(f ?? {}), { deep: true });
		function displayTitle(p) {
			const t = p.title;
			if (typeof t === "string" && t.trim() !== "") return t;
			if (t && typeof t === "object") {
				const raw = t[locale()] ?? t.en ?? Object.values(t).find((v) => typeof v === "string");
				if (typeof raw === "string" && raw.trim() !== "") return raw;
			}
			const pn = p.project_name;
			if (typeof pn === "string" && pn.trim() !== "") return pn;
			if (typeof pn === "object" && pn !== null) {
				const raw = pn[locale()] ?? pn.en ?? Object.values(pn).find((v) => typeof v === "string");
				if (typeof raw === "string") return raw;
			}
			return p.project_code || "—";
		}
		function formatMoney(amount) {
			return formatPropertyMoney(amount, locale());
		}
		onMounted(async () => {
			syncRangeDefaults();
			syncFromFilters(props.filters ?? {});
			await nextTick();
			await ensureSliders();
		});
		onBeforeUnmount(() => {
			destroyHeroRangeSliders({
				areaSelector: LISTING_AREA_SELECTOR,
				priceSelector: LISTING_PRICE_SELECTOR
			});
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<aside${ssrRenderAttrs(mergeProps({ class: "imas-blog-v2-sidebar" }, _attrs))} data-v-7d012e99><div class="imas-blog-v2-sidebar__box imas-property-listings-filter" data-v-7d012e99><h4 class="imas-blog-v2-sidebar__heading text-start" data-v-7d012e99>${ssrInterpolate(trans("listing_page.find_your_house"))}</h4><div class="banner-search-wrap imas-listing-property-search" data-v-7d012e99><form class="tab-content" method="get"${ssrRenderAttr("action", __props.searchAction)} data-v-7d012e99><input type="hidden" name="sort"${ssrRenderAttr("value", __props.sort)} data-v-7d012e99>`);
			if (includeAdvancedParams.value) _push(`<input type="hidden" name="min_price"${ssrRenderAttr("value", priceRange.value[0])} data-v-7d012e99>`);
			else _push(`<!---->`);
			if (includeAdvancedParams.value) _push(`<input type="hidden" name="max_price"${ssrRenderAttr("value", priceRange.value[1])} data-v-7d012e99>`);
			else _push(`<!---->`);
			if (includeAdvancedParams.value) _push(`<input type="hidden" name="min_area"${ssrRenderAttr("value", areaRange.value[0])} data-v-7d012e99>`);
			else _push(`<!---->`);
			if (includeAdvancedParams.value) _push(`<input type="hidden" name="max_area"${ssrRenderAttr("value", areaRange.value[1])} data-v-7d012e99>`);
			else _push(`<!---->`);
			_push(`<div class="tab-pane fade show active" data-v-7d012e99><div class="rld-main-search" data-v-7d012e99><div class="row imas-listing-property-search__fields" data-v-7d012e99><div class="rld-single-select imas-listing-city-cell" data-v-7d012e99>`);
			_push(ssrRenderComponent(LocationCityPicker_default, {
				modelValue: unref(searchCityIds),
				"onUpdate:modelValue": ($event) => isRef(searchCityIds) ? searchCityIds.value = $event : null,
				layout: "sidebar",
				cities: __props.cities,
				name: "location_id[]"
			}, null, _parent));
			_push(`</div><div class="rld-single-select imas-listing-location-cell" data-v-7d012e99>`);
			_push(ssrRenderComponent(LocationAreaPicker_default, {
				modelValue: unref(searchLocationIds),
				"onUpdate:modelValue": ($event) => isRef(searchLocationIds) ? searchLocationIds.value = $event : null,
				layout: "sidebar",
				districts: unref(filteredDistricts),
				areas: unref(filteredAreas),
				name: "location_id[]"
			}, null, _parent));
			_push(`</div><div class="rld-single-select" data-v-7d012e99><select class="select single-select wide" name="property_type_id" data-v-7d012e99><option value="" data-v-7d012e99${ssrIncludeBooleanAttr(Array.isArray(searchPropertyTypeId.value) ? ssrLooseContain(searchPropertyTypeId.value, "") : ssrLooseEqual(searchPropertyTypeId.value, "")) ? " selected" : ""}>${ssrInterpolate(trans("Property Type"))}</option><!--[-->`);
			ssrRenderList(__props.propertyTypes, (t) => {
				_push(`<option${ssrRenderAttr("value", String(t.id))} data-v-7d012e99${ssrIncludeBooleanAttr(Array.isArray(searchPropertyTypeId.value) ? ssrLooseContain(searchPropertyTypeId.value, String(t.id)) : ssrLooseEqual(searchPropertyTypeId.value, String(t.id))) ? " selected" : ""}>${ssrInterpolate(t.name)}</option>`);
			});
			_push(`<!--]--></select></div>`);
			if (projectUnitTypes.value.length) {
				_push(`<div class="rld-single-select unitTypeSelect" data-v-7d012e99><select class="select single-select wide" data-v-7d012e99><option value="" data-v-7d012e99${ssrIncludeBooleanAttr(Array.isArray(searchUnitTypeId.value) ? ssrLooseContain(searchUnitTypeId.value, "") : ssrLooseEqual(searchUnitTypeId.value, "")) ? " selected" : ""}>${ssrInterpolate(trans("Unit Types"))}</option><!--[-->`);
				ssrRenderList(projectUnitTypes.value, (ut) => {
					_push(`<option${ssrRenderAttr("value", String(ut.id))} data-v-7d012e99${ssrIncludeBooleanAttr(Array.isArray(searchUnitTypeId.value) ? ssrLooseContain(searchUnitTypeId.value, String(ut.id)) : ssrLooseEqual(searchUnitTypeId.value, String(ut.id))) ? " selected" : ""}>${ssrInterpolate(ut.name)}</option>`);
				});
				_push(`<!--]--></select>`);
				if (searchUnitTypeId.value) _push(`<input type="hidden" name="project_unit_type_id[]"${ssrRenderAttr("value", searchUnitTypeId.value)} data-v-7d012e99>`);
				else _push(`<!---->`);
				_push(`</div>`);
			} else _push(`<!---->`);
			_push(`<div class="imas-listing-range-panel" data-v-7d012e99><div class="main-search-field-2" data-v-7d012e99><div class="range-slider" data-v-7d012e99><label data-v-7d012e99>${ssrInterpolate(trans("Area Size"))}</label><div id="imas-listing-area-range"${ssrRenderAttr("data-min", areaBounds.value.min)}${ssrRenderAttr("data-max", areaBounds.value.max)}${ssrRenderAttr("data-unit", areaBounds.value.unit)} data-v-7d012e99></div><div class="clearfix" data-v-7d012e99></div></div><br data-v-7d012e99><div class="range-slider" data-v-7d012e99><label data-v-7d012e99>${ssrInterpolate(trans("Price Range"))}</label><div id="imas-listing-price-range"${ssrRenderAttr("data-min", priceBounds.value.min)}${ssrRenderAttr("data-max", priceBounds.value.max)}${ssrRenderAttr("data-unit", priceBounds.value.currency)} data-v-7d012e99></div><div class="clearfix" data-v-7d012e99></div></div></div></div><div class="imas-listing-property-search__submit" data-v-7d012e99><button type="submit" class="btn btn-yellow btn-block" data-v-7d012e99>${ssrInterpolate(trans("Search Now"))}</button></div></div></div></div></form></div></div>`);
			if (__props.recentProperties.length > 0) {
				_push(`<div class="imas-blog-v2-sidebar__box" data-v-7d012e99><h4 class="imas-blog-v2-sidebar__heading text-start" data-v-7d012e99>${ssrInterpolate(trans("listing_page.recent_properties"))}</h4><div class="imas-blog-v2-sidebar__recent" data-v-7d012e99><!--[-->`);
				ssrRenderList(__props.recentProperties, (p) => {
					_push(`<a${ssrRenderAttr("href", p.url)} class="imas-blog-v2-sidebar__recent-item" data-v-7d012e99><img${ssrRenderAttr("src", p.thumbnail_url)}${ssrRenderAttr("alt", displayTitle(p))} loading="lazy" data-v-7d012e99><div data-v-7d012e99><div class="imas-blog-v2-sidebar__recent-title" data-v-7d012e99>${ssrInterpolate(displayTitle(p))}</div><div class="imas-blog-v2-sidebar__recent-date text-dim text-start" data-v-7d012e99>${ssrInterpolate(formatMoney(unref(propertyStartPrice)(p)))}</div></div></a>`);
				});
				_push(`<!--]--></div></div>`);
			} else _push(`<!---->`);
			_push(ssrRenderComponent(_sfc_main$5, { "featured-properties": __props.featuredProperties }, null, _parent));
			_push(`</aside>`);
		};
	}
};
var _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyListingSidebar.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var PropertyListingSidebar_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$1, [["__scopeId", "data-v-7d012e99"]]);
//#endregion
//#region Modules/Property/resources/assets/js/Pages/index.vue
var _sfc_main = {
	__name: "index",
	__ssrInlineRender: true,
	props: {
		title: {
			type: String,
			required: true
		},
		properties: {
			type: Object,
			required: true
		},
		filters: {
			type: Object,
			required: true
		},
		sort: {
			type: String,
			required: true
		},
		propertyTypes: {
			type: Array,
			default: () => []
		},
		cities: {
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
		recentProperties: {
			type: Array,
			default: () => []
		},
		featuredProperties: {
			type: Array,
			default: () => []
		}
	},
	setup(__props) {
		const props = __props;
		const page = usePage();
		const activeLocale = computed(() => page.props.locale || "en");
		const pageRef = ref(null);
		const media = computed(() => page.props.globals?.media ?? {});
		function scrollToListingsTop() {
			pageRef.value?.scrollIntoView({
				behavior: "smooth",
				block: "start"
			});
		}
		onMounted(() => {
			scrollToListingsTop();
		});
		const propertyIndexUrl = computed(() => localizedRoute("property.index", {}, activeLocale.value, "/property"));
		const { title: documentTitle, description: metaDescription, keywords: metaKeywords, ogTitle, ogDescription, ogImage, canonical: canonicalUrl, ogUrl, twitterCard } = useDocumentSeo({
			pageTitle: () => props.title,
			canonical: () => propertyIndexUrl.value
		});
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const propertyHeadingItems = computed(() => {
			const rows = [];
			try {
				if (typeof route === "function" && route().has?.("home")) rows.push({
					title: trans("navBar.Home"),
					href: localizedRoute("home", {}, activeLocale.value, "/")
				});
			} catch {}
			rows.push({
				title: trans("navBar.Buy Real Estate"),
				href: null
			});
			return rows;
		});
		const listingsBannerUrl = computed(() => {
			const url = media.value.property_show_banner;
			if (typeof url !== "string" || url.trim() === "") return "";
			const trimmed = url.trim();
			if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) return "";
			return trimmed;
		});
		useScrollReveal(pageRef, { variant: "propertyListings" });
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: unref(documentTitle) }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (unref(metaDescription)) _push(`<meta head-key="description" name="description"${ssrRenderAttr("content", unref(metaDescription))}${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(metaKeywords)) _push(`<meta head-key="keywords" name="keywords"${ssrRenderAttr("content", unref(metaKeywords))}${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(canonicalUrl)) _push(`<link head-key="canonical" rel="canonical"${ssrRenderAttr("href", unref(canonicalUrl))}${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogTitle)) _push(`<meta head-key="og:title" property="og:title"${ssrRenderAttr("content", unref(ogTitle))}${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogDescription)) _push(`<meta head-key="og:description" property="og:description"${ssrRenderAttr("content", unref(ogDescription))}${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogImage)) _push(`<meta head-key="og:image" property="og:image"${ssrRenderAttr("content", unref(ogImage))}${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="og:type" property="og:type" content="website"${_scopeId}>`);
						if (unref(ogUrl)) _push(`<meta head-key="og:url" property="og:url"${ssrRenderAttr("content", unref(ogUrl))}${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="twitter:card" name="twitter:card"${ssrRenderAttr("content", unref(twitterCard))}${_scopeId}>`);
						if (unref(ogTitle)) _push(`<meta head-key="twitter:title" name="twitter:title"${ssrRenderAttr("content", unref(ogTitle))}${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogDescription)) _push(`<meta head-key="twitter:description" name="twitter:description"${ssrRenderAttr("content", unref(ogDescription))}${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogImage)) _push(`<meta head-key="twitter:image" name="twitter:image"${ssrRenderAttr("content", unref(ogImage))}${_scopeId}>`);
						else _push(`<!---->`);
					} else return [
						unref(metaDescription) ? (openBlock(), createBlock("meta", {
							key: 0,
							"head-key": "description",
							name: "description",
							content: unref(metaDescription)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						unref(metaKeywords) ? (openBlock(), createBlock("meta", {
							key: 1,
							"head-key": "keywords",
							name: "keywords",
							content: unref(metaKeywords)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						unref(canonicalUrl) ? (openBlock(), createBlock("link", {
							key: 2,
							"head-key": "canonical",
							rel: "canonical",
							href: unref(canonicalUrl)
						}, null, 8, ["href"])) : createCommentVNode("", true),
						unref(ogTitle) ? (openBlock(), createBlock("meta", {
							key: 3,
							"head-key": "og:title",
							property: "og:title",
							content: unref(ogTitle)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						unref(ogDescription) ? (openBlock(), createBlock("meta", {
							key: 4,
							"head-key": "og:description",
							property: "og:description",
							content: unref(ogDescription)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						unref(ogImage) ? (openBlock(), createBlock("meta", {
							key: 5,
							"head-key": "og:image",
							property: "og:image",
							content: unref(ogImage)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						createVNode("meta", {
							"head-key": "og:type",
							property: "og:type",
							content: "website"
						}),
						unref(ogUrl) ? (openBlock(), createBlock("meta", {
							key: 6,
							"head-key": "og:url",
							property: "og:url",
							content: unref(ogUrl)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						createVNode("meta", {
							"head-key": "twitter:card",
							name: "twitter:card",
							content: unref(twitterCard)
						}, null, 8, ["content"]),
						unref(ogTitle) ? (openBlock(), createBlock("meta", {
							key: 7,
							"head-key": "twitter:title",
							name: "twitter:title",
							content: unref(ogTitle)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						unref(ogDescription) ? (openBlock(), createBlock("meta", {
							key: 8,
							"head-key": "twitter:description",
							name: "twitter:description",
							content: unref(ogDescription)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						unref(ogImage) ? (openBlock(), createBlock("meta", {
							key: 9,
							"head-key": "twitter:image",
							name: "twitter:image",
							content: unref(ogImage)
						}, null, 8, ["content"])) : createCommentVNode("", true)
					];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(_sfc_main$3, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="imas-blog-v2 imas-property-listings imas-blog-section-anchor"${_scopeId}>`);
						_push(ssrRenderComponent(_sfc_main$4, {
							"page-title": __props.title,
							items: propertyHeadingItems.value,
							"banner-image-url": listingsBannerUrl.value
						}, null, _parent, _scopeId));
						_push(`<main class="imas-blog-v2__page"${_scopeId}><section class="imas-blog-v2__main"${_scopeId}>`);
						_push(ssrRenderComponent(_sfc_main$2, {
							properties: __props.properties,
							filters: __props.filters,
							sort: __props.sort
						}, null, _parent, _scopeId));
						if ((__props.properties.data ?? []).length > 0) {
							_push(`<div class="imas-property-listings__grid"${_scopeId}>`);
							_push(ssrRenderComponent(_sfc_main$6, { properties: __props.properties }, null, _parent, _scopeId));
							_push(`</div>`);
						} else _push(`<p class="imas-blog-v2__empty text-dim"${_scopeId}>${ssrInterpolate(trans("listing_page.results_count").replace(":count", "0"))}</p>`);
						_push(ssrRenderComponent(_sfc_main$7, {
							properties: __props.properties,
							onNavigate: scrollToListingsTop
						}, null, _parent, _scopeId));
						_push(`</section>`);
						_push(ssrRenderComponent(PropertyListingSidebar_default, {
							"search-action": propertyIndexUrl.value,
							filters: __props.filters,
							sort: __props.sort,
							cities: __props.cities,
							districts: __props.districts,
							areas: __props.areas,
							"property-types": __props.propertyTypes,
							"recent-properties": __props.recentProperties,
							"featured-properties": __props.featuredProperties
						}, null, _parent, _scopeId));
						_push(`</main></div>`);
					} else return [createVNode("div", {
						class: "imas-blog-v2 imas-property-listings imas-blog-section-anchor",
						ref_key: "pageRef",
						ref: pageRef
					}, [createVNode(_sfc_main$4, {
						"page-title": __props.title,
						items: propertyHeadingItems.value,
						"banner-image-url": listingsBannerUrl.value
					}, null, 8, [
						"page-title",
						"items",
						"banner-image-url"
					]), createVNode("main", { class: "imas-blog-v2__page" }, [createVNode("section", { class: "imas-blog-v2__main" }, [
						createVNode(_sfc_main$2, {
							properties: __props.properties,
							filters: __props.filters,
							sort: __props.sort
						}, null, 8, [
							"properties",
							"filters",
							"sort"
						]),
						(__props.properties.data ?? []).length > 0 ? (openBlock(), createBlock("div", {
							key: 0,
							class: "imas-property-listings__grid"
						}, [createVNode(_sfc_main$6, { properties: __props.properties }, null, 8, ["properties"])])) : (openBlock(), createBlock("p", {
							key: 1,
							class: "imas-blog-v2__empty text-dim"
						}, toDisplayString(trans("listing_page.results_count").replace(":count", "0")), 1)),
						createVNode(_sfc_main$7, {
							properties: __props.properties,
							onNavigate: scrollToListingsTop
						}, null, 8, ["properties"])
					]), createVNode(PropertyListingSidebar_default, {
						"search-action": propertyIndexUrl.value,
						filters: __props.filters,
						sort: __props.sort,
						cities: __props.cities,
						districts: __props.districts,
						areas: __props.areas,
						"property-types": __props.propertyTypes,
						"recent-properties": __props.recentProperties,
						"featured-properties": __props.featuredProperties
					}, null, 8, [
						"search-action",
						"filters",
						"sort",
						"cities",
						"districts",
						"areas",
						"property-types",
						"recent-properties",
						"featured-properties"
					])])], 512)];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
};
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/Pages/index.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
//#endregion
export { _sfc_main as default };
