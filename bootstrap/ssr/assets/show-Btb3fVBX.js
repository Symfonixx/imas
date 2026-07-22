import { a as formatPropertyMoney, c as propertyLocationLine, d as _plugin_vue_export_helper_default, l as localizedField, o as propertyStartPrice, s as localizedLocationName, u as VideoLightbox_default } from "../ssr.js";
import { o as localizedRoute, t as _sfc_main$6 } from "./App-BI701647.js";
import { t as useScrollReveal } from "./useScrollReveal-Gyyo-c-h.js";
import { t as _sfc_main$7 } from "./InnerPageHeadingHero-BLXiwMGA.js";
import "./FeaturedPropertiesSidebar-kVCwhJ6B.js";
import { c as stripHtml, i as buildRealEstateListingSchema, l as _sfc_main$8, n as buildBreadcrumbSchema, s as filterSchemaImages } from "./structuredData-HzbggR2u.js";
import { t as PopularPropertiesSection_default } from "./PopularPropertiesSection-CCY95sSQ.js";
import { t as PropertyShowContactSidebar_default } from "./PropertyShowContactSidebar-DxgByLJq.js";
import { t as useBoundedSticky } from "./useBoundedSticky-rITTuW3v.js";
import { Head, usePage } from "@inertiajs/vue3";
import { Fragment, computed, createBlock, createCommentVNode, createVNode, mergeProps, nextTick, openBlock, ref, toDisplayString, unref, useSSRContext, watch, withCtx } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region Modules/Property/resources/assets/js/components/PropertyShowGallery.vue
var _sfc_main$5 = {
	__name: "PropertyShowGallery",
	__ssrInlineRender: true,
	props: {
		propertyId: {
			type: [Number, String],
			required: true
		},
		slides: {
			type: Array,
			default: () => []
		},
		thumbnailUrl: {
			type: String,
			default: ""
		},
		alt: {
			type: String,
			default: ""
		},
		title: {
			type: String,
			default: "Gallery"
		}
	},
	setup(__props) {
		const props = __props;
		const page = usePage();
		const activeIndex = ref(0);
		const thumbsViewportRef = ref(null);
		const thumbItemRefs = ref([]);
		const carouselId = computed(() => `listingDetailsSlider-${props.propertyId}`);
		const isRtl = computed(() => {
			const dir = String(page.props.text_direction ?? "");
			const locale = String(page.props.locale ?? "");
			return dir === "rtl" || locale === "ar";
		});
		const thumbsDir = computed(() => isRtl.value ? "rtl" : "ltr");
		const previousLabel = computed(() => page.props.translations?.["global.previous"] || "Previous");
		const nextLabel = computed(() => page.props.translations?.["global.next"] || "Next");
		const thumbsRegionLabel = computed(() => page.props.translations?.["property_show.gallery_thumbnails"] || "Gallery thumbnails");
		const images = computed(() => {
			const rows = [];
			const seen = /* @__PURE__ */ new Set();
			const thumb = props.thumbnailUrl?.trim() ?? "";
			const isPlaceholderThumb = thumb === "" || thumb.includes("/images/blank.png");
			if (thumb && !isPlaceholderThumb) {
				seen.add(thumb);
				rows.push({
					key: "thumbnail",
					url: thumb,
					alt: props.alt
				});
			}
			for (const slide of props.slides ?? []) {
				const url = slide?.image_url;
				if (typeof url !== "string" || url === "" || seen.has(url)) continue;
				seen.add(url);
				rows.push({
					key: `slide-${slide.id ?? rows.length}`,
					url,
					alt: props.alt
				});
			}
			return rows;
		});
		function prefersReducedMotion() {
			return typeof window !== "undefined" && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
		}
		function scrollBehavior() {
			return prefersReducedMotion() ? "auto" : "smooth";
		}
		/**
		* Scroll the thumb strip using getBoundingClientRect + scrollBy so LTR and RTL
		* (including negative scrollLeft browsers) stay in sync with the active image.
		*/
		async function scrollActiveThumbIntoView() {
			await nextTick();
			const viewport = thumbsViewportRef.value;
			if (!viewport) return;
			const thumbEl = thumbItemRefs.value[activeIndex.value];
			if (!thumbEl) return;
			const behavior = scrollBehavior();
			const vpRect = viewport.getBoundingClientRect();
			const thumbRect = thumbEl.getBoundingClientRect();
			const pad = 2;
			if (activeIndex.value === 0) {
				const delta = isRtl.value ? thumbRect.right - vpRect.right : thumbRect.left - vpRect.left;
				if (Math.abs(delta) > pad) viewport.scrollBy({
					left: delta,
					behavior
				});
				return;
			}
			if (thumbRect.left < vpRect.left - pad) {
				viewport.scrollBy({
					left: thumbRect.left - vpRect.left,
					behavior
				});
				return;
			}
			if (thumbRect.right > vpRect.right + pad) viewport.scrollBy({
				left: thumbRect.right - vpRect.right,
				behavior
			});
		}
		watch(() => images.value.map((image) => image.key).join("|"), () => {
			thumbItemRefs.value = [];
			const length = images.value.length;
			if (length === 0) {
				activeIndex.value = 0;
				return;
			}
			if (activeIndex.value >= length) activeIndex.value = length - 1;
		});
		watch(activeIndex, () => {
			scrollActiveThumbIntoView();
		});
		return (_ctx, _push, _parent, _attrs) => {
			if (images.value.length > 0) {
				_push(`<div${ssrRenderAttrs(mergeProps({
					id: carouselId.value,
					class: "carousel listing-details-sliders slide mb-30 imas-property-gallery"
				}, _attrs))} data-v-2daad186><h5 class="imas-section-title mb-4" data-v-2daad186>${ssrInterpolate(__props.title)}</h5><div class="imas-gallery-main" data-v-2daad186><div class="carousel-inner imas-gallery-main__inner" data-v-2daad186><!--[-->`);
				ssrRenderList(images.value, (image, index) => {
					_push(`<div class="${ssrRenderClass([{ active: index === activeIndex.value }, "item carousel-item imas-gallery-slide"])}"${ssrRenderAttr("data-slide-number", index)} data-v-2daad186><div class="imas-gallery-main__frame" data-v-2daad186><img${ssrRenderAttr("src", image.url)} class="imas-gallery-main__img"${ssrRenderAttr("alt", image.alt)} loading="lazy" data-v-2daad186></div></div>`);
				});
				_push(`<!--]--></div>`);
				if (images.value.length > 1) _push(`<div class="imas-gallery-counter" aria-live="polite" data-v-2daad186><i class="fa fa-image imas-gallery-counter__icon" aria-hidden="true" data-v-2daad186></i><span class="imas-gallery-counter__text" data-v-2daad186>${ssrInterpolate(activeIndex.value + 1)} / ${ssrInterpolate(images.value.length)}</span></div>`);
				else _push(`<!---->`);
				if (images.value.length > 1) _push(`<!--[--><a class="carousel-control left imas-gallery-control" href="#" role="button"${ssrRenderAttr("aria-label", previousLabel.value)} data-v-2daad186><i class="fa fa-angle-left" aria-hidden="true" data-v-2daad186></i></a><a class="carousel-control right imas-gallery-control" href="#" role="button"${ssrRenderAttr("aria-label", nextLabel.value)} data-v-2daad186><i class="fa fa-angle-right" aria-hidden="true" data-v-2daad186></i></a><!--]-->`);
				else _push(`<!---->`);
				_push(`</div>`);
				if (images.value.length > 1) {
					_push(`<div class="imas-gallery-thumbs-outer"${ssrRenderAttr("dir", thumbsDir.value)} role="region" tabindex="0"${ssrRenderAttr("aria-label", thumbsRegionLabel.value)} data-v-2daad186><ul class="carousel-indicators smail-listing list-inline imas-gallery-thumbs" data-v-2daad186><!--[-->`);
					ssrRenderList(images.value, (image, index) => {
						_push(`<li class="${ssrRenderClass([{ active: index === activeIndex.value }, "list-inline-item imas-gallery-thumbs__item"])}" data-v-2daad186><a href="#" class="${ssrRenderClass({ selected: index === activeIndex.value })}"${ssrRenderAttr("aria-label", `Image ${index + 1}`)}${ssrRenderAttr("aria-current", index === activeIndex.value ? "true" : void 0)} data-v-2daad186><span class="imas-gallery-thumb__frame" data-v-2daad186><img${ssrRenderAttr("src", image.url)} class="imas-gallery-thumb__img"${ssrRenderAttr("alt", image.alt)} loading="lazy" data-v-2daad186></span></a></li>`);
					});
					_push(`<!--]--></ul></div>`);
				} else _push(`<!---->`);
				_push(`</div>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyShowGallery.vue");
	return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
var PropertyShowGallery_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$5, [["__scopeId", "data-v-2daad186"]]);
//#endregion
//#region Modules/Property/resources/assets/js/components/PropertyShowVideo.vue
var _sfc_main$4 = {
	__name: "PropertyShowVideo",
	__ssrInlineRender: true,
	props: {
		videoUrl: {
			type: String,
			required: true
		},
		posterUrl: {
			type: String,
			required: true
		},
		posterAlt: {
			type: String,
			default: ""
		},
		title: {
			type: String,
			required: true
		},
		invalidMessage: {
			type: String,
			default: ""
		}
	},
	setup(__props) {
		const props = __props;
		const page = usePage();
		const lightboxOpen = ref(false);
		const playLabel = computed(() => page.props.translations?.["property_show.play_video"] || "Play property video");
		const invalidMessageText = computed(() => props.invalidMessage || page.props.translations?.["property_show.video_unavailable"] || "Video is not available.");
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "property wprt-image-video w50 pro imas-property-video imas-property-show-panel mb-30" }, _attrs))} data-v-620fcb77><h5 class="imas-section-title mb-4" data-v-620fcb77>${ssrInterpolate(__props.title)}</h5><div class="imas-property-video__stage" data-v-620fcb77><img${ssrRenderAttr("src", __props.posterUrl)}${ssrRenderAttr("alt", __props.posterAlt)} class="imas-property-video__poster" data-v-620fcb77><button type="button" class="imas-property-video__play"${ssrRenderAttr("aria-label", playLabel.value)} data-v-620fcb77><span class="imas-property-video__play-btn" aria-hidden="true" data-v-620fcb77><i class="fa fa-play" data-v-620fcb77></i></span><span class="imas-property-video__ripple" aria-hidden="true" data-v-620fcb77><span class="imas-property-video__ripple-ring" data-v-620fcb77></span><span class="imas-property-video__ripple-ring" data-v-620fcb77></span><span class="imas-property-video__ripple-ring" data-v-620fcb77></span></span></button></div>`);
			_push(ssrRenderComponent(VideoLightbox_default, {
				modelValue: lightboxOpen.value,
				"onUpdate:modelValue": ($event) => lightboxOpen.value = $event,
				"video-url": __props.videoUrl,
				"aria-label": __props.title,
				"invalid-message": invalidMessageText.value
			}, null, _parent));
			_push(`</div>`);
		};
	}
};
var _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyShowVideo.vue");
	return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
var PropertyShowVideo_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$4, [["__scopeId", "data-v-620fcb77"]]);
//#endregion
//#region Modules/Property/resources/assets/js/components/PropertyShowUnitTypesTable.vue
var _sfc_main$3 = {
	__name: "PropertyShowUnitTypesTable",
	__ssrInlineRender: true,
	props: {
		unitTypes: {
			type: Array,
			default: () => []
		},
		title: {
			type: String,
			required: true
		},
		colRooms: {
			type: String,
			required: true
		},
		colArea: {
			type: String,
			required: true
		},
		colPrice: {
			type: String,
			required: true
		},
		projectId: {
			type: String,
			default: ""
		},
		projectIdLabel: {
			type: String,
			default: ""
		},
		projectLocation: {
			type: String,
			default: ""
		},
		projectLocationLabel: {
			type: String,
			default: ""
		},
		propertyType: {
			type: String,
			default: ""
		},
		propertyTypeLabel: {
			type: String,
			default: ""
		}
	},
	setup(__props) {
		const page = usePage();
		function locale() {
			return page.props.locale || "en";
		}
		function formatArea(row) {
			const min = row?.min_area;
			const max = row?.max_area;
			const minN = Number(min);
			const maxN = Number(max);
			if (Number.isFinite(minN) && Number.isFinite(maxN) && minN !== maxN) return `${formatNumber(minN)} – ${formatNumber(maxN)} m²`;
			if (Number.isFinite(minN)) return `${formatNumber(minN)} m²`;
			if (Number.isFinite(maxN)) return `${formatNumber(maxN)} m²`;
			return "—";
		}
		function formatNumber(value) {
			return new Intl.NumberFormat(locale(), { maximumFractionDigits: 0 }).format(value);
		}
		function formatPrice(amount) {
			return formatPropertyMoney(amount, locale());
		}
		return (_ctx, _push, _parent, _attrs) => {
			if (__props.unitTypes.length > 0) {
				_push(`<div${ssrRenderAttrs(mergeProps({ class: "imas-unit-types-table mb-30" }, _attrs))} data-v-82d46c5b><h5 class="imas-section-title mb-4" data-v-82d46c5b>${ssrInterpolate(__props.title)}</h5>`);
				if (__props.projectId || __props.projectLocation) {
					_push(`<dl class="imas-unit-types-table__meta mb-4" data-v-82d46c5b>`);
					if (__props.projectId) _push(`<div class="imas-unit-types-table__meta-item" data-v-82d46c5b><dt class="imas-unit-types-table__meta-label" data-v-82d46c5b>${ssrInterpolate(__props.projectIdLabel)}</dt><dd class="imas-unit-types-table__meta-value" data-v-82d46c5b>${ssrInterpolate(__props.projectId)}</dd></div>`);
					else _push(`<!---->`);
					if (__props.projectLocation) _push(`<div class="imas-unit-types-table__meta-item" data-v-82d46c5b><dt class="imas-unit-types-table__meta-label" data-v-82d46c5b>${ssrInterpolate(__props.projectLocationLabel)}</dt><dd class="imas-unit-types-table__meta-value" data-v-82d46c5b>${ssrInterpolate(__props.projectLocation)}</dd></div>`);
					else _push(`<!---->`);
					if (__props.propertyType) _push(`<div class="imas-unit-types-table__meta-item" data-v-82d46c5b><dt class="imas-unit-types-table__meta-label" data-v-82d46c5b>${ssrInterpolate(__props.propertyTypeLabel)}</dt><dd class="imas-unit-types-table__meta-value" data-v-82d46c5b>${ssrInterpolate(__props.propertyType)}</dd></div>`);
					else _push(`<!---->`);
					_push(`</dl>`);
				} else _push(`<!---->`);
				_push(`<div class="table-responsive" data-v-82d46c5b><table class="table imas-unit-types-table__grid mb-0" data-v-82d46c5b><thead data-v-82d46c5b><tr data-v-82d46c5b><th scope="col" data-v-82d46c5b>${ssrInterpolate(__props.colRooms)}</th><th scope="col" data-v-82d46c5b>${ssrInterpolate(__props.colArea)}</th><th scope="col" data-v-82d46c5b>${ssrInterpolate(__props.colPrice)}</th></tr></thead><tbody data-v-82d46c5b><!--[-->`);
				ssrRenderList(__props.unitTypes, (row) => {
					_push(`<tr data-v-82d46c5b><td data-v-82d46c5b>${ssrInterpolate(row.name || "—")}</td><td data-v-82d46c5b>${ssrInterpolate(formatArea(row))}</td><td data-v-82d46c5b>${ssrInterpolate(formatPrice(row.price))}</td></tr>`);
				});
				_push(`<!--]--></tbody></table></div></div>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyShowUnitTypesTable.vue");
	return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
var PropertyShowUnitTypesTable_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$3, [["__scopeId", "data-v-82d46c5b"]]);
//#endregion
//#region Modules/Property/resources/assets/js/components/PropertyShowMap.vue
var _sfc_main$2 = {
	__name: "PropertyShowMap",
	__ssrInlineRender: true,
	props: {
		lat: {
			type: [Number, String],
			default: null
		},
		lng: {
			type: [Number, String],
			default: null
		},
		title: {
			type: String,
			default: "Location"
		},
		unavailableText: {
			type: String,
			default: ""
		}
	},
	setup(__props) {
		const props = __props;
		const embedUrl = computed(() => {
			const lat = Number(props.lat);
			const lng = Number(props.lng);
			if (!Number.isFinite(lat) || !Number.isFinite(lng)) return "";
			const pad = .02;
			const bbox = [
				lng - pad,
				lat - pad,
				lng + pad,
				lat + pad
			].join(",");
			return `https://www.openstreetmap.org/export/embed.html?bbox=${encodeURIComponent(bbox)}&layer=mapnik&marker=${lat}%2C${lng}`;
		});
		return (_ctx, _push, _parent, _attrs) => {
			if (embedUrl.value) _push(`<div${ssrRenderAttrs(mergeProps({ class: "property-location map imas-property-map mb-30" }, _attrs))} data-v-68066e45><h5 class="imas-section-title" data-v-68066e45>${ssrInterpolate(__props.title)}</h5><div class="divider-fade" data-v-68066e45></div><div class="contact-map imas-property-map__frame" data-v-68066e45><iframe${ssrRenderAttr("src", embedUrl.value)}${ssrRenderAttr("title", __props.title)} loading="lazy" referrerpolicy="no-referrer-when-downgrade" data-v-68066e45></iframe></div></div>`);
			else if (__props.unavailableText) _push(`<p${ssrRenderAttrs(mergeProps({ class: "text-muted imas-property-map-unavailable mb-30" }, _attrs))} data-v-68066e45>${ssrInterpolate(__props.unavailableText)}</p>`);
			else _push(`<!---->`);
		};
	}
};
var _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyShowMap.vue");
	return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
var PropertyShowMap_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$2, [["__scopeId", "data-v-68066e45"]]);
//#endregion
//#region Modules/Property/resources/assets/js/components/RecentPropertiesSidebar.vue
var _sfc_main$1 = {
	__name: "RecentPropertiesSidebar",
	__ssrInlineRender: true,
	props: { recentProperties: {
		type: Array,
		default: () => []
	} },
	setup(__props) {
		const page = usePage();
		function trans(key) {
			return page.props.translations[key] || key;
		}
		function locale() {
			return page.props.locale || "en";
		}
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
		return (_ctx, _push, _parent, _attrs) => {
			if (__props.recentProperties.length > 0) {
				_push(`<div${ssrRenderAttrs(mergeProps({ class: "imas-blog-v2-sidebar__box" }, _attrs))}><h4 class="imas-blog-v2-sidebar__heading text-start">${ssrInterpolate(trans("listing_page.recent_properties"))}</h4><div class="imas-blog-v2-sidebar__recent"><!--[-->`);
				ssrRenderList(__props.recentProperties, (p) => {
					_push(`<a${ssrRenderAttr("href", p.url)} class="imas-blog-v2-sidebar__recent-item"><img${ssrRenderAttr("src", p.thumbnail_url)}${ssrRenderAttr("alt", displayTitle(p))} loading="lazy"><div><div class="imas-blog-v2-sidebar__recent-title">${ssrInterpolate(displayTitle(p))}</div><div class="imas-blog-v2-sidebar__recent-date text-dim text-start">${ssrInterpolate(formatMoney(unref(propertyStartPrice)(p)))}</div></div></a>`);
				});
				_push(`<!--]--></div></div>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/RecentPropertiesSidebar.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
//#endregion
//#region Modules/Property/resources/assets/js/Pages/show.vue
var _sfc_main = {
	__name: "show",
	__ssrInlineRender: true,
	props: {
		property: {
			type: Object,
			required: true
		},
		recentProperties: {
			type: Array,
			default: () => []
		},
		featuredProperties: {
			type: Array,
			default: () => []
		},
		similarProperties: {
			type: Array,
			default: () => []
		},
		contactStoreUrl: {
			type: String,
			required: true
		}
	},
	setup(__props) {
		const props = __props;
		const page = usePage();
		const locale = computed(() => page.props.locale || "en");
		const activeLocale = locale;
		const globals = computed(() => page.props.globals ?? {});
		const media = computed(() => globals.value.media ?? {});
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const displayTitle = computed(() => {
			const fromTitle = localizedField(props.property.title, locale.value);
			if (fromTitle) return fromTitle;
			const fromProject = localizedField(props.property.project_name, locale.value);
			if (fromProject) return fromProject;
			return props.property.project_code || "Property";
		});
		const propertyHeadingItems = computed(() => {
			const rows = [];
			try {
				if (typeof route === "function" && route().has?.("home")) rows.push({
					title: trans("navBar.Home"),
					href: localizedRoute("home", {}, activeLocale.value, "/")
				});
				if (typeof route === "function" && route().has?.("property.index")) rows.push({
					title: trans("navBar.Buy Real Estate"),
					href: localizedRoute("property.index", {}, activeLocale.value, "/property")
				});
			} catch {}
			rows.push({
				title: displayTitle.value,
				href: null
			});
			return rows;
		});
		const propertyShowBannerUrl = computed(() => {
			const url = media.value.property_show_banner;
			if (typeof url !== "string" || url.trim() === "") return "";
			const trimmed = url.trim();
			if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) return "";
			return trimmed;
		});
		const addressLine = computed(() => propertyLocationLine(props.property.location, locale.value));
		const propertyTypeLabel = computed(() => {
			const type = props.property.property_type;
			if (!type) return "";
			return localizedField(type.name, locale.value);
		});
		function formatMoney(amount) {
			return formatPropertyMoney(amount, locale.value);
		}
		const pricePrefix = computed(() => trans("properties.start_price"));
		const priceAmount = computed(() => {
			const start = propertyStartPrice(props.property);
			if (start == null) return null;
			return formatMoney(start);
		});
		const overviewHtml = computed(() => localizedField(props.property.overview, locale.value));
		const contentHtml = computed(() => localizedField(props.property.content, locale.value));
		const whyToBuyHtml = computed(() => localizedField(props.property.why_to_buy, locale.value));
		const facilitiesHtml = computed(() => localizedField(props.property.facilities, locale.value));
		function hasValidCoordinate(value) {
			if (value === null || value === void 0 || value === "") return false;
			return Number.isFinite(Number(value));
		}
		const hasMapCoordinates = computed(() => hasValidCoordinate(props.property.lat) && hasValidCoordinate(props.property.lng));
		const meta = computed(() => props.property.metadata ?? {});
		const documentTitle = computed(() => {
			const custom = meta.value.meta_title;
			return `${typeof custom === "string" && custom.trim() !== "" ? custom.trim() : displayTitle.value} | ${page.props.appName}`;
		});
		const metaDescription = computed(() => {
			const d = meta.value.meta_description;
			if (typeof d === "string" && d.trim() !== "") return d.trim();
			return overviewHtml.value.replace(/<[^>]*>/g, " ").trim().slice(0, 160);
		});
		const metaKeywords = computed(() => {
			const k = meta.value.meta_keywords;
			if (Array.isArray(k) && k.length > 0) return k.join(", ");
			if (typeof k === "string" && k.trim() !== "") return k.trim();
			return "";
		});
		const ogTitle = computed(() => documentTitle.value);
		const ogDescription = computed(() => metaDescription.value);
		const ogImage = computed(() => {
			const candidates = filterSchemaImages([props.property.thumbnail_url, media.value.meta_img]);
			return candidates.length > 0 ? candidates[0].trim() : "";
		});
		const canonicalUrl = computed(() => {
			try {
				if (typeof route === "function" && route().has?.("property.show")) {
					const slug = props.property.project_code || props.property.slug;
					if (slug) return route("property.show", slug);
				}
			} catch {}
			return "";
		});
		const ogUrl = computed(() => canonicalUrl.value);
		const twitterCard = computed(() => ogImage.value ? "summary_large_image" : "summary");
		const realEstateListingSchema = computed(() => {
			const loc = props.property.location;
			const slideImages = (props.property.slides ?? []).map((slide) => slide?.image_url).filter(Boolean);
			return buildRealEstateListingSchema({
				name: displayTitle.value,
				description: metaDescription.value || stripHtml(overviewHtml.value),
				url: canonicalUrl.value,
				images: [props.property.thumbnail_url, ...slideImages],
				datePosted: props.property.created_at,
				dateModified: props.property.updated_at,
				price: propertyStartPrice(props.property),
				isSoldOut: Boolean(props.property.is_sold_out),
				addressLocality: localizedLocationName(loc?.area?.name, locale.value) || localizedLocationName(loc?.district?.name, locale.value),
				addressRegion: localizedLocationName(loc?.city?.name, locale.value),
				latitude: props.property.lat,
				longitude: props.property.lng,
				minArea: props.property.min_area,
				maxArea: props.property.max_area,
				propertyType: propertyTypeLabel.value
			});
		});
		const realEstateListingJsonLd = computed(() => {
			const schema = realEstateListingSchema.value;
			return schema ? JSON.stringify(schema) : "";
		});
		const breadcrumbJsonLd = computed(() => {
			const schema = buildBreadcrumbSchema(propertyHeadingItems.value.map((item, index, arr) => ({
				name: item.title,
				url: index === arr.length - 1 ? canonicalUrl.value : item.href || void 0
			})));
			return schema ? JSON.stringify(schema) : "";
		});
		/**
		* Admin-provided custom JSON-LD (Property → SEO → schema). Rendered only when it
		* is valid JSON so a malformed textarea value never breaks the page head.
		*/
		const customSchemaJsonLd = computed(() => {
			const raw = meta.value.schema;
			if (raw && typeof raw === "object") try {
				return JSON.stringify(raw);
			} catch {
				return "";
			}
			if (typeof raw === "string" && raw.trim() !== "") try {
				return JSON.stringify(JSON.parse(raw));
			} catch {
				return "";
			}
			return "";
		});
		const pageRef = ref(null);
		const propertyContentRowRef = ref(null);
		const propertySidebarColRef = ref(null);
		const propertySidebarStickyRef = ref(null);
		useScrollReveal(pageRef, { variant: "propertyListings" });
		useBoundedSticky({
			boundaryRef: propertyContentRowRef,
			columnRef: propertySidebarColRef,
			targetRef: propertySidebarStickyRef
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: documentTitle.value }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (metaDescription.value) _push(`<meta head-key="description" name="description"${ssrRenderAttr("content", metaDescription.value)} data-v-771b0d9d${_scopeId}>`);
						else _push(`<!---->`);
						if (metaKeywords.value) _push(`<meta head-key="keywords" name="keywords"${ssrRenderAttr("content", metaKeywords.value)} data-v-771b0d9d${_scopeId}>`);
						else _push(`<!---->`);
						if (canonicalUrl.value) _push(`<link head-key="canonical" rel="canonical"${ssrRenderAttr("href", canonicalUrl.value)} data-v-771b0d9d${_scopeId}>`);
						else _push(`<!---->`);
						if (ogTitle.value) _push(`<meta head-key="og:title" property="og:title"${ssrRenderAttr("content", ogTitle.value)} data-v-771b0d9d${_scopeId}>`);
						else _push(`<!---->`);
						if (ogDescription.value) _push(`<meta head-key="og:description" property="og:description"${ssrRenderAttr("content", ogDescription.value)} data-v-771b0d9d${_scopeId}>`);
						else _push(`<!---->`);
						if (ogImage.value) _push(`<meta head-key="og:image" property="og:image"${ssrRenderAttr("content", ogImage.value)} data-v-771b0d9d${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="og:type" property="og:type" content="website" data-v-771b0d9d${_scopeId}>`);
						if (ogUrl.value) _push(`<meta head-key="og:url" property="og:url"${ssrRenderAttr("content", ogUrl.value)} data-v-771b0d9d${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="twitter:card" name="twitter:card"${ssrRenderAttr("content", twitterCard.value)} data-v-771b0d9d${_scopeId}>`);
						if (ogTitle.value) _push(`<meta head-key="twitter:title" name="twitter:title"${ssrRenderAttr("content", ogTitle.value)} data-v-771b0d9d${_scopeId}>`);
						else _push(`<!---->`);
						if (ogDescription.value) _push(`<meta head-key="twitter:description" name="twitter:description"${ssrRenderAttr("content", ogDescription.value)} data-v-771b0d9d${_scopeId}>`);
						else _push(`<!---->`);
						if (ogImage.value) _push(`<meta head-key="twitter:image" name="twitter:image"${ssrRenderAttr("content", ogImage.value)} data-v-771b0d9d${_scopeId}>`);
						else _push(`<!---->`);
						_push(ssrRenderComponent(_sfc_main$8, {
							"head-key": "jsonld-real-estate-listing",
							content: realEstateListingJsonLd.value
						}, null, _parent, _scopeId));
						_push(ssrRenderComponent(_sfc_main$8, {
							"head-key": "jsonld-breadcrumb",
							content: breadcrumbJsonLd.value
						}, null, _parent, _scopeId));
						_push(ssrRenderComponent(_sfc_main$8, {
							"head-key": "jsonld-custom",
							content: customSchemaJsonLd.value
						}, null, _parent, _scopeId));
					} else return [
						metaDescription.value ? (openBlock(), createBlock("meta", {
							key: 0,
							"head-key": "description",
							name: "description",
							content: metaDescription.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						metaKeywords.value ? (openBlock(), createBlock("meta", {
							key: 1,
							"head-key": "keywords",
							name: "keywords",
							content: metaKeywords.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						canonicalUrl.value ? (openBlock(), createBlock("link", {
							key: 2,
							"head-key": "canonical",
							rel: "canonical",
							href: canonicalUrl.value
						}, null, 8, ["href"])) : createCommentVNode("", true),
						ogTitle.value ? (openBlock(), createBlock("meta", {
							key: 3,
							"head-key": "og:title",
							property: "og:title",
							content: ogTitle.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						ogDescription.value ? (openBlock(), createBlock("meta", {
							key: 4,
							"head-key": "og:description",
							property: "og:description",
							content: ogDescription.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						ogImage.value ? (openBlock(), createBlock("meta", {
							key: 5,
							"head-key": "og:image",
							property: "og:image",
							content: ogImage.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						createVNode("meta", {
							"head-key": "og:type",
							property: "og:type",
							content: "website"
						}),
						ogUrl.value ? (openBlock(), createBlock("meta", {
							key: 6,
							"head-key": "og:url",
							property: "og:url",
							content: ogUrl.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						createVNode("meta", {
							"head-key": "twitter:card",
							name: "twitter:card",
							content: twitterCard.value
						}, null, 8, ["content"]),
						ogTitle.value ? (openBlock(), createBlock("meta", {
							key: 7,
							"head-key": "twitter:title",
							name: "twitter:title",
							content: ogTitle.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						ogDescription.value ? (openBlock(), createBlock("meta", {
							key: 8,
							"head-key": "twitter:description",
							name: "twitter:description",
							content: ogDescription.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						ogImage.value ? (openBlock(), createBlock("meta", {
							key: 9,
							"head-key": "twitter:image",
							name: "twitter:image",
							content: ogImage.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						createVNode(_sfc_main$8, {
							"head-key": "jsonld-real-estate-listing",
							content: realEstateListingJsonLd.value
						}, null, 8, ["content"]),
						createVNode(_sfc_main$8, {
							"head-key": "jsonld-breadcrumb",
							content: breadcrumbJsonLd.value
						}, null, 8, ["content"]),
						createVNode(_sfc_main$8, {
							"head-key": "jsonld-custom",
							content: customSchemaJsonLd.value
						}, null, 8, ["content"])
					];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(_sfc_main$6, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="inner-pages blog imas-property-show-page imas-blog-v2 imas-property-listings" data-v-771b0d9d${_scopeId}>`);
						_push(ssrRenderComponent(_sfc_main$7, {
							"page-title": trans("properties.proprty_details"),
							items: propertyHeadingItems.value,
							"banner-image-url": propertyShowBannerUrl.value
						}, null, _parent, _scopeId));
						_push(`<section class="single-proper blog details imas-property-show" data-v-771b0d9d${_scopeId}><div class="container" data-v-771b0d9d${_scopeId}><div class="row imas-property-show__content-row" data-v-771b0d9d${_scopeId}><div class="col-lg-8 col-md-12 blog-pots" data-v-771b0d9d${_scopeId}><div class="row" data-v-771b0d9d${_scopeId}><div class="col-md-12" data-v-771b0d9d${_scopeId}><section data-imas-reveal class="headings-2 pt-0" data-v-771b0d9d${_scopeId}><div class="pro-wrapper imas-property-title-row" data-v-771b0d9d${_scopeId}><div class="detail-wrapper-body" data-v-771b0d9d${_scopeId}><div class="listing-title-bar text-start" data-v-771b0d9d${_scopeId}>`);
						if (__props.property.project_code) _push(`<div class="mt-0" data-v-771b0d9d${_scopeId}><span class="listing-address" data-v-771b0d9d${_scopeId}>${ssrInterpolate(trans("property_show.project_id"))}: ${ssrInterpolate(__props.property.project_code)}</span></div>`);
						else _push(`<!---->`);
						_push(`<h3 data-v-771b0d9d${_scopeId}>${ssrInterpolate(displayTitle.value)}</h3>`);
						if (addressLine.value) {
							_push(`<div class="mt-0" data-v-771b0d9d${_scopeId}>`);
							if (hasMapCoordinates.value) _push(`<a href="#listing-location" class="listing-address" data-v-771b0d9d${_scopeId}><i class="fa fa-map-marker imas-address-marker" aria-hidden="true" data-v-771b0d9d${_scopeId}></i><span data-v-771b0d9d${_scopeId}>${ssrInterpolate(addressLine.value)}</span></a>`);
							else _push(`<span class="listing-address" data-v-771b0d9d${_scopeId}><i class="fa fa-map-marker imas-address-marker" aria-hidden="true" data-v-771b0d9d${_scopeId}></i><span data-v-771b0d9d${_scopeId}>${ssrInterpolate(addressLine.value)}</span></span>`);
							_push(`</div>`);
						} else _push(`<!---->`);
						if (propertyTypeLabel.value) _push(`<div class="imas-property-type-badge mt-2" data-v-771b0d9d${_scopeId}>${ssrInterpolate(propertyTypeLabel.value)}</div>`);
						else _push(`<!---->`);
						_push(`</div></div><div class="single detail-wrapper ms-lg-auto" data-v-771b0d9d${_scopeId}><div class="detail-wrapper-body" data-v-771b0d9d${_scopeId}><div class="listing-title-bar text-start text-lg-end" data-v-771b0d9d${_scopeId}><h4 class="imas-price-heading" data-v-771b0d9d${_scopeId}>`);
						if (priceAmount.value) _push(`<!--[--><span class="imas-price-heading__prefix" data-v-771b0d9d${_scopeId}>${ssrInterpolate(pricePrefix.value)}</span><span class="imas-price-heading__amount text-gold" data-v-771b0d9d${_scopeId}>${ssrInterpolate(priceAmount.value)}</span><!--]-->`);
						else _push(`<span class="imas-price-heading__amount text-gold" data-v-771b0d9d${_scopeId}>—</span>`);
						_push(`</h4></div></div></div></div></section><div data-imas-reveal data-v-771b0d9d${_scopeId}>`);
						_push(ssrRenderComponent(PropertyShowGallery_default, {
							"property-id": __props.property.id,
							slides: __props.property.slides,
							"thumbnail-url": __props.property.thumbnail_url,
							alt: displayTitle.value,
							title: trans("property_show.gallery")
						}, null, _parent, _scopeId));
						_push(`</div>`);
						if (overviewHtml.value) _push(`<div data-imas-reveal class="blog-info details mb-30 text-start imas-property-show-panel" data-v-771b0d9d${_scopeId}><h5 class="imas-section-title mb-4" data-v-771b0d9d${_scopeId}>${ssrInterpolate(trans("property_show.description"))}</h5><div class="imas-rich-content text-md" data-v-771b0d9d${_scopeId}>${overviewHtml.value ?? ""}</div></div>`);
						else _push(`<!---->`);
						if (__props.property.unit_types?.length) {
							_push(`<div data-imas-reveal data-v-771b0d9d${_scopeId}>`);
							_push(ssrRenderComponent(PropertyShowUnitTypesTable_default, {
								"unit-types": __props.property.unit_types,
								"property-type": propertyTypeLabel.value,
								"property-type-label": trans("Property type"),
								title: trans("property_show.unit_types_title"),
								"project-id": __props.property.project_code,
								"project-id-label": trans("property_show.project_id"),
								"project-location": addressLine.value,
								"project-location-label": trans("property_show.project_location"),
								"col-rooms": trans("property_show.col_rooms"),
								"col-area": trans("property_show.col_area"),
								"col-price": trans("property_show.col_price")
							}, null, _parent, _scopeId));
							_push(`</div>`);
						} else _push(`<!---->`);
						if (whyToBuyHtml.value) _push(`<div data-imas-reveal class="blog-info details mb-30 text-start imas-property-show-panel" data-v-771b0d9d${_scopeId}><h5 class="imas-section-title mb-4" data-v-771b0d9d${_scopeId}>${ssrInterpolate(trans("property_show.why_to_buy"))}</h5><div class="imas-rich-content text-md" data-v-771b0d9d${_scopeId}>${whyToBuyHtml.value ?? ""}</div></div>`);
						else _push(`<!---->`);
						if (facilitiesHtml.value) _push(`<div data-imas-reveal class="blog-info details mb-30 text-start imas-property-show-panel" data-v-771b0d9d${_scopeId}><h5 class="imas-section-title mb-4" data-v-771b0d9d${_scopeId}>${ssrInterpolate(trans("property_show.facilities"))}</h5><div class="imas-rich-content text-md" data-v-771b0d9d${_scopeId}>${facilitiesHtml.value ?? ""}</div></div>`);
						else _push(`<!---->`);
						_push(`</div></div>`);
						if (contentHtml.value) _push(`<div data-imas-reveal class="blog-info details mb-30 text-start imas-property-show-panel" data-v-771b0d9d${_scopeId}><h5 class="imas-section-title mb-4" data-v-771b0d9d${_scopeId}>${ssrInterpolate(trans("property_show.details"))}</h5><div class="imas-rich-content text-md" data-v-771b0d9d${_scopeId}>${contentHtml.value ?? ""}</div></div>`);
						else _push(`<!---->`);
						if (__props.property.youtube_video_url) {
							_push(`<div data-imas-reveal data-v-771b0d9d${_scopeId}>`);
							_push(ssrRenderComponent(PropertyShowVideo_default, {
								"video-url": __props.property.youtube_video_url,
								"poster-url": __props.property.thumbnail_url,
								"poster-alt": displayTitle.value,
								title: trans("property_show.property_video")
							}, null, _parent, _scopeId));
							_push(`</div>`);
						} else _push(`<!---->`);
						if (hasMapCoordinates.value) {
							_push(`<div id="listing-location" data-imas-reveal data-v-771b0d9d${_scopeId}>`);
							_push(ssrRenderComponent(PropertyShowMap_default, {
								lat: __props.property.lat,
								lng: __props.property.lng,
								title: trans("property_show.location")
							}, null, _parent, _scopeId));
							_push(`</div>`);
						} else _push(`<!---->`);
						_push(`</div><aside class="col-lg-4 col-md-12 car imas-blog-v2-sidebar imas-property-show__sidebar-col" data-v-771b0d9d${_scopeId}><div class="imas-property-show__contact-sticky" data-imas-reveal="aside" data-v-771b0d9d${_scopeId}>`);
						_push(ssrRenderComponent(PropertyShowContactSidebar_default, {
							"contact-store-url": __props.contactStoreUrl,
							"default-subject": canonicalUrl.value,
							"source-page": displayTitle.value,
							"default-message": trans("property_show.default_inquiry_message"),
							"hide-form-subject": "",
							"property-id": __props.property.id,
							"is-favorited": __props.property.is_favorited,
							"is-sold-out": __props.property.is_sold_out
						}, null, _parent, _scopeId));
						_push(`</div></aside></div></div></section>`);
						if (__props.similarProperties.length > 0) _push(ssrRenderComponent(PopularPropertiesSection_default, {
							properties: __props.similarProperties,
							"hide-title": true,
							"custom-title": trans("property_show.similar_properties")
						}, null, _parent, _scopeId));
						else _push(`<!---->`);
						_push(`</div>`);
					} else return [createVNode("div", {
						ref_key: "pageRef",
						ref: pageRef,
						class: "inner-pages blog imas-property-show-page imas-blog-v2 imas-property-listings"
					}, [
						createVNode(_sfc_main$7, {
							"page-title": trans("properties.proprty_details"),
							items: propertyHeadingItems.value,
							"banner-image-url": propertyShowBannerUrl.value
						}, null, 8, [
							"page-title",
							"items",
							"banner-image-url"
						]),
						createVNode("section", { class: "single-proper blog details imas-property-show" }, [createVNode("div", { class: "container" }, [createVNode("div", {
							ref_key: "propertyContentRowRef",
							ref: propertyContentRowRef,
							class: "row imas-property-show__content-row"
						}, [createVNode("div", { class: "col-lg-8 col-md-12 blog-pots" }, [
							createVNode("div", { class: "row" }, [createVNode("div", { class: "col-md-12" }, [
								createVNode("section", {
									"data-imas-reveal": "",
									class: "headings-2 pt-0"
								}, [createVNode("div", { class: "pro-wrapper imas-property-title-row" }, [createVNode("div", { class: "detail-wrapper-body" }, [createVNode("div", { class: "listing-title-bar text-start" }, [
									__props.property.project_code ? (openBlock(), createBlock("div", {
										key: 0,
										class: "mt-0"
									}, [createVNode("span", { class: "listing-address" }, toDisplayString(trans("property_show.project_id")) + ": " + toDisplayString(__props.property.project_code), 1)])) : createCommentVNode("", true),
									createVNode("h3", null, toDisplayString(displayTitle.value), 1),
									addressLine.value ? (openBlock(), createBlock("div", {
										key: 1,
										class: "mt-0"
									}, [hasMapCoordinates.value ? (openBlock(), createBlock("a", {
										key: 0,
										href: "#listing-location",
										class: "listing-address"
									}, [createVNode("i", {
										class: "fa fa-map-marker imas-address-marker",
										"aria-hidden": "true"
									}), createVNode("span", null, toDisplayString(addressLine.value), 1)])) : (openBlock(), createBlock("span", {
										key: 1,
										class: "listing-address"
									}, [createVNode("i", {
										class: "fa fa-map-marker imas-address-marker",
										"aria-hidden": "true"
									}), createVNode("span", null, toDisplayString(addressLine.value), 1)]))])) : createCommentVNode("", true),
									propertyTypeLabel.value ? (openBlock(), createBlock("div", {
										key: 2,
										class: "imas-property-type-badge mt-2"
									}, toDisplayString(propertyTypeLabel.value), 1)) : createCommentVNode("", true)
								])]), createVNode("div", { class: "single detail-wrapper ms-lg-auto" }, [createVNode("div", { class: "detail-wrapper-body" }, [createVNode("div", { class: "listing-title-bar text-start text-lg-end" }, [createVNode("h4", { class: "imas-price-heading" }, [priceAmount.value ? (openBlock(), createBlock(Fragment, { key: 0 }, [createVNode("span", { class: "imas-price-heading__prefix" }, toDisplayString(pricePrefix.value), 1), createVNode("span", { class: "imas-price-heading__amount text-gold" }, toDisplayString(priceAmount.value), 1)], 64)) : (openBlock(), createBlock("span", {
									key: 1,
									class: "imas-price-heading__amount text-gold"
								}, "—"))])])])])])]),
								createVNode("div", { "data-imas-reveal": "" }, [createVNode(PropertyShowGallery_default, {
									"property-id": __props.property.id,
									slides: __props.property.slides,
									"thumbnail-url": __props.property.thumbnail_url,
									alt: displayTitle.value,
									title: trans("property_show.gallery")
								}, null, 8, [
									"property-id",
									"slides",
									"thumbnail-url",
									"alt",
									"title"
								])]),
								overviewHtml.value ? (openBlock(), createBlock("div", {
									key: 0,
									"data-imas-reveal": "",
									class: "blog-info details mb-30 text-start imas-property-show-panel"
								}, [createVNode("h5", { class: "imas-section-title mb-4" }, toDisplayString(trans("property_show.description")), 1), createVNode("div", {
									class: "imas-rich-content text-md",
									innerHTML: overviewHtml.value
								}, null, 8, ["innerHTML"])])) : createCommentVNode("", true),
								__props.property.unit_types?.length ? (openBlock(), createBlock("div", {
									key: 1,
									"data-imas-reveal": ""
								}, [createVNode(PropertyShowUnitTypesTable_default, {
									"unit-types": __props.property.unit_types,
									"property-type": propertyTypeLabel.value,
									"property-type-label": trans("Property type"),
									title: trans("property_show.unit_types_title"),
									"project-id": __props.property.project_code,
									"project-id-label": trans("property_show.project_id"),
									"project-location": addressLine.value,
									"project-location-label": trans("property_show.project_location"),
									"col-rooms": trans("property_show.col_rooms"),
									"col-area": trans("property_show.col_area"),
									"col-price": trans("property_show.col_price")
								}, null, 8, [
									"unit-types",
									"property-type",
									"property-type-label",
									"title",
									"project-id",
									"project-id-label",
									"project-location",
									"project-location-label",
									"col-rooms",
									"col-area",
									"col-price"
								])])) : createCommentVNode("", true),
								whyToBuyHtml.value ? (openBlock(), createBlock("div", {
									key: 2,
									"data-imas-reveal": "",
									class: "blog-info details mb-30 text-start imas-property-show-panel"
								}, [createVNode("h5", { class: "imas-section-title mb-4" }, toDisplayString(trans("property_show.why_to_buy")), 1), createVNode("div", {
									class: "imas-rich-content text-md",
									innerHTML: whyToBuyHtml.value
								}, null, 8, ["innerHTML"])])) : createCommentVNode("", true),
								facilitiesHtml.value ? (openBlock(), createBlock("div", {
									key: 3,
									"data-imas-reveal": "",
									class: "blog-info details mb-30 text-start imas-property-show-panel"
								}, [createVNode("h5", { class: "imas-section-title mb-4" }, toDisplayString(trans("property_show.facilities")), 1), createVNode("div", {
									class: "imas-rich-content text-md",
									innerHTML: facilitiesHtml.value
								}, null, 8, ["innerHTML"])])) : createCommentVNode("", true)
							])]),
							contentHtml.value ? (openBlock(), createBlock("div", {
								key: 0,
								"data-imas-reveal": "",
								class: "blog-info details mb-30 text-start imas-property-show-panel"
							}, [createVNode("h5", { class: "imas-section-title mb-4" }, toDisplayString(trans("property_show.details")), 1), createVNode("div", {
								class: "imas-rich-content text-md",
								innerHTML: contentHtml.value
							}, null, 8, ["innerHTML"])])) : createCommentVNode("", true),
							__props.property.youtube_video_url ? (openBlock(), createBlock("div", {
								key: 1,
								"data-imas-reveal": ""
							}, [createVNode(PropertyShowVideo_default, {
								"video-url": __props.property.youtube_video_url,
								"poster-url": __props.property.thumbnail_url,
								"poster-alt": displayTitle.value,
								title: trans("property_show.property_video")
							}, null, 8, [
								"video-url",
								"poster-url",
								"poster-alt",
								"title"
							])])) : createCommentVNode("", true),
							hasMapCoordinates.value ? (openBlock(), createBlock("div", {
								key: 2,
								id: "listing-location",
								"data-imas-reveal": ""
							}, [createVNode(PropertyShowMap_default, {
								lat: __props.property.lat,
								lng: __props.property.lng,
								title: trans("property_show.location")
							}, null, 8, [
								"lat",
								"lng",
								"title"
							])])) : createCommentVNode("", true)
						]), createVNode("aside", {
							ref_key: "propertySidebarColRef",
							ref: propertySidebarColRef,
							class: "col-lg-4 col-md-12 car imas-blog-v2-sidebar imas-property-show__sidebar-col"
						}, [createVNode("div", {
							ref_key: "propertySidebarStickyRef",
							ref: propertySidebarStickyRef,
							class: "imas-property-show__contact-sticky",
							"data-imas-reveal": "aside"
						}, [createVNode(PropertyShowContactSidebar_default, {
							"contact-store-url": __props.contactStoreUrl,
							"default-subject": canonicalUrl.value,
							"source-page": displayTitle.value,
							"default-message": trans("property_show.default_inquiry_message"),
							"hide-form-subject": "",
							"property-id": __props.property.id,
							"is-favorited": __props.property.is_favorited,
							"is-sold-out": __props.property.is_sold_out
						}, null, 8, [
							"contact-store-url",
							"default-subject",
							"source-page",
							"default-message",
							"property-id",
							"is-favorited",
							"is-sold-out"
						])], 512)], 512)], 512)])]),
						__props.similarProperties.length > 0 ? (openBlock(), createBlock(PopularPropertiesSection_default, {
							key: 0,
							properties: __props.similarProperties,
							"hide-title": true,
							"custom-title": trans("property_show.similar_properties")
						}, null, 8, ["properties", "custom-title"])) : createCommentVNode("", true)
					], 512)];
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
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/Pages/show.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var show_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-771b0d9d"]]);
//#endregion
export { show_default as default };
