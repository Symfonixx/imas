import { u as _plugin_vue_export_helper_default } from "../ssr.js";
import { o as localizedRoute, t as _sfc_main$1 } from "./App-Tm_yWILr.js";
import { t as useScrollReveal } from "./useScrollReveal-DCquZn8b.js";
import { t as _sfc_main$2 } from "./InnerPageHeadingHero-CFmV_XXE.js";
import { n as _sfc_main$3, t as _sfc_main$4 } from "./PropertyListingPagination-BiNZbPQF.js";
import { t as PropertyShowContactSidebar_default } from "./PropertyShowContactSidebar-CSnc4Cvc.js";
import { Head, usePage } from "@inertiajs/vue3";
import { computed, createBlock, createCommentVNode, createVNode, onMounted, openBlock, ref, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { ssrInterpolate, ssrRenderComponent } from "vue/server-renderer";
//#region Modules/Property/resources/assets/js/Pages/FavoriteProperties.vue
var _sfc_main = {
	__name: "FavoriteProperties",
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
		contactStoreUrl: {
			type: String,
			required: true
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
		const documentTitle = computed(() => `${props.title} | ${page.props.appName}`);
		const inquirySubject = computed(() => props.title);
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const resultsCountLabel = computed(() => {
			const total = props.properties?.total ?? props.properties?.data?.length ?? 0;
			return trans("properties.favorite_properties_count").replace(":count", String(total));
		});
		const headingItems = computed(() => {
			const rows = [];
			try {
				if (typeof route === "function" && route().has?.("home")) rows.push({
					title: trans("navBar.Home"),
					href: localizedRoute("home", {}, activeLocale.value, "/")
				});
			} catch {}
			rows.push({
				title: trans("navBar.Buy Real Estate"),
				href: localizedRoute("property.index", {}, activeLocale.value, "/property")
			});
			rows.push({
				title: props.title,
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
			_push(ssrRenderComponent(unref(Head), { title: documentTitle.value }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<meta name="robots" head-key="robots" content="noindex, nofollow" data-v-50914387${_scopeId}>`);
					else return [createVNode("meta", {
						name: "robots",
						"head-key": "robots",
						content: "noindex, nofollow"
					})];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(_sfc_main$1, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="imas-blog-v2 imas-property-listings imas-blog-section-anchor" data-v-50914387${_scopeId}>`);
						_push(ssrRenderComponent(_sfc_main$2, {
							"page-title": __props.title,
							items: headingItems.value,
							"banner-image-url": listingsBannerUrl.value
						}, null, _parent, _scopeId));
						_push(`<main class="imas-blog-v2__page" data-v-50914387${_scopeId}><section class="imas-blog-v2__main" data-v-50914387${_scopeId}>`);
						if ((__props.properties.data ?? []).length > 0) _push(`<p class="imas-property-listings__count text-dim" data-v-50914387${_scopeId}>${ssrInterpolate(resultsCountLabel.value)}</p>`);
						else _push(`<!---->`);
						if ((__props.properties.data ?? []).length > 0) {
							_push(`<div class="imas-property-listings__grid" data-v-50914387${_scopeId}>`);
							_push(ssrRenderComponent(_sfc_main$3, { properties: __props.properties }, null, _parent, _scopeId));
							_push(`</div>`);
						} else _push(`<p class="imas-blog-v2__empty text-dim" data-v-50914387${_scopeId}>${ssrInterpolate(trans("properties.favorite_properties_empty"))}</p>`);
						_push(ssrRenderComponent(_sfc_main$4, {
							properties: __props.properties,
							onNavigate: scrollToListingsTop
						}, null, _parent, _scopeId));
						_push(`</section><aside class="imas-blog-v2-sidebar" data-v-50914387${_scopeId}><div class="imas-favorites-aside-sticky" data-v-50914387${_scopeId}>`);
						_push(ssrRenderComponent(PropertyShowContactSidebar_default, {
							"contact-store-url": __props.contactStoreUrl,
							"default-subject": inquirySubject.value,
							"source-page": inquirySubject.value
						}, null, _parent, _scopeId));
						_push(`</div></aside></main></div>`);
					} else return [createVNode("div", {
						class: "imas-blog-v2 imas-property-listings imas-blog-section-anchor",
						ref_key: "pageRef",
						ref: pageRef
					}, [createVNode(_sfc_main$2, {
						"page-title": __props.title,
						items: headingItems.value,
						"banner-image-url": listingsBannerUrl.value
					}, null, 8, [
						"page-title",
						"items",
						"banner-image-url"
					]), createVNode("main", { class: "imas-blog-v2__page" }, [createVNode("section", { class: "imas-blog-v2__main" }, [
						(__props.properties.data ?? []).length > 0 ? (openBlock(), createBlock("p", {
							key: 0,
							class: "imas-property-listings__count text-dim"
						}, toDisplayString(resultsCountLabel.value), 1)) : createCommentVNode("", true),
						(__props.properties.data ?? []).length > 0 ? (openBlock(), createBlock("div", {
							key: 1,
							class: "imas-property-listings__grid"
						}, [createVNode(_sfc_main$3, { properties: __props.properties }, null, 8, ["properties"])])) : (openBlock(), createBlock("p", {
							key: 2,
							class: "imas-blog-v2__empty text-dim"
						}, toDisplayString(trans("properties.favorite_properties_empty")), 1)),
						createVNode(_sfc_main$4, {
							properties: __props.properties,
							onNavigate: scrollToListingsTop
						}, null, 8, ["properties"])
					]), createVNode("aside", { class: "imas-blog-v2-sidebar" }, [createVNode("div", { class: "imas-favorites-aside-sticky" }, [createVNode(PropertyShowContactSidebar_default, {
						"contact-store-url": __props.contactStoreUrl,
						"default-subject": inquirySubject.value,
						"source-page": inquirySubject.value
					}, null, 8, [
						"contact-store-url",
						"default-subject",
						"source-page"
					])])])])], 512)];
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
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/Pages/FavoriteProperties.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var FavoriteProperties_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-50914387"]]);
//#endregion
export { FavoriteProperties_default as default };
