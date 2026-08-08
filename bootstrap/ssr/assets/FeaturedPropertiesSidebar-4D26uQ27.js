import { f as _plugin_vue_export_helper_default, i as prefersReducedMotion, l as propertyLocationLine, n as unitTypeDisplayParts, o as formatPropertyMoney, s as propertyStartPrice, u as localizedField } from "../ssr.js";
import { usePage } from "@inertiajs/vue3";
import { computed, createBlock, createCommentVNode, createVNode, mergeProps, nextTick, onBeforeUnmount, onMounted, openBlock, ref, resolveDynamicComponent, toDisplayString, unref, useSSRContext, watch, withCtx, withModifiers } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle, ssrRenderVNode } from "vue/server-renderer";
//#region Modules/Property/resources/assets/js/components/FeaturedPropertyUnitAreasFlip.vue
var _sfc_main$1 = {
	__name: "FeaturedPropertyUnitAreasFlip",
	__ssrInlineRender: true,
	props: { unitTypes: {
		type: Array,
		default: () => []
	} },
	setup(__props) {
		const props = __props;
		const page = usePage();
		const trans = (key) => page.props.translations[key] || key;
		const activeIndex = ref(0);
		let rotateTimer = null;
		/** Unit type names only (skip empty labels). */
		const names = computed(() => (Array.isArray(props.unitTypes) ? props.unitTypes : []).map((ut) => unitTypeDisplayParts(ut).name).filter((name) => typeof name === "string" && name.trim() !== "" && name !== "—"));
		const activeName = computed(() => names.value[activeIndex.value] ?? names.value[0] ?? "");
		function clearRotateTimer() {
			if (rotateTimer !== null) {
				clearInterval(rotateTimer);
				rotateTimer = null;
			}
		}
		function startRotateTimer() {
			clearRotateTimer();
			activeIndex.value = 0;
			if (names.value.length <= 1 || prefersReducedMotion()) return;
			rotateTimer = setInterval(() => {
				activeIndex.value = (activeIndex.value + 1) % names.value.length;
			}, 3e3);
		}
		watch(() => props.unitTypes, () => startRotateTimer(), { deep: true });
		onMounted(() => startRotateTimer());
		onBeforeUnmount(() => clearRotateTimer());
		return (_ctx, _push, _parent, _attrs) => {
			if (names.value.length > 0) _push(`<div${ssrRenderAttrs(mergeProps({
				class: "imas-featured-unit-areas",
				role: "group",
				"aria-label": trans("properties.unit_types_aria"),
				"aria-live": "polite"
			}, _attrs))} data-v-f35e81f6><div class="imas-featured-unit-areas__flip" data-v-f35e81f6><span class="imas-featured-unit-areas__value" data-v-f35e81f6>${ssrInterpolate(activeName.value)}</span></div></div>`);
			else _push(`<!---->`);
		};
	}
};
var _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/FeaturedPropertyUnitAreasFlip.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var FeaturedPropertyUnitAreasFlip_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$1, [["__scopeId", "data-v-f35e81f6"]]);
//#endregion
//#region Modules/Property/resources/assets/js/components/FeaturedPropertiesSidebar.vue
var SLICK_SCRIPT_SRC = "/theme/findhouses/js/slick.min.js";
var SLICK_SCRIPT_ID = "imas-theme-slick-carousel";
var _sfc_main = {
	__name: "FeaturedPropertiesSidebar",
	__ssrInlineRender: true,
	props: {
		featuredProperties: {
			type: Array,
			default: () => []
		},
		/** When set, replaces the default “featured properties” sidebar title. */
		heading: {
			type: String,
			default: ""
		}
	},
	setup(__props) {
		const props = __props;
		const page = usePage();
		const slickRootRef = ref(null);
		const navRef = ref(null);
		const prevArrowRef = ref(null);
		const nextArrowRef = ref(null);
		const showCarouselArrows = computed(() => props.featuredProperties.length > 1);
		const slickIsRtl = computed(() => String(page.props.text_direction || "") === "rtl");
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const locale = () => page.props.locale || "en";
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
		function locationLine(p) {
			return propertyLocationLine(p.location, locale());
		}
		function propertyTypeLabel(p) {
			const type = p?.property_type;
			if (!type) return "";
			return localizedField(type.name, locale());
		}
		function isSoldOut(p) {
			return Boolean(p.is_sold_out);
		}
		function soldOutCardLabel(p) {
			return `${displayTitle(p)} – ${trans("properties.sold_out")}`;
		}
		function formatMoney(amount) {
			return formatPropertyMoney(amount, locale());
		}
		function loadSlickScriptOnce() {
			return new Promise((resolve, reject) => {
				if ((window.jQuery || window.$)?.fn?.slick) {
					resolve();
					return;
				}
				const existing = document.getElementById(SLICK_SCRIPT_ID);
				if (existing) {
					existing.addEventListener("load", () => resolve(), { once: true });
					existing.addEventListener("error", () => reject(/* @__PURE__ */ new Error("Slick script failed")), { once: true });
					return;
				}
				const el = document.createElement("script");
				el.id = SLICK_SCRIPT_ID;
				el.async = true;
				el.src = SLICK_SCRIPT_SRC;
				el.onload = () => resolve();
				el.onerror = () => reject(/* @__PURE__ */ new Error("Slick script failed"));
				document.body.appendChild(el);
			});
		}
		function destroySlick() {
			const el = slickRootRef.value;
			const jq = window.jQuery || window.$;
			if (!el || !jq?.fn?.slick) return;
			const $el = jq(el);
			if ($el.hasClass("slick-initialized")) $el.slick("unslick");
		}
		function initSlick() {
			const el = slickRootRef.value;
			const jq = window.jQuery || window.$;
			const prev = prevArrowRef.value;
			const next = nextArrowRef.value;
			const nav = navRef.value;
			if (!el || !jq?.fn?.slick || props.featuredProperties.length === 0) return;
			const options = {
				rtl: slickIsRtl.value,
				infinite: false,
				slidesToShow: 1,
				slidesToScroll: 1,
				dots: false,
				arrows: showCarouselArrows.value,
				adaptiveHeight: true
			};
			if (showCarouselArrows.value && prev && next && nav) {
				options.prevArrow = jq(prev);
				options.nextArrow = jq(next);
				options.appendArrows = jq(nav);
			}
			jq(el).slick(options);
		}
		async function setupSlick() {
			if (props.featuredProperties.length === 0) return;
			try {
				await loadSlickScriptOnce();
			} catch {
				return;
			}
			await nextTick();
			destroySlick();
			await nextTick();
			initSlick();
		}
		onMounted(() => {
			setupSlick();
		});
		onBeforeUnmount(() => {
			destroySlick();
		});
		watch(() => [props.featuredProperties, slickIsRtl.value], () => {
			setupSlick();
		}, { deep: true });
		return (_ctx, _push, _parent, _attrs) => {
			if (__props.featuredProperties.length > 0) {
				_push(`<div${ssrRenderAttrs(mergeProps({ class: "imas-blog-v2-sidebar__box imas-featured-properties-sidebar" }, _attrs))}><div class="imas-featured-properties-sidebar__header"><h4 class="imas-blog-v2-sidebar__heading imas-featured-properties-sidebar__heading text-start">${ssrInterpolate(__props.heading || trans("listing_page.feature_properties"))}</h4><div class="imas-featured-properties-sidebar__nav" style="${ssrRenderStyle(showCarouselArrows.value ? null : { display: "none" })}"><button type="button" class="imas-featured-properties-sidebar__arrow imas-featured-properties-sidebar__arrow--prev"${ssrRenderAttr("aria-label", trans("Previous"))}></button><button type="button" class="imas-featured-properties-sidebar__arrow imas-featured-properties-sidebar__arrow--next"${ssrRenderAttr("aria-label", trans("Next"))}></button></div></div><div class="imas-featured-properties-sidebar__body"><div class="imas-featured-properties-sidebar__carousel"><!--[-->`);
				ssrRenderList(__props.featuredProperties, (p) => {
					_push(`<article class="${ssrRenderClass([{ "imas-featured-properties-sidebar__slide--sold-out": isSoldOut(p) }, "imas-featured-properties-sidebar__slide"])}">`);
					ssrRenderVNode(_push, createVNode(resolveDynamicComponent(isSoldOut(p) ? "div" : "a"), {
						href: isSoldOut(p) ? void 0 : p.url,
						class: ["imas-featured-properties-sidebar__card", { "imas-featured-properties-sidebar__card--sold-out": isSoldOut(p) }],
						role: isSoldOut(p) ? "group" : void 0,
						"aria-label": isSoldOut(p) ? soldOutCardLabel(p) : void 0
					}, {
						default: withCtx((_, _push, _parent, _scopeId) => {
							if (_push) {
								_push(`<div class="imas-featured-properties-sidebar__media"${_scopeId}><img${ssrRenderAttr("src", p.thumbnail_url)}${ssrRenderAttr("alt", displayTitle(p))} loading="lazy"${_scopeId}><div class="imas-featured-properties-sidebar__badges"${_scopeId}><span class="imas-featured-properties-sidebar__badge imas-featured-properties-sidebar__badge--price"${_scopeId}><span class="imas-featured-properties-sidebar__price-from"${_scopeId}>${ssrInterpolate(trans("properties.price_from"))}</span><span class="imas-featured-properties-sidebar__price-amount"${_scopeId}>${ssrInterpolate(formatMoney(unref(propertyStartPrice)(p)))}</span></span><div class="imas-featured-properties-sidebar__badges-end"${_scopeId}>`);
								if (propertyTypeLabel(p)) _push(`<span class="imas-featured-properties-sidebar__badge imas-featured-properties-sidebar__badge--type"${_scopeId}>${ssrInterpolate(propertyTypeLabel(p))}</span>`);
								else _push(`<!---->`);
								if (isSoldOut(p)) _push(`<span class="imas-featured-properties-sidebar__badge imas-featured-properties-sidebar__badge--sold-out imas-sold-out-badge imas-badge--danger"${_scopeId}>${ssrInterpolate(trans("properties.sold_out"))}</span>`);
								else _push(`<!---->`);
								_push(`</div></div><div class="imas-featured-properties-sidebar__overlay"${_scopeId}><h5 class="imas-featured-properties-sidebar__title"${_scopeId}>${ssrInterpolate(displayTitle(p))}</h5>`);
								if (locationLine(p)) _push(`<p class="imas-featured-properties-sidebar__location text-dim"${_scopeId}>${ssrInterpolate(locationLine(p))}</p>`);
								else _push(`<!---->`);
								if ((p.unit_types ?? []).length > 0) {
									_push(`<div class="imas-featured-properties-sidebar__body-meta"${_scopeId}>`);
									_push(ssrRenderComponent(FeaturedPropertyUnitAreasFlip_default, { "unit-types": p.unit_types ?? [] }, null, _parent, _scopeId));
									_push(`</div>`);
								} else _push(`<!---->`);
								_push(`</div></div>`);
							} else return [createVNode("div", { class: "imas-featured-properties-sidebar__media" }, [
								createVNode("img", {
									src: p.thumbnail_url,
									alt: displayTitle(p),
									loading: "lazy"
								}, null, 8, ["src", "alt"]),
								createVNode("div", { class: "imas-featured-properties-sidebar__badges" }, [createVNode("span", { class: "imas-featured-properties-sidebar__badge imas-featured-properties-sidebar__badge--price" }, [createVNode("span", { class: "imas-featured-properties-sidebar__price-from" }, toDisplayString(trans("properties.price_from")), 1), createVNode("span", { class: "imas-featured-properties-sidebar__price-amount" }, toDisplayString(formatMoney(unref(propertyStartPrice)(p))), 1)]), createVNode("div", { class: "imas-featured-properties-sidebar__badges-end" }, [propertyTypeLabel(p) ? (openBlock(), createBlock("span", {
									key: 0,
									class: "imas-featured-properties-sidebar__badge imas-featured-properties-sidebar__badge--type"
								}, toDisplayString(propertyTypeLabel(p)), 1)) : createCommentVNode("", true), isSoldOut(p) ? (openBlock(), createBlock("span", {
									key: 1,
									class: "imas-featured-properties-sidebar__badge imas-featured-properties-sidebar__badge--sold-out imas-sold-out-badge imas-badge--danger"
								}, toDisplayString(trans("properties.sold_out")), 1)) : createCommentVNode("", true)])]),
								createVNode("div", { class: "imas-featured-properties-sidebar__overlay" }, [
									createVNode("h5", { class: "imas-featured-properties-sidebar__title" }, toDisplayString(displayTitle(p)), 1),
									locationLine(p) ? (openBlock(), createBlock("p", {
										key: 0,
										class: "imas-featured-properties-sidebar__location text-dim"
									}, toDisplayString(locationLine(p)), 1)) : createCommentVNode("", true),
									(p.unit_types ?? []).length > 0 ? (openBlock(), createBlock("div", {
										key: 1,
										class: "imas-featured-properties-sidebar__body-meta",
										onClick: withModifiers(() => {}, ["stop"])
									}, [createVNode(FeaturedPropertyUnitAreasFlip_default, { "unit-types": p.unit_types ?? [] }, null, 8, ["unit-types"])], 8, ["onClick"])) : createCommentVNode("", true)
								])
							])];
						}),
						_: 2
					}), _parent);
					_push(`</article>`);
				});
				_push(`<!--]--></div></div></div>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/FeaturedPropertiesSidebar.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
//#endregion
export { _sfc_main as t };
