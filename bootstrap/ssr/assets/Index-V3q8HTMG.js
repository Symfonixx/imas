import { a as refreshScrollTrigger, f as _plugin_vue_export_helper_default, i as prefersReducedMotion, r as createGsapContext } from "../ssr.js";
import { a as cmsPageUrl, o as localizedRoute, s as useGsap, t as _sfc_main$10 } from "./App-DkOZMeWI.js";
import { t as useScrollReveal } from "./useScrollReveal-BBzB6gt6.js";
import { t as useDocumentSeo } from "./useDocumentSeo-IoWJXXs8.js";
import { a as buildWebsiteSchema, l as _sfc_main$11, o as collectSocialUrls, r as buildOrganizationSchema } from "./structuredData-HzbggR2u.js";
import { t as TurkishCitizenshipSplitTitle_default } from "./TurkishCitizenshipSplitTitle-CTihbSCa.js";
import { t as PopularPropertiesSection_default } from "./PopularPropertiesSection-Di4Mi5Y1.js";
import { t as _sfc_main$12 } from "./BlogV2ArticleCard-DB7mNYG4.js";
import { a as destroyHeroRangeSliders, c as setHeroRangeSliderValues, i as LocationAreaPicker_default, r as LocationCityPicker_default, t as useLocationSearchFilters } from "./useLocationSearchFilters-wPn96EyU.js";
import { Head, usePage } from "@inertiajs/vue3";
import { computed, createBlock, createCommentVNode, createTextVNode, createVNode, isRef, mergeProps, nextTick, onBeforeUnmount, onMounted, openBlock, ref, resolveComponent, resolveDynamicComponent, toDisplayString, unref, useSSRContext, watch, withCtx } from "vue";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrLooseContain, ssrLooseEqual, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle, ssrRenderVNode } from "vue/server-renderer";
//#region resources/js/components/buttons/ReadMore.vue
var _sfc_main$9 = {
	__name: "ReadMore",
	__ssrInlineRender: true,
	props: {
		href: {
			type: String,
			required: true
		},
		text: {
			type: String,
			required: true
		}
	},
	setup(__props) {
		/**
		* Theme “view more” CTA (Find Houses featured strip).
		* @prop {string} href — Target URL (pass `route('name')` from parent or any path).
		* @prop {string} text — Button label.
		*/
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "bg-all" }, _attrs))}><a${ssrRenderAttr("href", __props.href)} class="btn btn-outline-light imas-featured-view-more">${ssrInterpolate(__props.text)}</a></div>`);
		};
	}
};
var _sfc_setup$9 = _sfc_main$9.setup;
_sfc_main$9.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/buttons/ReadMore.vue");
	return _sfc_setup$9 ? _sfc_setup$9(props, ctx) : void 0;
};
//#endregion
//#region Modules/Base/resources/assets/js/components/FeaturedPropertiesSection.vue
var MOBILE_MAX$1 = 768;
var _sfc_main$8 = {
	__name: "FeaturedPropertiesSection",
	__ssrInlineRender: true,
	props: {
		properties: {
			type: Array,
			default: () => []
		},
		title: {
			type: String,
			default: "Featured properties"
		},
		subtitle: {
			type: String,
			default: "We provide full service at every step."
		}
	},
	setup(__props) {
		const page = usePage();
		function trans(key) {
			return page.props.translations[key] || key;
		}
		/** RTL/LTR for card content; scroll rail stays `dir="ltr"` so scrollLeft is reliable. */
		const slideTextDir = computed(() => page.props.text_direction === "rtl" ? "rtl" : "ltr");
		const viewMoreHref = computed(() => {
			try {
				if (typeof route === "function" && route().has?.("property.index")) return route("property.index");
			} catch {}
			return "/property";
		});
		const props = __props;
		const sectionRef = ref(null);
		useScrollReveal(sectionRef, {
			preset: "home",
			variant: "cards",
			when: computed(() => props.properties.length > 0)
		});
		const viewportRef = ref(null);
		const isMobileCarousel = ref(false);
		const activePage = ref(0);
		const isDragging = ref(false);
		function syncLayoutMode() {
			if (typeof window === "undefined") return;
			isMobileCarousel.value = window.innerWidth <= MOBILE_MAX$1;
		}
		const pageCount = computed(() => {
			if (!isMobileCarousel.value) return 1;
			const n = props.properties.length;
			return n > 1 ? n : 1;
		});
		function cumulativeOffsetBeforeSlide(vp, index) {
			const slides = vp.querySelectorAll(".imas-featured-slide");
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
			if (!isMobileCarousel.value) return;
			const vp = viewportRef.value;
			if (!vp) return;
			requestAnimationFrame(() => {
				const slides = vp.querySelectorAll(".imas-featured-slide");
				const maxStart = Math.max(0, slides.length - 1);
				const i = Math.min(maxStart, Math.max(0, index));
				scrollViewportToSlideIndex(vp, i);
				activePage.value = i;
			});
		}
		function syncActiveFromScroll() {
			if (!isMobileCarousel.value) return;
			const vp = viewportRef.value;
			if (!vp) return;
			const slides = vp.querySelectorAll(".imas-featured-slide");
			if (!slides.length) return;
			const maxStart = Math.max(0, slides.length - 1);
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
			const wasMobile = isMobileCarousel.value;
			syncLayoutMode();
			requestAnimationFrame(async () => {
				const vp = viewportRef.value;
				if (!vp) return;
				if (isMobileCarousel.value) {
					const i = Math.min(activePage.value, pageCount.value - 1);
					activePage.value = i;
					scrollViewportToSlideIndex(vp, i, "auto");
					syncActiveFromScroll();
				} else if (wasMobile) {
					vp.scrollLeft = 0;
					activePage.value = 0;
				}
			});
		}
		watch(() => props.properties, async () => {
			activePage.value = 0;
			await nextTick();
			if (isMobileCarousel.value) goToPage(0);
			else {
				const vp = viewportRef.value;
				if (vp) vp.scrollLeft = 0;
			}
		}, { deep: true });
		watch(isMobileCarousel, async (mobile) => {
			await nextTick();
			const vp = viewportRef.value;
			if (!vp) return;
			if (mobile) {
				activePage.value = Math.min(activePage.value, pageCount.value - 1);
				scrollViewportToSlideIndex(vp, activePage.value, "auto");
				syncActiveFromScroll();
			} else {
				vp.scrollLeft = 0;
				activePage.value = 0;
			}
		});
		onMounted(async () => {
			syncLayoutMode();
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
					class: "featured portfolio bg-white-2 imas-home-section"
				}, _attrs))} data-v-e72e249f><div class="container" data-v-e72e249f><div class="sec-title" data-v-e72e249f><h2 data-v-e72e249f>${ssrInterpolate(__props.title)}</h2><p data-v-e72e249f>${ssrInterpolate(__props.subtitle)}</p></div><div class="imas-featured-rail" data-v-e72e249f><div dir="ltr" class="${ssrRenderClass([{ "is-dragging": isDragging.value }, "row portfolio-items imas-featured-viewport"])}" data-v-e72e249f><!--[-->`);
				ssrRenderList(__props.properties, (property) => {
					_push(`<div class="imas-featured-slide"${ssrRenderAttr("dir", slideTextDir.value)} data-v-e72e249f>`);
					_push(ssrRenderComponent(_component_PropertyCard, {
						property,
						"column-class": ""
					}, null, _parent));
					_push(`</div>`);
				});
				_push(`<!--]--></div>`);
				if (isMobileCarousel.value && pageCount.value > 1) {
					_push(`<ul class="slick-dots imas-featured-dots" role="group"${ssrRenderAttr("aria-label", __props.title)} data-v-e72e249f><!--[-->`);
					ssrRenderList(pageCount.value, (i) => {
						_push(`<li class="${ssrRenderClass({ "slick-active": activePage.value === i - 1 })}" data-v-e72e249f><button type="button"${ssrRenderAttr("aria-label", `Slide ${i}`)}${ssrRenderAttr("aria-pressed", activePage.value === i - 1)} data-v-e72e249f></button></li>`);
					});
					_push(`<!--]--></ul>`);
				} else _push(`<!---->`);
				_push(`</div>`);
				_push(ssrRenderComponent(_sfc_main$9, {
					href: viewMoreHref.value,
					text: trans("global.view_more")
				}, null, _parent));
				_push(`</div></section>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$8 = _sfc_main$8.setup;
_sfc_main$8.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/components/FeaturedPropertiesSection.vue");
	return _sfc_setup$8 ? _sfc_setup$8(props, ctx) : void 0;
};
var FeaturedPropertiesSection_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$8, [["__scopeId", "data-v-e72e249f"]]);
//#endregion
//#region Modules/Base/resources/assets/js/components/TurkishCitizenshipOverview.vue
var _sfc_main$7 = {
	__name: "TurkishCitizenshipOverview",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		const activeLocale = computed(() => page.props.locale || "en");
		function trans(key) {
			return page.props.translations[key] || key;
		}
		function pickTranslation(key, fallback) {
			const value = trans(key);
			if (value && value !== key) return value;
			return fallback;
		}
		const globals = computed(() => page.props.globals ?? {});
		const seo = computed(() => globals.value.seo ?? {});
		const turkishCitizenship = computed(() => globals.value.turkish_citizenship ?? {});
		const media = computed(() => globals.value.media ?? {});
		const summaryText = computed(() => {
			const raw = turkishCitizenship.value.summary ?? seo.value.turkish_citizenship ?? "";
			if (typeof raw !== "string") return "";
			return raw.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
		});
		const summaryWords = computed(() => {
			if (!summaryText.value) return [];
			return summaryText.value.split(/\s+/).filter(Boolean);
		});
		const bannerUrl = computed(() => {
			const url = turkishCitizenship.value.banner_url || media.value.turkish_citizenship_banner || "";
			if (typeof url !== "string" || url.trim() === "") return "";
			return url.trim();
		});
		const hasRealBanner = computed(() => {
			const url = bannerUrl.value;
			if (!url) return false;
			return !/\/default\.jpg(?:\?.*)?$/i.test(url);
		});
		const isVisible = computed(() => summaryText.value !== "" || hasRealBanner.value);
		const backgroundStyle = computed(() => {
			if (!bannerUrl.value) return { backgroundImage: "linear-gradient(135deg, var(--brand-navy) 0%, #2f3d5c 100%)" };
			return { backgroundImage: `url("${bannerUrl.value}")` };
		});
		const titlePrimary = computed(() => pickTranslation("turkishCitizenship.overview_title_primary", "Turkish Citizenship"));
		const titleAccent = computed(() => pickTranslation("turkishCitizenship.overview_title_accent", "by Investment Programme"));
		const sectionTitle = computed(() => `${titlePrimary.value} ${titleAccent.value}`.trim());
		const discoverLabel = computed(() => pickTranslation("turkishCitizenship.discover_more", "Discover More"));
		const citizenshipHref = computed(() => localizedRoute("turkish-citizenship", {}, activeLocale.value, "/turkish-citizenship"));
		const sectionRef = ref(null);
		const panelRef = ref(null);
		const { gsap, context, prefersReducedMotion, refreshScrollTrigger } = useGsap();
		let hasAnimated = false;
		function setupPanelAnimation() {
			const section = sectionRef.value;
			const panel = panelRef.value;
			if (!section || !panel || hasAnimated || !isVisible.value) return;
			if (prefersReducedMotion()) {
				hasAnimated = true;
				return;
			}
			context(() => {
				const primary = panel.querySelector(".imas-tc-split-title__primary");
				const accent = panel.querySelector(".imas-tc-split-title__accent");
				const divider = panel.querySelector(".imas-tc-split-title__divider");
				const words = panel.querySelectorAll(".imas-tc-overview__word");
				const cursor = panel.querySelector(".imas-tc-overview__cursor");
				const cta = panel.querySelector(".imas-tc-overview__cta");
				gsap.set(panel, {
					opacity: 0,
					y: 36
				});
				if (primary) gsap.set(primary, {
					opacity: 0,
					y: 28
				});
				if (accent) gsap.set(accent, {
					opacity: 0,
					y: 28
				});
				if (divider) gsap.set(divider, {
					opacity: 0,
					scaleX: 0,
					transformOrigin: "center center"
				});
				if (words.length) gsap.set(words, { opacity: 0 });
				if (cursor) gsap.set(cursor, { opacity: 0 });
				if (cta) gsap.set(cta, {
					opacity: 0,
					y: 18
				});
				const tl = gsap.timeline({
					scrollTrigger: {
						trigger: section,
						start: "top 88%",
						once: true,
						toggleActions: "play none none none"
					},
					defaults: { ease: "power2.out" }
				});
				tl.to(panel, {
					opacity: 1,
					y: 0,
					duration: .65
				}, 0);
				if (primary) tl.to(primary, {
					opacity: 1,
					y: 0,
					duration: .85
				}, .08);
				if (accent) tl.to(accent, {
					opacity: 1,
					y: 0,
					duration: .85
				}, .2);
				if (divider) tl.to(divider, {
					opacity: 1,
					scaleX: 1,
					duration: .5
				}, .32);
				const typingEnd = words.length > 0 ? .1 + Math.max(0, words.length - 1) * .065 + .12 : .45;
				if (words.length) tl.to(words, {
					opacity: 1,
					duration: .12,
					stagger: {
						each: .065,
						from: "start"
					}
				}, .1);
				if (cta) tl.to(cta, {
					opacity: 1,
					y: 0,
					duration: .1
				}, typingEnd + .1);
				if (cursor) {
					tl.to(cursor, {
						opacity: 1,
						duration: .12
					}, typingEnd);
					tl.to(cursor, {
						opacity: 0,
						duration: .35,
						repeat: 2,
						yoyo: true,
						ease: "steps(1)"
					}, typingEnd + .12);
				}
			}, sectionRef);
			hasAnimated = true;
			refreshScrollTrigger();
		}
		function schedulePanelAnimation() {
			nextTick(() => {
				nextTick(setupPanelAnimation);
			});
		}
		watch(isVisible, (visible) => {
			if (visible) schedulePanelAnimation();
		}, { immediate: true });
		return (_ctx, _push, _parent, _attrs) => {
			if (isVisible.value) {
				_push(`<section${ssrRenderAttrs(mergeProps({
					ref_key: "sectionRef",
					ref: sectionRef,
					class: "imas-tc-overview",
					"aria-label": sectionTitle.value
				}, _attrs))} data-v-27157782><div class="imas-tc-overview__bg" style="${ssrRenderStyle(backgroundStyle.value)}" aria-hidden="true" data-v-27157782></div><div class="imas-tc-overview__overlay" aria-hidden="true" data-v-27157782></div><div class="container imas-tc-overview__inner" data-v-27157782><div class="imas-tc-overview__panel" data-v-27157782>`);
				_push(ssrRenderComponent(TurkishCitizenshipSplitTitle_default, {
					primary: titlePrimary.value,
					accent: titleAccent.value,
					align: "center"
				}, null, _parent));
				if (summaryText.value) {
					_push(`<p class="imas-tc-overview__text"${ssrRenderAttr("title", summaryText.value)} data-v-27157782><span class="imas-tc-overview__text-flow" data-v-27157782><!--[-->`);
					ssrRenderList(summaryWords.value, (word, index) => {
						_push(`<span class="imas-tc-overview__word" data-v-27157782>${ssrInterpolate(word)} </span>`);
					});
					_push(`<!--]-->`);
					if (summaryWords.value.length) _push(`<span class="imas-tc-overview__cursor" aria-hidden="true" data-v-27157782>|</span>`);
					else _push(`<!---->`);
					_push(`</span></p>`);
				} else _push(`<!---->`);
				_push(`<a${ssrRenderAttr("href", citizenshipHref.value)} class="imas-tc-overview__cta" data-v-27157782><span data-v-27157782>${ssrInterpolate(discoverLabel.value)}</span></a></div></div></section>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$7 = _sfc_main$7.setup;
_sfc_main$7.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/components/TurkishCitizenshipOverview.vue");
	return _sfc_setup$7 ? _sfc_setup$7(props, ctx) : void 0;
};
var TurkishCitizenshipOverview_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$7, [["__scopeId", "data-v-27157782"]]);
//#endregion
//#region Modules/Base/resources/assets/js/components/HomeAboutus.vue
var _sfc_main$6 = {
	__name: "HomeAboutus",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		function trans(key) {
			return page.props.translations[key] || key;
		}
		function pickTranslation(key, fallback) {
			const value = trans(key);
			if (value && value !== key) return value;
			return fallback;
		}
		const globals = computed(() => page.props.globals ?? {});
		const seo = computed(() => globals.value.seo ?? {});
		const about = computed(() => globals.value.about ?? {});
		const summaryText = computed(() => {
			const raw = about.value.summary ?? seo.value.about_us ?? "";
			if (typeof raw !== "string") return "";
			return raw.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
		});
		const isVisible = computed(() => summaryText.value !== "");
		const titlePrimary = computed(() => pickTranslation("aboutUs.overview_title_primary", "About"));
		const titleAccent = computed(() => {
			const key = "aboutUs.overview_title_accent";
			const value = trans(key);
			if (value !== key) return typeof value === "string" ? value.trim() : "";
			return "Us";
		});
		const sectionTitle = computed(() => `${titlePrimary.value} ${titleAccent.value}`.trim());
		const exploreLabel = computed(() => pickTranslation("aboutUs.explore_more", "Explore More"));
		const aboutHref = computed(() => {
			try {
				if (typeof route === "function" && route().has?.("about-us")) return route("about-us");
			} catch {}
			return cmsPageUrl("about-us");
		});
		const sectionRef = ref(null);
		useScrollReveal(sectionRef, {
			preset: "home",
			variant: "panel",
			when: isVisible
		});
		return (_ctx, _push, _parent, _attrs) => {
			if (isVisible.value) {
				_push(`<section${ssrRenderAttrs(mergeProps({
					ref_key: "sectionRef",
					ref: sectionRef,
					class: "imas-about-overview",
					"aria-label": sectionTitle.value
				}, _attrs))} data-v-b7586250><div class="container imas-about-overview__inner" data-v-b7586250><div class="imas-about-overview__panel" data-v-b7586250><h2 class="imas-about-overview__title" data-v-b7586250><span class="imas-about-overview__title-primary" data-v-b7586250>${ssrInterpolate(titlePrimary.value)}</span>`);
				if (titleAccent.value) _push(`<span class="imas-about-overview__title-accent" data-v-b7586250>${ssrInterpolate(titleAccent.value)}</span>`);
				else _push(`<!---->`);
				_push(`</h2><hr class="imas-about-overview__divider" data-v-b7586250><p class="imas-about-overview__text"${ssrRenderAttr("title", summaryText.value)} data-v-b7586250>${ssrInterpolate(summaryText.value)}</p><a${ssrRenderAttr("href", aboutHref.value)} class="imas-about-overview__cta" data-v-b7586250><span data-v-b7586250>${ssrInterpolate(exploreLabel.value)}</span></a></div></div></section>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/components/HomeAboutus.vue");
	return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
var HomeAboutus_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$6, [["__scopeId", "data-v-b7586250"]]);
//#endregion
//#region Modules/Base/resources/assets/js/components/HomeServices.vue
var _sfc_main$5 = {
	__name: "HomeServices",
	__ssrInlineRender: true,
	props: { services: {
		type: Array,
		default: () => []
	} },
	setup(__props) {
		const page = usePage();
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const props = __props;
		useScrollReveal(ref(null), {
			preset: "home",
			variant: "cards",
			when: computed(() => props.services.length > 0)
		});
		const sectionTitleHtml = computed(() => {
			const raw = trans("services.title");
			if (raw.includes("<")) return raw;
			const parts = raw.split(/\s+/);
			if (parts.length < 2) return raw;
			return `<span>${parts[0]} </span>${parts.slice(1).join(" ")}`;
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<section${ssrRenderAttrs(mergeProps({ class: "home-services" }, _attrs))} data-v-e9d9e2df><div class="container" data-v-e9d9e2df>`);
			if (__props.services.length) {
				_push(`<section class="how-it-works bg-white rec-pro" data-v-e9d9e2df><div class="container-fluid" data-v-e9d9e2df><div class="sec-title" data-v-e9d9e2df><h2 data-v-e9d9e2df>${sectionTitleHtml.value ?? ""}</h2><p data-v-e9d9e2df>${ssrInterpolate(trans("services.description"))}</p></div><div class="row service-1" data-v-e9d9e2df><!--[-->`);
				ssrRenderList(__props.services, (service, index) => {
					_push(`<article class="${ssrRenderClass([{ "mb-0 pt": index === __props.services.length - 1 }, "col-lg-4 col-md-6 col-xs-12 serv"])}" data-v-e9d9e2df><div class="serv-flex" data-v-e9d9e2df><div class="art-1 img-13 corporate-service-art" data-v-e9d9e2df>`);
					if (service.image) _push(`<img class="corporate-service-img"${ssrRenderAttr("src", service.image)}${ssrRenderAttr("alt", service.title)} loading="lazy" data-v-e9d9e2df>`);
					else _push(`<!---->`);
					_push(`<h3 data-v-e9d9e2df>${ssrInterpolate(service.title)}</h3></div><div class="service-text-p" data-v-e9d9e2df><p class="text-center" data-v-e9d9e2df>${ssrInterpolate(service.description)}</p></div></div></article>`);
				});
				_push(`<!--]--></div></div></section>`);
			} else _push(`<!---->`);
			_push(`</div></section>`);
		};
	}
};
var _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/components/HomeServices.vue");
	return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
