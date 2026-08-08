import { f as _plugin_vue_export_helper_default } from "../ssr.js";
import { t as useScrollReveal } from "./useScrollReveal-BBzB6gt6.js";
import { usePage } from "@inertiajs/vue3";
import { computed, mergeProps, nextTick, onBeforeUnmount, onMounted, ref, resolveComponent, useSSRContext, watch } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
//#region resources/js/components/Global/CustomHeading.vue
var _sfc_main$1 = {
	__name: "CustomHeading",
	__ssrInlineRender: true,
	props: {
		title: {
			type: String,
			default: ""
		},
		textColor: {
			type: String,
			default: "#333333"
		}
	},
	setup(__props) {
		const props = __props;
		const hasContent = computed(() => props.title.trim() !== "" || props.subtitle.trim() !== "");
		const textStyle = computed(() => ({ color: props.textColor?.trim() || "#333333" }));
		return (_ctx, _push, _parent, _attrs) => {
			if (hasContent.value) {
				_push(`<div${ssrRenderAttrs(mergeProps({ class: "imas-custom-heading call-info" }, _attrs))} data-v-168c1983>`);
				if (__props.title.trim()) _push(`<h3 class="text-start imas-custom-heading__title" style="${ssrRenderStyle(textStyle.value)}" data-v-168c1983>${ssrInterpolate(__props.title)}</h3>`);
				else _push(`<!---->`);
				_push(`</div>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Global/CustomHeading.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var CustomHeading_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$1, [["__scopeId", "data-v-168c1983"]]);
//#endregion
//#region Modules/Base/resources/assets/js/components/PopularPropertiesSection.vue
var _sfc_main = {
	__name: "PopularPropertiesSection",
	__ssrInlineRender: true,
	props: {
		properties: {
			type: Array,
			default: () => []
		},
		title: {
			type: String,
			default: ""
		},
		subtitle: {
			type: String,
			default: ""
		},
		hideTitle: {
			type: Boolean,
			default: false
		},
		customTitle: {
			type: String,
			default: ""
		},
		headingTextColor: {
			type: String,
			default: "#eef2f8"
		}
	},
	setup(__props) {
		const page = usePage();
		/** RTL/LTR for card content; scroll rail stays `dir="ltr"` so scrollLeft is reliable. */
		const slideTextDir = computed(() => page.props.text_direction === "rtl" ? "rtl" : "ltr");
		const props = __props;
		const sectionRef = ref(null);
		useScrollReveal(sectionRef, {
			preset: "home",
			variant: "carousel",
			when: computed(() => props.properties.length > 0)
		});
		const viewportRef = ref(null);
		/** Matches theme `index.html` slick-lancers: 4 / 2 / 1 */
		const visibleCount = ref(4);
		const activePage = ref(0);
		const isDragging = ref(false);
		function syncVisibleCount() {
			if (typeof window === "undefined") return;
			const w = window.innerWidth;
			if (w < 769) visibleCount.value = 1;
			else if (w < 1293) visibleCount.value = 2;
			else visibleCount.value = 4;
		}
		const pageCount = computed(() => {
			const n = props.properties.length;
			const v = visibleCount.value;
			if (n <= v) return 1;
			return n - v + 1;
		});
		/** Offset from the left edge of scroll content to align slide `index` at the viewport’s left edge. */
		function cumulativeOffsetBeforeSlide(vp, index) {
			const slides = vp.querySelectorAll(".imas-popular-slide");
			let left = 0;
			const stop = Math.min(index, slides.length);
			for (let i = 0; i < stop; i++) left += slides[i].offsetWidth;
			return left;
		}
		function scrollViewportToSlideIndex(vp, index, behavior = "smooth") {
			const offset = cumulativeOffsetBeforeSlide(vp, index);
			vp.scrollTo({
				left: offset,
				behavior
			});
		}
		function goToPage(index) {
			const vp = viewportRef.value;
			if (!vp) return;
			requestAnimationFrame(() => {
				const slides = vp.querySelectorAll(".imas-popular-slide");
				const maxStart = Math.max(0, slides.length - visibleCount.value);
				const i = Math.min(maxStart, Math.max(0, index));
				scrollViewportToSlideIndex(vp, i);
				activePage.value = i;
			});
		}
		function syncActiveFromScroll() {
			const vp = viewportRef.value;
			if (!vp) return;
			const slides = vp.querySelectorAll(".imas-popular-slide");
			if (!slides.length) return;
			const maxStart = Math.max(0, slides.length - visibleCount.value);
			const pos = vp.scrollLeft;
			let best = 0;
			let bestDist = Infinity;
			for (let i = 0; i <= maxStart; i++) {
				const target = cumulativeOffsetBeforeSlide(vp, i);
				const d = Math.abs(pos - target);
				if (d < bestDist) {
					bestDist = d;
					best = i;
				}
			}
			activePage.value = best;
		}
		function onResize() {
			syncVisibleCount();
			requestAnimationFrame(() => {
				const vp = viewportRef.value;
				if (!vp) return;
				const i = Math.min(activePage.value, pageCount.value - 1);
				activePage.value = i;
				scrollViewportToSlideIndex(vp, i, "auto");
				syncActiveFromScroll();
			});
		}
		watch(() => props.properties, async () => {
			activePage.value = 0;
			await nextTick();
			goToPage(0);
		}, { deep: true });
		watch(visibleCount, async () => {
			activePage.value = Math.min(activePage.value, pageCount.value - 1);
			await nextTick();
			const vp = viewportRef.value;
			if (vp) {
				scrollViewportToSlideIndex(vp, activePage.value, "auto");
				syncActiveFromScroll();
			}
		});
		onMounted(async () => {
			syncVisibleCount();
			await nextTick();
			onResize();
			window.addEventListener("resize", onResize);
		});
		onBeforeUnmount(() => {
			window.removeEventListener("resize", onResize);
		});
		return (_ctx, _push, _parent, _attrs) => {
			const _component_PropertyCard = resolveComponent("PropertyCard");
			if (__props.properties.length > 0) {
				_push(`<section${ssrRenderAttrs(mergeProps({
					ref_key: "sectionRef",
					ref: sectionRef,
					class: "featured portfolio rec-pro disc"
				}, _attrs))} data-v-2c31c6e8><div class="container-fluid" data-v-2c31c6e8>`);
				if (!__props.hideTitle) _push(`<div class="sec-title discover" data-v-2c31c6e8><h2 data-v-2c31c6e8><span data-v-2c31c6e8>${ssrInterpolate(__props.title)}</span></h2><p data-v-2c31c6e8>${ssrInterpolate(__props.subtitle)}</p></div>`);
				else _push(`<!---->`);
				_push(`<div class="portfolio col-xl-12" data-v-2c31c6e8>`);
				if (__props.hideTitle) _push(ssrRenderComponent(CustomHeading_default, {
					class: "pl-3",
					title: __props.customTitle,
					"text-color": __props.headingTextColor
				}, null, _parent));
				else _push(`<!---->`);
				_push(`<div class="imas-popular-rail" data-v-2c31c6e8><div dir="ltr" class="${ssrRenderClass([{ "is-dragging": isDragging.value }, "slick-lancers imas-popular-viewport"])}" style="${ssrRenderStyle({ "--imas-slides-visible": visibleCount.value })}" data-v-2c31c6e8><!--[-->`);
				ssrRenderList(__props.properties, (property) => {
					_push(`<div class="imas-popular-slide agents-grid"${ssrRenderAttr("dir", slideTextDir.value)} data-v-2c31c6e8><div class="landscapes w-100" data-v-2c31c6e8>`);
					_push(ssrRenderComponent(_component_PropertyCard, { property }, null, _parent));
					_push(`</div></div>`);
				});
				_push(`<!--]--></div></div>`);
				if (pageCount.value > 1) {
					_push(`<ul class="slick-dots imas-popular-dots" role="group" aria-label="Popular properties" data-v-2c31c6e8><!--[-->`);
					ssrRenderList(pageCount.value, (i) => {
						_push(`<li class="${ssrRenderClass({ "slick-active": activePage.value === i - 1 })}" data-v-2c31c6e8><button type="button"${ssrRenderAttr("aria-label", `Slide ${i}`)}${ssrRenderAttr("aria-pressed", activePage.value === i - 1)} data-v-2c31c6e8></button></li>`);
					});
					_push(`<!--]--></ul>`);
				} else _push(`<!---->`);
				_push(`</div></div></section>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/components/PopularPropertiesSection.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var PopularPropertiesSection_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-2c31c6e8"]]);
//#endregion
export { PopularPropertiesSection_default as t };
