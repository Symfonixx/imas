import { f as _plugin_vue_export_helper_default } from "../ssr.js";
import { o as localizedRoute, t as _sfc_main$1 } from "./App-DkOZMeWI.js";
import { t as useScrollReveal } from "./useScrollReveal-BBzB6gt6.js";
import { t as useDocumentSeo } from "./useDocumentSeo-IoWJXXs8.js";
import { t as _sfc_main$2 } from "./InnerPageHeadingHero-Cb2JTq3_.js";
import { n as _sfc_main$3, t as _sfc_main$4 } from "./PropertyListingPagination-DqSNAame.js";
import { t as PropertyShowContactSidebar_default } from "./PropertyShowContactSidebar-b9VAGH2m.js";
import { Head, usePage } from "@inertiajs/vue3";
import { computed, createBlock, createCommentVNode, createVNode, onMounted, openBlock, ref, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderComponent } from "vue/server-renderer";
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
		const { title: documentTitle, description: metaDescription, keywords: metaKeywords, ogTitle, ogDescription, ogImage, canonical: canonicalUrl, ogUrl, twitterCard, robots } = useDocumentSeo({
			pageTitle: () => props.title,
			robots: "noindex, nofollow",
			canonical: () => {
				try {
					if (typeof route === "function" && route().has?.("property.favorites")) return route("property.favorites");
				} catch {}
				return localizedRoute("property.favorites", {}, activeLocale.value, "/property/favorites");
			}
		});
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
			_push(ssrRenderComponent(unref(Head), { title: unref(documentTitle) }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (unref(metaDescription)) _push(`<meta head-key="description" name="description"${ssrRenderAttr("content", unref(metaDescription))} data-v-be711d90${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(metaKeywords)) _push(`<meta head-key="keywords" name="keywords"${ssrRenderAttr("content", unref(metaKeywords))} data-v-be711d90${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(canonicalUrl)) _push(`<link head-key="canonical" rel="canonical"${ssrRenderAttr("href", unref(canonicalUrl))} data-v-be711d90${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogTitle)) _push(`<meta head-key="og:title" property="og:title"${ssrRenderAttr("content", unref(ogTitle))} data-v-be711d90${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogDescription)) _push(`<meta head-key="og:description" property="og:description"${ssrRenderAttr("content", unref(ogDescription))} data-v-be711d90${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogImage)) _push(`<meta head-key="og:image" property="og:image"${ssrRenderAttr("content", unref(ogImage))} data-v-be711d90${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="og:type" property="og:type" content="website" data-v-be711d90${_scopeId}>`);
						if (unref(ogUrl)) _push(`<meta head-key="og:url" property="og:url"${ssrRenderAttr("content", unref(ogUrl))} data-v-be711d90${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(robots)) _push(`<meta head-key="robots" name="robots"${ssrRenderAttr("content", unref(robots))} data-v-be711d90${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="twitter:card" name="twitter:card"${ssrRenderAttr("content", unref(twitterCard))} data-v-be711d90${_scopeId}>`);
						if (unref(ogTitle)) _push(`<meta head-key="twitter:title" name="twitter:title"${ssrRenderAttr("content", unref(ogTitle))} data-v-be711d90${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogDescription)) _push(`<meta head-key="twitter:description" name="twitter:description"${ssrRenderAttr("content", unref(ogDescription))} data-v-be711d90${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogImage)) _push(`<meta head-key="twitter:image" name="twitter:image"${ssrRenderAttr("content", unref(ogImage))} data-v-be711d90${_scopeId}>`);
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
						unref(robots) ? (openBlock(), createBlock("meta", {
							key: 7,
							"head-key": "robots",
							name: "robots",
							content: unref(robots)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						createVNode("meta", {
							"head-key": "twitter:card",
							name: "twitter:card",
							content: unref(twitterCard)
						}, null, 8, ["content"]),
						unref(ogTitle) ? (openBlock(), createBlock("meta", {
							key: 8,
							"head-key": "twitter:title",
							name: "twitter:title",
							content: unref(ogTitle)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						unref(ogDescription) ? (openBlock(), createBlock("meta", {
							key: 9,
							"head-key": "twitter:description",
							name: "twitter:description",
							content: unref(ogDescription)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						unref(ogImage) ? (openBlock(), createBlock("meta", {
							key: 10,
							"head-key": "twitter:image",
							name: "twitter:image",
							content: unref(ogImage)
						}, null, 8, ["content"])) : createCommentVNode("", true)
					];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(_sfc_main$1, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="imas-blog-v2 imas-property-listings imas-blog-section-anchor" data-v-be711d90${_scopeId}>`);
						_push(ssrRenderComponent(_sfc_main$2, {
							"page-title": __props.title,
							items: headingItems.value,
							"banner-image-url": listingsBannerUrl.value
						}, null, _parent, _scopeId));
						_push(`<div class="imas-blog-v2__page" data-v-be711d90${_scopeId}><section class="imas-blog-v2__main" data-v-be711d90${_scopeId}>`);
						if ((__props.properties.data ?? []).length > 0) _push(`<p class="imas-property-listings__count text-dim" data-v-be711d90${_scopeId}>${ssrInterpolate(resultsCountLabel.value)}</p>`);
						else _push(`<!---->`);
						if ((__props.properties.data ?? []).length > 0) {
							_push(`<div class="imas-property-listings__grid" data-v-be711d90${_scopeId}>`);
							_push(ssrRenderComponent(_sfc_main$3, { properties: __props.properties }, null, _parent, _scopeId));
							_push(`</div>`);
						} else _push(`<p class="imas-blog-v2__empty text-dim" data-v-be711d90${_scopeId}>${ssrInterpolate(trans("properties.favorite_properties_empty"))}</p>`);
						_push(ssrRenderComponent(_sfc_main$4, {
							properties: __props.properties,
							onNavigate: scrollToListingsTop
						}, null, _parent, _scopeId));
						_push(`</section><aside class="imas-blog-v2-sidebar" data-v-be711d90${_scopeId}><div class="imas-favorites-aside-sticky" data-v-be711d90${_scopeId}>`);
						_push(ssrRenderComponent(PropertyShowContactSidebar_default, {
							"contact-store-url": __props.contactStoreUrl,
							"default-subject": inquirySubject.value,
							"source-page": inquirySubject.value
						}, null, _parent, _scopeId));
						_push(`</div></aside></div></div>`);
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
					]), createVNode("div", { class: "imas-blog-v2__page" }, [createVNode("section", { class: "imas-blog-v2__main" }, [
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
var FavoriteProperties_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-be711d90"]]);
//#endregion
export { FavoriteProperties_default as default };