var HomeServices_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$5, [["__scopeId", "data-v-e9d9e2df"]]);
//#endregion
//#region Modules/Base/resources/assets/js/components/HomeTestimonials.vue
var _sfc_main$4 = {
	__name: "HomeTestimonials",
	__ssrInlineRender: true,
	props: { testimonials: {
		type: Array,
		default: () => []
	} },
	setup(__props) {
		const page = usePage();
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const props = __props;
		const testimonialsCarousel = ref(null);
		const sectionRef = ref(null);
		useScrollReveal(sectionRef, {
			preset: "home",
			variant: "carousel",
			when: computed(() => props.testimonials.length > 0)
		});
		function subtitleLine(item) {
			const position = String(item.position ?? "").trim();
			const client = String(item.client ?? "").trim();
			return position || client || "";
		}
		function jquery() {
			if (typeof window === "undefined") return null;
			return window.jQuery ?? window.$ ?? null;
		}
		function initOwl() {
			const $ = jquery();
			const el = testimonialsCarousel.value;
			if (!$ || !el || !props.testimonials.length) return;
			const $el = $(el);
			if ($el.data("owl.carousel")) return;
			const rtl = String(page.props.text_direction || "").toLowerCase() === "rtl";
			$el.owlCarousel({
				rtl,
				items: 2,
				loop: props.testimonials.length > 1,
				margin: 30,
				autoplay: false,
				nav: true,
				smartSpeed: 1e3,
				slideSpeed: 1e3,
				navText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
				dots: false,
				responsive: {
					0: { items: 1 },
					991: { items: 3 }
				}
			});
		}
		function destroyOwl() {
			const $ = jquery();
			const el = testimonialsCarousel.value;
			if (!$ || !el) return;
			const $el = $(el);
			if ($el.data("owl.carousel")) $el.owlCarousel("destroy");
		}
		async function refreshOwl() {
			destroyOwl();
			await nextTick();
			initOwl();
		}
		onMounted(() => {
			nextTick(() => {
				nextTick(() => {
					initOwl();
					refreshScrollTrigger();
				});
			});
		});
		watch(() => props.testimonials.map((t) => t.id).join(","), () => {
			refreshOwl();
		});
		onBeforeUnmount(() => {
			destroyOwl();
		});
		return (_ctx, _push, _parent, _attrs) => {
			if (__props.testimonials.length) {
				_push(`<section${ssrRenderAttrs(mergeProps({
					ref_key: "sectionRef",
					ref: sectionRef,
					class: "home-testimonials testimonials bg-white-2 rec-pro"
				}, _attrs))} data-v-4f1ac714><div class="container-fluid" data-v-4f1ac714><div class="sec-title" data-v-4f1ac714><h2 data-v-4f1ac714><span data-v-4f1ac714>${ssrInterpolate(trans("testimonials.title"))}</span></h2><p data-v-4f1ac714>${ssrInterpolate(trans("testimonials.description"))}</p></div><div class="owl-carousel job_clientSlide" data-v-4f1ac714><!--[-->`);
				ssrRenderList(__props.testimonials, (item) => {
					_push(`<div class="singleJobClinet bg-gray" data-v-4f1ac714><p class="quote" data-v-4f1ac714>${item.quote ?? ""}</p><div class="detailJC" data-v-4f1ac714><span data-v-4f1ac714><img${ssrRenderAttr("src", item.avatar)}${ssrRenderAttr("alt", item.name)} data-v-4f1ac714></span><h5 data-v-4f1ac714>`);
					if (item.link) _push(`<a${ssrRenderAttr("href", item.link)} rel="noopener noreferrer" target="_blank" data-v-4f1ac714>${ssrInterpolate(item.name)}</a>`);
					else _push(`<!--[-->${ssrInterpolate(item.name)}<!--]-->`);
					_push(`</h5><p data-v-4f1ac714>${ssrInterpolate(subtitleLine(item))}</p></div></div>`);
				});
				_push(`<!--]--></div></div></section>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/components/HomeTestimonials.vue");
	return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
var HomeTestimonials_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$4, [["__scopeId", "data-v-4f1ac714"]]);
//#endregion
//#region Modules/Base/resources/assets/js/components/HomeArticlesSection.vue
var MOBILE_MAX = 768;
var _sfc_main$3 = {
	__name: "HomeArticlesSection",
	__ssrInlineRender: true,
	props: { articles: {
		type: Array,
		default: () => []
	} },
	setup(__props) {
		const props = __props;
		const sectionRef = ref(null);
		useScrollReveal(sectionRef, {
			preset: "home",
			variant: "cards",
			when: computed(() => props.articles.length > 0)
		});
		const page = usePage();
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const readArticleCta = computed(() => {
			return `${trans("articles.read_more").replace(/\.\.\.$|…$/u, "").trim()} ›`;
		});
		/** RTL/LTR for card content; scroll rail stays `dir="ltr"` so scrollLeft is reliable. */
		const slideTextDir = computed(() => page.props.text_direction === "rtl" ? "rtl" : "ltr");
		const viewportRef = ref(null);
		const isMobileCarousel = ref(false);
		const activePage = ref(0);
		const isDragging = ref(false);
		function syncLayoutMode() {
			if (typeof window === "undefined") return;
			isMobileCarousel.value = window.innerWidth <= MOBILE_MAX;
		}
		const pageCount = computed(() => {
			if (!isMobileCarousel.value) return 1;
			const n = props.articles.length;
			return n > 1 ? n : 1;
		});
		function cumulativeOffsetBeforeSlide(vp, index) {
			const slides = vp.querySelectorAll(".imas-articles-slide");
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
			if (!isMobileCarousel.value) return;
			const vp = viewportRef.value;
			if (!vp) return;
			requestAnimationFrame(() => {
				const slides = vp.querySelectorAll(".imas-articles-slide");
				const maxStart = Math.max(0, slides.length - 1);
				const i = Math.min(maxStart, Math.max(0, index));
				scrollViewportToSlideIndex(vp, i);
				activePage.value = i;
			});
		}
		function syncActiveFromScroll() {
			if (!isMobileCarousel.value) return;
			const vp = viewportRef.value;
			if (!vp) return;
			const slides = vp.querySelectorAll(".imas-articles-slide");
			if (!slides.length) return;
			const maxStart = Math.max(0, slides.length - 1);
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
			const wasMobile = isMobileCarousel.value;
			syncLayoutMode();
			requestAnimationFrame(() => {
				const vp = viewportRef.value;
				if (!vp) return;
				if (isMobileCarousel.value) {
					const i = Math.min(activePage.value, pageCount.value - 1);
					activePage.value = i;
					scrollViewportToSlideIndex(vp, i, "auto");
					syncActiveFromScroll();
				} else if (wasMobile) {
					vp.scrollLeft = 0;
					activePage.value = 0;
				}
			});
		}
		watch(() => props.articles, async () => {
			activePage.value = 0;
			await nextTick();
			if (isMobileCarousel.value) goToPage(0);
			else {
				const vp = viewportRef.value;
				if (vp) vp.scrollLeft = 0;
			}
		}, { deep: true });
		watch(isMobileCarousel, async (mobile) => {
			await nextTick();
			const vp = viewportRef.value;
			if (!vp) return;
			if (mobile) {
				activePage.value = Math.min(activePage.value, pageCount.value - 1);
				scrollViewportToSlideIndex(vp, activePage.value, "auto");
				syncActiveFromScroll();
			} else {
				vp.scrollLeft = 0;
				activePage.value = 0;
			}
		});
		onMounted(async () => {
			syncLayoutMode();
			await nextTick();
			onResize();
			window.addEventListener("resize", onResize);
		});
		onBeforeUnmount(() => {
			window.removeEventListener("resize", onResize);
		});
		return (_ctx, _push, _parent, _attrs) => {
			if (__props.articles.length) {
				_push(`<section${ssrRenderAttrs(mergeProps({
					ref_key: "sectionRef",
					ref: sectionRef,
					class: "blog-section bg-white-2 imas-home-section"
				}, _attrs))} data-v-03002197><div class="container" data-v-03002197><div class="sec-title" data-v-03002197><h2 data-v-03002197>${ssrInterpolate(trans("articles.title"))}</h2><p data-v-03002197>${ssrInterpolate(trans("articles.description"))}</p></div><div class="news-wrap" data-v-03002197><div class="imas-articles-rail" data-v-03002197><div dir="ltr" class="${ssrRenderClass([{ "is-dragging": isDragging.value }, "row imas-articles-viewport"])}" data-v-03002197><!--[-->`);
				ssrRenderList(__props.articles, (article, index) => {
					_push(`<div class="imas-articles-slide d-flex"${ssrRenderAttr("dir", slideTextDir.value)} data-v-03002197>`);
					_push(ssrRenderComponent(_sfc_main$12, {
						article,
						"stagger-index": index,
						"read-more-label": trans("articles.read_more"),
						"read-article-label": readArticleCta.value
					}, null, _parent));
					_push(`</div>`);
				});
				_push(`<!--]--></div>`);
				if (isMobileCarousel.value && pageCount.value > 1) {
					_push(`<ul class="slick-dots imas-articles-dots" role="group"${ssrRenderAttr("aria-label", trans("articles.title"))} data-v-03002197><!--[-->`);
					ssrRenderList(pageCount.value, (i) => {
						_push(`<li class="${ssrRenderClass({ "slick-active": activePage.value === i - 1 })}" data-v-03002197><button type="button"${ssrRenderAttr("aria-label", `Slide ${i}`)}${ssrRenderAttr("aria-pressed", activePage.value === i - 1)} data-v-03002197></button></li>`);
					});
					_push(`<!--]--></ul>`);
				} else _push(`<!---->`);
				_push(`</div></div>`);
				_push(ssrRenderComponent(_sfc_main$9, {
					class: "btnMarginTop",
					href: "#",
					text: trans("global.view_more")
				}, null, _parent));
				_push(`</div></section>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/components/HomeArticlesSection.vue");
	return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
var HomeArticlesSection_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$3, [["__scopeId", "data-v-03002197"]]);
//#endregion
//#region Modules/Base/resources/assets/js/components/HomeHeroPropertySearch.vue
var _sfc_main$2 = {
	__name: "HomeHeroPropertySearch",
	__ssrInlineRender: true,
	props: {
		action: {
			type: String,
			required: true
		},
		purpose: {
			type: String,
			default: "sale"
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
		}
	},
	setup(__props) {
		const props = __props;
		const page = usePage();
		ref("");
		const searchPropertyTypeId = ref("");
		const searchUnitTypeId = ref("");
		const advancedOpen = ref(false);
		const rangesDirty = ref(false);
		const slidersReady = ref(false);
		const advancedWrapRef = ref(null);
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
		/** Committed values (sent with search only after Accept). */
		const priceRange = ref([0, 1]);
		const areaRange = ref([0, 1]);
		/** Draft values while the advanced panel is open. */
		const draftPriceRange = ref([0, 1]);
		const draftAreaRange = ref([0, 1]);
		const includeAdvancedParams = computed(() => rangesDirty.value);
		function trans(key) {
			return page.props.translations[key] || key;
		}
		function syncRangeDefaults() {
			priceRange.value = [priceBounds.value.min, priceBounds.value.max];
			areaRange.value = [areaBounds.value.min, areaBounds.value.max];
			syncDraftFromCommitted();
		}
		function syncDraftFromCommitted() {
			draftPriceRange.value = [...priceRange.value];
			draftAreaRange.value = [...areaRange.value];
		}
		function applySliderUi(areaValues, priceValues) {
			if (!slidersReady.value) return;
			setHeroRangeSliderValues({
				areaValues,
				priceValues,
				areaUnit: areaBounds.value.unit,
				priceUnit: priceBounds.value.currency
			});
		}
		function cancelAdvanced() {
			syncDraftFromCommitted();
			applySliderUi(areaRange.value, priceRange.value);
			advancedOpen.value = false;
		}
		function onDocumentPointerDown(event) {
			if (!advancedOpen.value) return;
			const wrap = advancedWrapRef.value;
			if (wrap && !wrap.contains(event.target)) cancelAdvanced();
		}
		onMounted(() => {
			syncRangeDefaults();
			document.addEventListener("pointerdown", onDocumentPointerDown);
		});
		onBeforeUnmount(() => {
			document.removeEventListener("pointerdown", onDocumentPointerDown);
			destroyHeroRangeSliders();
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "banner-search-wrap imas-hero-property-search" }, _attrs))} data-v-79a8a1b6><form class="tab-content" method="get"${ssrRenderAttr("action", __props.action)} data-v-79a8a1b6><input type="hidden" name="purpose"${ssrRenderAttr("value", __props.purpose)} data-v-79a8a1b6>`);
			if (includeAdvancedParams.value) _push(`<input type="hidden" name="min_price"${ssrRenderAttr("value", priceRange.value[0])} data-v-79a8a1b6>`);
			else _push(`<!---->`);
			if (includeAdvancedParams.value) _push(`<input type="hidden" name="max_price"${ssrRenderAttr("value", priceRange.value[1])} data-v-79a8a1b6>`);
			else _push(`<!---->`);
			if (includeAdvancedParams.value) _push(`<input type="hidden" name="min_area"${ssrRenderAttr("value", areaRange.value[0])} data-v-79a8a1b6>`);
			else _push(`<!---->`);
			if (includeAdvancedParams.value) _push(`<input type="hidden" name="max_area"${ssrRenderAttr("value", areaRange.value[1])} data-v-79a8a1b6>`);
			else _push(`<!---->`);
			_push(`<div class="tab-pane fade show active" data-v-79a8a1b6><div class="rld-main-search" data-v-79a8a1b6><div class="imas-hero-search-row" data-v-79a8a1b6><div class="imas-hero-search-fields" data-v-79a8a1b6><div class="rld-single-select imas-hero-city-cell" data-v-79a8a1b6>`);
			_push(ssrRenderComponent(LocationCityPicker_default, {
				modelValue: unref(searchCityIds),
				"onUpdate:modelValue": ($event) => isRef(searchCityIds) ? searchCityIds.value = $event : null,
				cities: __props.cities,
				name: "location_id[]"
			}, null, _parent));
			_push(`</div><div class="rld-single-select imas-hero-location-cell" data-v-79a8a1b6>`);
			_push(ssrRenderComponent(LocationAreaPicker_default, {
				modelValue: unref(searchLocationIds),
				"onUpdate:modelValue": ($event) => isRef(searchLocationIds) ? searchLocationIds.value = $event : null,
				districts: unref(filteredDistricts),
				areas: unref(filteredAreas),
				name: "location_id[]"
			}, null, _parent));
			_push(`</div><div class="rld-single-select ml-22" data-v-79a8a1b6><select class="select single-select wide" name="property_type_id" data-v-79a8a1b6><option value="" data-v-79a8a1b6${ssrIncludeBooleanAttr(Array.isArray(searchPropertyTypeId.value) ? ssrLooseContain(searchPropertyTypeId.value, "") : ssrLooseEqual(searchPropertyTypeId.value, "")) ? " selected" : ""}>${ssrInterpolate(trans("Property Type"))}</option><!--[-->`);
			ssrRenderList(__props.propertyTypes, (t) => {
				_push(`<option${ssrRenderAttr("value", String(t.id))} data-v-79a8a1b6${ssrIncludeBooleanAttr(Array.isArray(searchPropertyTypeId.value) ? ssrLooseContain(searchPropertyTypeId.value, String(t.id)) : ssrLooseEqual(searchPropertyTypeId.value, String(t.id))) ? " selected" : ""}>${ssrInterpolate(t.name)}</option>`);
			});
			_push(`<!--]--></select></div>`);
			if (projectUnitTypes.value.length) {
				_push(`<div class="rld-single-select unitTypeSelect" data-v-79a8a1b6><select class="select single-select wide" data-v-79a8a1b6><option value="" data-v-79a8a1b6${ssrIncludeBooleanAttr(Array.isArray(searchUnitTypeId.value) ? ssrLooseContain(searchUnitTypeId.value, "") : ssrLooseEqual(searchUnitTypeId.value, "")) ? " selected" : ""}>${ssrInterpolate(trans("Unit Types"))}</option><!--[-->`);
				ssrRenderList(projectUnitTypes.value, (ut) => {
					_push(`<option${ssrRenderAttr("value", String(ut.id))} data-v-79a8a1b6${ssrIncludeBooleanAttr(Array.isArray(searchUnitTypeId.value) ? ssrLooseContain(searchUnitTypeId.value, String(ut.id)) : ssrLooseEqual(searchUnitTypeId.value, String(ut.id))) ? " selected" : ""}>${ssrInterpolate(ut.name)}</option>`);
				});
				_push(`<!--]--></select>`);
				if (searchUnitTypeId.value) _push(`<input type="hidden" name="project_unit_type_id[]"${ssrRenderAttr("value", searchUnitTypeId.value)} data-v-79a8a1b6>`);
				else _push(`<!---->`);
				_push(`</div>`);
			} else _push(`<!---->`);
			_push(`<div class="imas-hero-advanced-wrap" data-v-79a8a1b6><div class="dropdown-filter" role="button" tabindex="0"${ssrRenderAttr("aria-expanded", advancedOpen.value)} data-v-79a8a1b6><span data-v-79a8a1b6>${ssrInterpolate(trans("Advanced Search"))}</span></div><div class="${ssrRenderClass([{ "filter-block": advancedOpen.value }, "explore__form-checkbox-list full-filter imas-hero-advanced-panel"])}" data-v-79a8a1b6><div class="row" data-v-79a8a1b6><div class="col-12 py-1 pr-30 sld" data-v-79a8a1b6><div class="main-search-field-2" data-v-79a8a1b6><div class="range-slider" data-v-79a8a1b6><label data-v-79a8a1b6>${ssrInterpolate(trans("Area Size"))}</label><div id="imas-hero-area-range"${ssrRenderAttr("data-min", areaBounds.value.min)}${ssrRenderAttr("data-max", areaBounds.value.max)}${ssrRenderAttr("data-unit", areaBounds.value.unit)} data-v-79a8a1b6></div><div class="clearfix" data-v-79a8a1b6></div></div><br data-v-79a8a1b6><div class="range-slider" data-v-79a8a1b6><label data-v-79a8a1b6>${ssrInterpolate(trans("Price Range"))}</label><div id="imas-hero-price-range"${ssrRenderAttr("data-min", priceBounds.value.min)}${ssrRenderAttr("data-max", priceBounds.value.max)}${ssrRenderAttr("data-unit", priceBounds.value.currency)} data-v-79a8a1b6></div><div class="clearfix" data-v-79a8a1b6></div></div></div><div class="imas-hero-advanced-actions" data-v-79a8a1b6><button type="button" class="btn imas-hero-advanced-btn imas-hero-advanced-btn--cancel" data-v-79a8a1b6>${ssrInterpolate(trans("Cancel"))}</button><button type="button" class="btn btn-yellow imas-hero-advanced-btn imas-hero-advanced-btn--accept" data-v-79a8a1b6>${ssrInterpolate(trans("Accept"))}</button></div></div></div></div></div><div class="imas-hero-search-submit" data-v-79a8a1b6><button type="submit" class="btn btn-yellow" data-v-79a8a1b6>${ssrInterpolate(trans("Search Now"))}</button></div></div></div></div></div></form></div>`);
		};
	}
};
var _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/components/HomeHeroPropertySearch.vue");
	return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
var HomeHeroPropertySearch_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$2, [["__scopeId", "data-v-79a8a1b6"]]);
//#endregion
//#region Modules/Base/resources/assets/js/components/HomeHero.vue
var MOBILE_HERO_MQ = "(max-width: 767.98px)";
var _sfc_main$1 = {
	__name: "HomeHero",
	__ssrInlineRender: true,
	props: {
		welcomeTitle: {
			type: String,
			required: true
		},
		welcomeSubtitle: {
			type: String,
			required: true
		},
		slides: {
			type: Array,
			default: () => []
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
		}
	},
	setup(__props) {
		const props = __props;
		const purpose = ref("sale");
		const activeSlideIndex = ref(0);
		const heroCopyRef = ref(null);
		const titleLeadRef = ref(null);
		const titleTypedRef = ref(null);
		const heroSubtitleRef = ref(null);
		const heroActionRef = ref(null);
		const heroFilterRef = ref(null);
		const displayedTypedText = ref("");
		const showTypeCursor = ref(false);
		const useStaticHeroCopy = ref(false);
		let mobileHeroMq = null;
		const { gsap, context } = useGsap();
		let slideTimer = null;
		let heroAnimToken = 0;
		/** @type {import('gsap').Context | null} */
		let heroSearchCtx = null;
		let searchEnterHasPlayed = false;
		const page = usePage();
		function trans(key) {
			return page.props.translations[key] || key;
		}
		function pickTranslation(key, fallback) {
			const value = trans(key);
			if (value && value !== key) return value;
			return fallback;
		}
		const slides = computed(() => props.slides || []);
		const activeSlide = computed(() => slides.value[activeSlideIndex.value] ?? null);
		const activeSlideLink = computed(() => {
			const link = activeSlide.value?.link;
			if (typeof link === "string" && link.trim() !== "") return link.trim();
			return "";
		});
		const actionLabel = computed(() => pickTranslation("turkishCitizenship.discover_more", "Discover More"));
		function pickSlideText(value, fallback) {
			if (typeof value !== "string") return fallback;
			const trimmed = value.trim();
			return trimmed !== "" ? trimmed : fallback;
		}
		const heroTitle = computed(() => slides.value.length > 0 ? pickSlideText(activeSlide.value?.title, props.welcomeTitle) : props.welcomeTitle);
		const heroSubtitle = computed(() => slides.value.length > 0 ? pickSlideText(activeSlide.value?.description, props.welcomeSubtitle) : props.welcomeSubtitle);
		const heroTitleTag = computed(() => "span");
		const heroTitleAttrs = computed(() => ({}));
		/**
		* @param {string} title
		* @returns {{ lead: string, typed: string }}
		*/
		function splitTitleForTypewriter(title) {
			const words = String(title || "").trim().split(/\s+/u).filter(Boolean);
			if (words.length === 0) return {
				lead: "",
				typed: ""
			};
			if (words.length <= 2) return {
				lead: "",
				typed: words.join(" ")
			};
			return {
				lead: words.slice(0, -2).join(" "),
				typed: words.slice(-2).join(" ")
			};
		}
		const titleParts = computed(() => splitTitleForTypewriter(heroTitle.value));
		const activeLocale = computed(() => page.props.locale || "en");
		const propertyIndexUrl = computed(() => localizedRoute("property.index", {}, activeLocale.value, "/property"));
		function updateStaticHeroCopy() {
			if (typeof window === "undefined" || !window.matchMedia) {
				useStaticHeroCopy.value = false;
				return;
			}
			useStaticHeroCopy.value = window.matchMedia(MOBILE_HERO_MQ).matches;
		}
		function killHeroCopyTweens() {
			const root = heroCopyRef.value;
			if (!root) return;
			gsap.killTweensOf(root);
			gsap.killTweensOf(root.querySelectorAll("*"));
		}
		function setHeroCopyVisible() {
			const root = heroCopyRef.value;
			if (!root) return;
			const targets = [
				root.querySelector(".imas-hero-title"),
				root.querySelector(".imas-hero-subtitle"),
				...root.querySelectorAll(".imas-hero-title-lead, .imas-hero-title-typed, .imas-hero-title-link, .imas-hero-action"),
				titleLeadRef.value,
				titleTypedRef.value,
				heroSubtitleRef.value,
				heroActionRef.value
			].filter(Boolean);
			gsap.killTweensOf(targets);
			if (targets.length) gsap.set(targets, {
				opacity: 1,
				y: 0,
				clearProps: "opacity,transform"
			});
		}
		function onMobileHeroMqChange() {
			updateStaticHeroCopy();
			nextTick(() => {
				killHeroCopyTweens();
				setHeroCopyVisible();
				if (useStaticHeroCopy.value) {
					displayedTypedText.value = titleParts.value.typed;
					showTypeCursor.value = false;
				} else playHeroCopyAnimation();
			});
		}
		function playHeroSearchEnterAnimation() {
			if (searchEnterHasPlayed) return;
			const filterEl = heroFilterRef.value;
			if (!filterEl) return;
			searchEnterHasPlayed = true;
			if (prefersReducedMotion()) {
				gsap.set(filterEl, {
					opacity: 1,
					scale: 1
				});
				return;
			}
			heroSearchCtx = createGsapContext(() => {
				gsap.fromTo(filterEl, {
					opacity: 0,
					scale: .5
				}, {
					opacity: 1,
					scale: 1,
					duration: 2.75,
					ease: "power2.out"
				});
			}, heroFilterRef);
		}
		function playHeroCopyAnimation() {
			const token = ++heroAnimToken;
			const { lead, typed } = titleParts.value;
			if (prefersReducedMotion() || useStaticHeroCopy.value) {
				displayedTypedText.value = typed;
				showTypeCursor.value = false;
				killHeroCopyTweens();
				setHeroCopyVisible();
				return;
			}
			displayedTypedText.value = "";
			showTypeCursor.value = false;
			const leadEl = titleLeadRef.value;
			const typedEl = titleTypedRef.value;
			const subEl = heroSubtitleRef.value;
			const actionEl = heroActionRef.value;
			context(() => {
				const tl = gsap.timeline({
					defaults: { ease: "power2.out" },
					onComplete: () => {
						if (token !== heroAnimToken) return;
						showTypeCursor.value = false;
						setHeroCopyVisible();
					}
				});
				if (subEl) gsap.set(subEl, {
					opacity: 0,
					y: -20
				});
				if (actionEl) gsap.set(actionEl, {
					opacity: 0,
					y: 12
				});
				if (leadEl && lead) {
					gsap.set(leadEl, {
						opacity: 0,
						y: -20
					});
					tl.fromTo(leadEl, {
						opacity: 0,
						y: -20
					}, {
						opacity: 1,
						y: 0,
						duration: 1.15
					}, 0);
				} else if (typedEl && !lead) gsap.set(typedEl, {
					opacity: 0,
					y: -20
				});
				const typeStart = lead ? .65 : 0;
				const charDelay = .095;
				const chars = [...typed];
				if (!lead && typedEl && chars.length) tl.fromTo(typedEl, {
					opacity: 0,
					y: -20
				}, {
					opacity: 1,
					y: 0,
					duration: .85
				}, typeStart);
				if (chars.length) {
					tl.call(() => {
						if (token !== heroAnimToken) return;
						showTypeCursor.value = true;
					}, null, typeStart + (lead ? .16 : .42));
					chars.forEach((char, index) => {
						tl.call(() => {
							if (token !== heroAnimToken) return;
							displayedTypedText.value += char;
						}, null, typeStart + (lead ? .24 : .52) + index * charDelay);
					});
				}
				const afterType = typeStart + (lead ? .24 : .52) + Math.max(chars.length, 1) * charDelay + .2;
				tl.call(() => {
					if (token !== heroAnimToken) return;
					showTypeCursor.value = false;
				}, null, afterType);
				if (actionEl) tl.fromTo(actionEl, {
					opacity: 0,
					y: 12
				}, {
					opacity: 1,
					y: 0,
					duration: .85
				}, afterType + .08);
				if (subEl) tl.fromTo(subEl, {
					opacity: 0,
					y: -20
				}, {
					opacity: 1,
					y: 0,
					duration: 1.1
				}, afterType + (actionEl ? .28 : .18));
			}, heroCopyRef);
		}
		function startSlideAutoplay() {
			stopSlideAutoplay();
			if (slides.value.length <= 1) return;
			slideTimer = window.setInterval(() => {
				activeSlideIndex.value = (activeSlideIndex.value + 1) % slides.value.length;
			}, 6500);
		}
		function stopSlideAutoplay() {
			if (slideTimer !== null) {
				clearInterval(slideTimer);
				slideTimer = null;
			}
		}
		watch(slides, () => {
			activeSlideIndex.value = 0;
			startSlideAutoplay();
			nextTick(() => playHeroCopyAnimation());
		}, { deep: true });
		watch(activeSlideIndex, () => {
			nextTick(() => playHeroCopyAnimation());
		});
		onMounted(() => {
			updateStaticHeroCopy();
			mobileHeroMq = window.matchMedia(MOBILE_HERO_MQ);
			mobileHeroMq.addEventListener("change", onMobileHeroMqChange);
			startSlideAutoplay();
			nextTick(() => {
				playHeroSearchEnterAnimation();
				playHeroCopyAnimation();
			});
		});
		onBeforeUnmount(() => {
			mobileHeroMq?.removeEventListener("change", onMobileHeroMqChange);
			mobileHeroMq = null;
			killHeroCopyTweens();
			stopSlideAutoplay();
			heroSearchCtx?.revert?.();
			heroSearchCtx = null;
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<section${ssrRenderAttrs(mergeProps({
				id: "hero-area",
				class: ["parallax-searchs home15 overlay thome-6 thome-1", { "imas-hero-slider": slides.value.length > 0 }],
				"data-stellar-background-ratio": "0.5"
			}, _attrs))} data-v-524e89df>`);
			if (slides.value.length > 0) {
				_push(`<div class="imas-hero-slider__layers" aria-hidden="true" data-v-524e89df><!--[-->`);
				ssrRenderList(slides.value, (slide, index) => {
					_push(`<div class="${ssrRenderClass([{ "imas-hero-slider__slide--active": index === activeSlideIndex.value }, "imas-hero-slider__slide"])}" style="${ssrRenderStyle({ backgroundImage: `url(${slide.image})` })}" data-v-524e89df></div>`);
				});
				_push(`<!--]--><div class="imas-hero-slider__scrim" data-v-524e89df></div></div>`);
			} else _push(`<!---->`);
			_push(`<div class="hero-main" data-v-524e89df><div class="container" data-v-524e89df><div class="row" data-v-524e89df><div class="col-12" data-v-524e89df><div class="hero-inner" data-v-524e89df><div class="imas-hero-copy" data-v-524e89df><div class="welcome-text" data-v-524e89df>`);
			if (useStaticHeroCopy.value) _push(`<!--[--><h1 class="h1 imas-hero-title imas-hero-title--static" data-v-524e89df>${ssrInterpolate(heroTitle.value)}</h1><p class="mt-4 imas-hero-subtitle" data-v-524e89df>${ssrInterpolate(heroSubtitle.value)}</p><!--]-->`);
			else {
				_push(`<!--[--><h1 class="h1 imas-hero-title" data-v-524e89df>`);
				ssrRenderVNode(_push, createVNode(resolveDynamicComponent(heroTitleTag.value), mergeProps(heroTitleAttrs.value, { class: "imas-hero-title-link" }), {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) {
							if (titleParts.value.lead) _push(`<span class="imas-hero-title-lead" data-v-524e89df${_scopeId}>${ssrInterpolate(titleParts.value.lead)}</span>`);
							else _push(`<!---->`);
							if (titleParts.value.lead) _push(`<span class="imas-hero-title-gap" aria-hidden="true" data-v-524e89df${_scopeId}> </span>`);
							else _push(`<!---->`);
							_push(`<span class="imas-hero-title-typed" data-v-524e89df${_scopeId}>${ssrInterpolate(displayedTypedText.value)}`);
							if (showTypeCursor.value) _push(`<span class="imas-hero-type-cursor" aria-hidden="true" data-v-524e89df${_scopeId}>|</span>`);
							else _push(`<!---->`);
							_push(`</span>`);
						} else return [
							titleParts.value.lead ? (openBlock(), createBlock("span", {
								key: 0,
								ref_key: "titleLeadRef",
								ref: titleLeadRef,
								class: "imas-hero-title-lead"
							}, toDisplayString(titleParts.value.lead), 513)) : createCommentVNode("", true),
							titleParts.value.lead ? (openBlock(), createBlock("span", {
								key: 1,
								class: "imas-hero-title-gap",
								"aria-hidden": "true"
							}, "\xA0")) : createCommentVNode("", true),
							createVNode("span", {
								ref_key: "titleTypedRef",
								ref: titleTypedRef,
								class: "imas-hero-title-typed"
							}, [createTextVNode(toDisplayString(displayedTypedText.value), 1), showTypeCursor.value ? (openBlock(), createBlock("span", {
								key: 0,
								class: "imas-hero-type-cursor",
								"aria-hidden": "true"
							}, "|")) : createCommentVNode("", true)], 512)
						];
					}),
					_: 1
				}), _parent);
				_push(`</h1><p class="mt-4 imas-hero-subtitle" data-v-524e89df>${ssrInterpolate(heroSubtitle.value)}</p><!--]-->`);
			}
			if (activeSlideLink.value) _push(`<a${ssrRenderAttr("href", activeSlideLink.value)} class="imas-hero-action" target="_blank" rel="noopener noreferrer" data-v-524e89df>${ssrInterpolate(actionLabel.value)}</a>`);
			else _push(`<!---->`);
			_push(`</div>`);
			if (slides.value.length > 1) {
				_push(`<div class="imas-hero-dots" role="group" aria-label="Slides" data-v-524e89df><!--[-->`);
				ssrRenderList(slides.value, (slide, index) => {
					_push(`<button type="button" class="${ssrRenderClass([{ "imas-hero-dot--active": index === activeSlideIndex.value }, "imas-hero-dot"])}"${ssrRenderAttr("aria-label", `Slide ${index + 1}`)}${ssrRenderAttr("aria-pressed", index === activeSlideIndex.value)} data-v-524e89df></button>`);
				});
				_push(`<!--]--></div>`);
			} else _push(`<!---->`);
			_push(`</div></div></div></div></div><div class="imas-hero-filter-shell" data-v-524e89df><div class="imas-hero-filter" data-v-524e89df>`);
			_push(ssrRenderComponent(HomeHeroPropertySearch_default, {
				action: propertyIndexUrl.value,
				purpose: purpose.value,
				"property-types": __props.propertyTypes,
				cities: __props.cities,
				districts: __props.districts,
				areas: __props.areas
			}, null, _parent));
			_push(`</div></div></div></section>`);
		};
	}
};
var _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/components/HomeHero.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var HomeHero_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$1, [["__scopeId", "data-v-524e89df"]]);
//#endregion
//#region Modules/Base/resources/assets/js/Pages/Index.vue
var _sfc_main = {
	__name: "Index",
	__ssrInlineRender: true,
	props: {
		welcomeTitle: {
			type: String,
			required: true
		},
		welcomeSubtitle: {
			type: String,
			required: true
		},
		slides: {
			type: Array,
			default: () => []
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
		featuredProperties: {
			type: Array,
			default: () => []
		},
		recommendedProperties: {
			type: Array,
			default: () => []
		},
		corporateServices: {
			type: Array,
			default: () => []
		},
		testimonials: {
			type: Array,
			default: () => []
		},
		articles: {
			type: Array,
			default: () => []
		}
	},
	setup(__props) {
		const page = usePage();
		const { globals, media, title: documentTitle, description: metaDescription, keywords: metaKeywords, ogTitle, ogDescription, ogImage, canonical: canonicalUrl, ogUrl, twitterCard } = useDocumentSeo({
			useGlobalTitleTemplate: true,
			fallbackPageTitle: "Home",
			canonical: () => {
				if (typeof route !== "function" || !route().has?.("home")) return "";
				try {
					return route("home");
				} catch {
					return "";
				}
			}
		});
		const organizationSchema = computed(() => {
			const contact = globals.value.contact ?? {};
			const logo = media.value.white_logo || media.value.black_logo || media.value.meta_img || "";
			return buildOrganizationSchema({
				name: page.props.appName,
				url: canonicalUrl.value,
				description: metaDescription.value,
				logo,
				email: contact.email,
				phone: contact.phone,
				address: contact.address,
				sameAs: collectSocialUrls(globals.value.social)
			});
		});
		const organizationJsonLd = computed(() => {
			const schema = organizationSchema.value;
			return schema ? JSON.stringify(schema) : "";
		});
		const propertySearchUrl = computed(() => {
			if (typeof route !== "function" || !route().has?.("property.index")) return "";
			try {
				const base = route("property.index");
				return `${base}${base.includes("?") ? "&" : "?"}q={search_term_string}`;
			} catch {
				return "";
			}
		});
		const websiteSchema = computed(() => buildWebsiteSchema({
			name: page.props.appName,
			url: canonicalUrl.value,
			description: metaDescription.value,
			searchUrlTemplate: propertySearchUrl.value
		}));
		const websiteJsonLd = computed(() => {
			const schema = websiteSchema.value;
			return schema ? JSON.stringify(schema) : "";
		});
		function trans(key) {
			return page.props.translations[key] || key;
		}
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
						_push(ssrRenderComponent(_sfc_main$11, {
							"head-key": "jsonld-organization",
							content: organizationJsonLd.value
						}, null, _parent, _scopeId));
						_push(ssrRenderComponent(_sfc_main$11, {
							"head-key": "jsonld-website",
							content: websiteJsonLd.value
						}, null, _parent, _scopeId));
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
						}, null, 8, ["content"])) : createCommentVNode("", true),
						createVNode(_sfc_main$11, {
							"head-key": "jsonld-organization",
							content: organizationJsonLd.value
						}, null, 8, ["content"]),
						createVNode(_sfc_main$11, {
							"head-key": "jsonld-website",
							content: websiteJsonLd.value
						}, null, 8, ["content"])
					];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(_sfc_main$10, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(ssrRenderComponent(HomeHero_default, {
							"welcome-title": __props.welcomeTitle,
							"welcome-subtitle": __props.welcomeSubtitle,
							slides: __props.slides,
							"property-types": __props.propertyTypes,
							cities: __props.cities,
							districts: __props.districts,
							areas: __props.areas
						}, null, _parent, _scopeId));
						_push(ssrRenderComponent(FeaturedPropertiesSection_default, {
							properties: __props.featuredProperties,
							title: trans("properties.featured_properties"),
							subtitle: trans("properties.we_provide_full_service_at_every_step")
						}, null, _parent, _scopeId));
						_push(ssrRenderComponent(TurkishCitizenshipOverview_default, null, null, _parent, _scopeId));
						_push(ssrRenderComponent(HomeAboutus_default, null, null, _parent, _scopeId));
						_push(ssrRenderComponent(HomeTestimonials_default, { testimonials: __props.testimonials }, null, _parent, _scopeId));
						_push(ssrRenderComponent(HomeArticlesSection_default, { articles: __props.articles }, null, _parent, _scopeId));
						_push(ssrRenderComponent(HomeServices_default, { services: __props.corporateServices }, null, _parent, _scopeId));
						_push(ssrRenderComponent(PopularPropertiesSection_default, {
							properties: __props.recommendedProperties,
							title: trans("properties.title"),
							subtitle: trans("properties.we_provide_full_service_at_every_step")
						}, null, _parent, _scopeId));
					} else return [
						createVNode(HomeHero_default, {
							"welcome-title": __props.welcomeTitle,
							"welcome-subtitle": __props.welcomeSubtitle,
							slides: __props.slides,
							"property-types": __props.propertyTypes,
							cities: __props.cities,
							districts: __props.districts,
							areas: __props.areas
						}, null, 8, [
							"welcome-title",
							"welcome-subtitle",
							"slides",
							"property-types",
							"cities",
							"districts",
							"areas"
						]),
						createVNode(FeaturedPropertiesSection_default, {
							properties: __props.featuredProperties,
							title: trans("properties.featured_properties"),
							subtitle: trans("properties.we_provide_full_service_at_every_step")
						}, null, 8, [
							"properties",
							"title",
							"subtitle"
						]),
						createVNode(TurkishCitizenshipOverview_default),
						createVNode(HomeAboutus_default),
						createVNode(HomeTestimonials_default, { testimonials: __props.testimonials }, null, 8, ["testimonials"]),
						createVNode(HomeArticlesSection_default, { articles: __props.articles }, null, 8, ["articles"]),
						createVNode(HomeServices_default, { services: __props.corporateServices }, null, 8, ["services"]),
						createVNode(PopularPropertiesSection_default, {
							properties: __props.recommendedProperties,
							title: trans("properties.title"),
							subtitle: trans("properties.we_provide_full_service_at_every_step")
						}, null, 8, [
							"properties",
							"title",
							"subtitle"
						])
					];
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
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/Pages/Index.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
//#endregion
export { _sfc_main as default };
