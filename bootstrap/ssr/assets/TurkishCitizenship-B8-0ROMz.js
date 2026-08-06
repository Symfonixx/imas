import { f as _plugin_vue_export_helper_default } from "../ssr.js";
import { t as _sfc_main$1 } from "./App-nb92tFBB.js";
import { t as useScrollReveal } from "./useScrollReveal-BTLywvvx.js";
import { t as useDocumentSeo } from "./useDocumentSeo-IoWJXXs8.js";
import { t as _sfc_main$2 } from "./InnerPageHeadingHero-B4myItxi.js";
import { t as TurkishCitizenshipSplitTitle_default } from "./TurkishCitizenshipSplitTitle-CTEYEJik.js";
import { t as PopularPropertiesSection_default } from "./PopularPropertiesSection-B82wt-HW.js";
import { t as PropertyShowContactSidebar_default } from "./PropertyShowContactSidebar-W6-NIpJl.js";
import { t as useBoundedSticky } from "./useBoundedSticky-rITTuW3v.js";
import { Head, usePage } from "@inertiajs/vue3";
import { computed, createBlock, createCommentVNode, createVNode, openBlock, ref, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderComponent } from "vue/server-renderer";
//#region Modules/Property/resources/assets/js/Pages/TurkishCitizenship.vue
var _sfc_main = {
	__name: "TurkishCitizenship",
	__ssrInlineRender: true,
	props: {
		turkishCitizenship: {
			type: Object,
			required: true
		},
		citizenshipProperties: {
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
		const pageRef = ref(null);
		const tcContentRowRef = ref(null);
		const tcSidebarColRef = ref(null);
		const tcSidebarStickyRef = ref(null);
		useScrollReveal(pageRef, { variant: "propertyListings" });
		useBoundedSticky({
			boundaryRef: tcContentRowRef,
			columnRef: tcSidebarColRef,
			targetRef: tcSidebarStickyRef
		});
		const globals = computed(() => page.props.globals ?? {});
		const media = computed(() => globals.value.media ?? {});
		const turkishCitizenshipGlobals = computed(() => globals.value.turkish_citizenship ?? {});
		const contentHtml = computed(() => props.turkishCitizenship.content ?? "");
		const youtubeEmbed = computed(() => props.turkishCitizenship.youtube_embed ?? "");
		const sectionLabel = computed(() => trans("navBar.Turkish Citizenship"));
		function pickTranslation(key, fallback) {
			const value = trans(key);
			if (value && value !== key) return value;
			return fallback;
		}
		const titlePrimary = computed(() => pickTranslation("turkishCitizenship.overview_title_primary", "Turkish Citizenship"));
		const titleAccent = computed(() => pickTranslation("turkishCitizenship.overview_title_accent", "by Investment Programme"));
		const { pickSeoString, title: documentTitle, description: metaDescription, keywords: metaKeywords, ogTitle, ogDescription, ogImage, canonical: canonicalUrl, ogUrl, twitterCard } = useDocumentSeo({
			pageTitle: () => {
				const t = props.turkishCitizenship.meta_title;
				if (typeof t === "string" && t.trim() !== "") return t.trim();
				const fromGlobal = pickSeoString("turkish_citizenship_meta_title");
				return fromGlobal !== "" ? fromGlobal : sectionLabel.value;
			},
			description: () => {
				const d = props.turkishCitizenship.meta_description;
				if (typeof d === "string" && d.trim() !== "") return d.trim();
				return pickSeoString("turkish_citizenship_meta_description", "site_meta_description", "website_desc");
			},
			keywords: () => {
				const k = props.turkishCitizenship.meta_keywords;
				if (typeof k === "string" && k.trim() !== "") return k.trim();
				return pickSeoString("turkish_citizenship_meta_keywords", "site_meta_keywords", "website_keywords");
			},
			ogImage: () => {
				const banner = props.turkishCitizenship.banner_url || turkishCitizenshipGlobals.value.banner_url || media.value.turkish_citizenship_banner;
				if (typeof banner === "string" && banner.trim() !== "") {
					const trimmed = banner.trim();
					if (!/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) return trimmed;
				}
				return "";
			},
			canonical: () => {
				if (typeof route !== "function" || !route().has?.("turkish-citizenship")) return "";
				try {
					return route("turkish-citizenship");
				} catch {
					return "";
				}
			}
		});
		const pageHeadingTitle = computed(() => {
			const t = props.turkishCitizenship.meta_title;
			if (typeof t === "string" && t.trim() !== "") return t.trim();
			const fromGlobal = pickSeoString("turkish_citizenship_meta_title");
			return fromGlobal !== "" ? fromGlobal : sectionLabel.value;
		});
		const inquirySubject = computed(() => pageHeadingTitle.value);
		const headingItems = computed(() => {
			const rows = [];
			try {
				if (typeof route === "function" && route().has?.("home")) rows.push({
					title: trans("navBar.Home"),
					href: route("home")
				});
			} catch {}
			rows.push({
				title: sectionLabel.value,
				href: null
			});
			return rows;
		});
		const heroBannerUrl = computed(() => {
			const url = bannerUrl.value;
			if (typeof url !== "string" || url.trim() === "") return "";
			const trimmed = url.trim();
			if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) return "";
			return trimmed;
		});
		const bannerUrl = computed(() => {
			const url = props.turkishCitizenship.banner_url || turkishCitizenshipGlobals.value.banner_url || media.value.turkish_citizenship_banner;
			if (typeof url !== "string" || url.trim() === "") return "";
			return url.trim();
		});
		function trans(key) {
			return page.props.translations[key] || key;
		}
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: unref(documentTitle) }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (unref(metaDescription)) _push(`<meta head-key="description" name="description"${ssrRenderAttr("content", unref(metaDescription))} data-v-8ec607e0${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(metaKeywords)) _push(`<meta head-key="keywords" name="keywords"${ssrRenderAttr("content", unref(metaKeywords))} data-v-8ec607e0${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(canonicalUrl)) _push(`<link head-key="canonical" rel="canonical"${ssrRenderAttr("href", unref(canonicalUrl))} data-v-8ec607e0${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogTitle)) _push(`<meta head-key="og:title" property="og:title"${ssrRenderAttr("content", unref(ogTitle))} data-v-8ec607e0${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogDescription)) _push(`<meta head-key="og:description" property="og:description"${ssrRenderAttr("content", unref(ogDescription))} data-v-8ec607e0${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogImage)) _push(`<meta head-key="og:image" property="og:image"${ssrRenderAttr("content", unref(ogImage))} data-v-8ec607e0${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="og:type" property="og:type" content="website" data-v-8ec607e0${_scopeId}>`);
						if (unref(ogUrl)) _push(`<meta head-key="og:url" property="og:url"${ssrRenderAttr("content", unref(ogUrl))} data-v-8ec607e0${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="twitter:card" name="twitter:card"${ssrRenderAttr("content", unref(twitterCard))} data-v-8ec607e0${_scopeId}>`);
						if (unref(ogTitle)) _push(`<meta head-key="twitter:title" name="twitter:title"${ssrRenderAttr("content", unref(ogTitle))} data-v-8ec607e0${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogDescription)) _push(`<meta head-key="twitter:description" name="twitter:description"${ssrRenderAttr("content", unref(ogDescription))} data-v-8ec607e0${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogImage)) _push(`<meta head-key="twitter:image" name="twitter:image"${ssrRenderAttr("content", unref(ogImage))} data-v-8ec607e0${_scopeId}>`);
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
			_push(ssrRenderComponent(_sfc_main$1, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="inner-pages imas-tc-page-root" data-v-8ec607e0${_scopeId}>`);
						_push(ssrRenderComponent(_sfc_main$2, {
							"page-title": pageHeadingTitle.value,
							items: headingItems.value,
							"banner-image-url": heroBannerUrl.value
						}, null, _parent, _scopeId));
						_push(`<section class="blog blog-section bg-white pt-3 pb-5 imas-tc-page" data-v-8ec607e0${_scopeId}><div class="container" data-v-8ec607e0${_scopeId}><div class="row imas-tc-page__content-row" data-v-8ec607e0${_scopeId}><div class="col-lg-8 col-md-12" data-v-8ec607e0${_scopeId}>`);
						_push(ssrRenderComponent(TurkishCitizenshipSplitTitle_default, {
							primary: titlePrimary.value,
							accent: titleAccent.value,
							align: "start",
							reveal: ""
						}, null, _parent, _scopeId));
						_push(`<div class="blog-pots imas-tc-page-content" data-v-8ec607e0${_scopeId}>`);
						if (contentHtml.value) _push(`<div class="imas-tc-content" data-v-8ec607e0${_scopeId}>${contentHtml.value ?? ""}</div>`);
						else _push(`<!---->`);
						if (youtubeEmbed.value) _push(`<div class="imas-tc-video ratio ratio-16x9 mb-4 mt-4 w-100" data-v-8ec607e0${_scopeId}>${youtubeEmbed.value ?? ""}</div>`);
						else _push(`<!---->`);
						if (!contentHtml.value && !youtubeEmbed.value) _push(`<p class="text-muted" data-v-8ec607e0${_scopeId}>${ssrInterpolate(trans("Turkish citizenship page has no published content yet."))}</p>`);
						else _push(`<!---->`);
						_push(`</div></div><aside class="col-lg-4 col-md-12 car imas-tc-page__sidebar-col" data-v-8ec607e0${_scopeId}><div class="imas-tc-page__contact-sticky" data-v-8ec607e0${_scopeId}>`);
						_push(ssrRenderComponent(PropertyShowContactSidebar_default, {
							"contact-store-url": __props.contactStoreUrl,
							"default-subject": inquirySubject.value,
							"source-page": inquirySubject.value
						}, null, _parent, _scopeId));
						_push(`</div></aside></div></div></section>`);
						if (__props.citizenshipProperties.length > 0) _push(ssrRenderComponent(PopularPropertiesSection_default, {
							properties: __props.citizenshipProperties,
							"hide-title": true,
							"custom-title": trans("suitable_properties_for_turkish_citizenship_by_citizenship_program")
						}, null, _parent, _scopeId));
						else _push(`<!---->`);
						_push(`</div>`);
					} else return [createVNode("div", {
						ref_key: "pageRef",
						ref: pageRef,
						class: "inner-pages imas-tc-page-root"
					}, [
						createVNode(_sfc_main$2, {
							"page-title": pageHeadingTitle.value,
							items: headingItems.value,
							"banner-image-url": heroBannerUrl.value
						}, null, 8, [
							"page-title",
							"items",
							"banner-image-url"
						]),
						createVNode("section", { class: "blog blog-section bg-white pt-3 pb-5 imas-tc-page" }, [createVNode("div", { class: "container" }, [createVNode("div", {
							ref_key: "tcContentRowRef",
							ref: tcContentRowRef,
							class: "row imas-tc-page__content-row"
						}, [createVNode("div", { class: "col-lg-8 col-md-12" }, [createVNode(TurkishCitizenshipSplitTitle_default, {
							primary: titlePrimary.value,
							accent: titleAccent.value,
							align: "start",
							reveal: ""
						}, null, 8, ["primary", "accent"]), createVNode("div", { class: "blog-pots imas-tc-page-content" }, [
							contentHtml.value ? (openBlock(), createBlock("div", {
								key: 0,
								class: "imas-tc-content",
								innerHTML: contentHtml.value
							}, null, 8, ["innerHTML"])) : createCommentVNode("", true),
							youtubeEmbed.value ? (openBlock(), createBlock("div", {
								key: 1,
								class: "imas-tc-video ratio ratio-16x9 mb-4 mt-4 w-100",
								innerHTML: youtubeEmbed.value
							}, null, 8, ["innerHTML"])) : createCommentVNode("", true),
							!contentHtml.value && !youtubeEmbed.value ? (openBlock(), createBlock("p", {
								key: 2,
								class: "text-muted"
							}, toDisplayString(trans("Turkish citizenship page has no published content yet.")), 1)) : createCommentVNode("", true)
						])]), createVNode("aside", {
							ref_key: "tcSidebarColRef",
							ref: tcSidebarColRef,
							class: "col-lg-4 col-md-12 car imas-tc-page__sidebar-col"
						}, [createVNode("div", {
							ref_key: "tcSidebarStickyRef",
							ref: tcSidebarStickyRef,
							class: "imas-tc-page__contact-sticky"
						}, [createVNode(PropertyShowContactSidebar_default, {
							"contact-store-url": __props.contactStoreUrl,
							"default-subject": inquirySubject.value,
							"source-page": inquirySubject.value
						}, null, 8, [
							"contact-store-url",
							"default-subject",
							"source-page"
						])], 512)], 512)], 512)])]),
						__props.citizenshipProperties.length > 0 ? (openBlock(), createBlock(PopularPropertiesSection_default, {
							key: 0,
							properties: __props.citizenshipProperties,
							"hide-title": true,
							"custom-title": trans("suitable_properties_for_turkish_citizenship_by_citizenship_program")
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
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/Pages/TurkishCitizenship.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var TurkishCitizenship_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-8ec607e0"]]);
//#endregion
export { TurkishCitizenship_default as default };
