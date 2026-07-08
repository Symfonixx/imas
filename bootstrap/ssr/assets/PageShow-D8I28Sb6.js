import { o as localizedRoute, t as _sfc_main$1 } from "./App-CcYXakTU.js";
import { t as _sfc_main$2 } from "./InnerPageHeadingHero-CFmV_XXE.js";
import { Head, usePage } from "@inertiajs/vue3";
import { computed, createBlock, createCommentVNode, createVNode, openBlock, unref, useSSRContext, withCtx } from "vue";
import { ssrRenderAttr, ssrRenderComponent } from "vue/server-renderer";
//#region Modules/Cms/resources/assets/js/Pages/PageShow.vue
var _sfc_main = {
	__name: "PageShow",
	__ssrInlineRender: true,
	props: {
		title: {
			type: String,
			required: true
		},
		page: {
			type: Object,
			required: true
		}
	},
	setup(__props) {
		const props = __props;
		const inertiaPage = usePage();
		const activeLocale = computed(() => inertiaPage.props.locale || "en");
		function trans(key) {
			return inertiaPage.props.translations?.[key] || key;
		}
		const showHeroImage = computed(() => {
			const src = props.page.image;
			return typeof src === "string" && src.trim() !== "" && !src.includes("blank.png") && !/\/default\.jpg(?:\?.*)?$/i.test(src.trim());
		});
		const heroBannerUrl = computed(() => {
			if (!showHeroImage.value) return "";
			return props.page.image.trim();
		});
		function plainText(value) {
			if (typeof value !== "string") return "";
			return value.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
		}
		const headingItems = computed(() => {
			const rows = [];
			try {
				if (typeof route === "function" && route().has?.("home")) rows.push({
					title: trans("navBar.Home"),
					href: localizedRoute("home", {}, activeLocale.value, "/")
				});
			} catch {}
			rows.push({
				title: props.page.title,
				href: null
			});
			return rows;
		});
		const meta = computed(() => props.page.meta ?? {});
		const documentTitle = computed(() => `${plainText(String(meta.value.title || props.title))} | ${inertiaPage.props.appName}`);
		const metaDescription = computed(() => {
			const d = meta.value.description;
			return typeof d === "string" && d.trim() !== "" ? plainText(d) : "";
		});
		const metaKeywords = computed(() => {
			const k = meta.value.keywords;
			if (Array.isArray(k)) {
				const s = k.filter(Boolean).join(", ").trim();
				return s !== "" ? s : "";
			}
			if (typeof k === "string" && k.trim() !== "") return k.trim();
			return "";
		});
		const canonicalUrl = computed(() => {
			const u = meta.value.canonical_url;
			return typeof u === "string" && u.trim() !== "" ? u.trim() : "";
		});
		const ogTitle = computed(() => documentTitle.value);
		const ogDescription = computed(() => metaDescription.value);
		const ogImage = computed(() => {
			const u = meta.value.image;
			if (typeof u === "string" && u.trim() !== "") return u.trim();
			const fallback = props.page.image;
			return typeof fallback === "string" && fallback.trim() !== "" ? fallback.trim() : "";
		});
		const ogUrl = computed(() => canonicalUrl.value);
		const twitterCard = computed(() => ogImage.value ? "summary_large_image" : "summary");
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: documentTitle.value }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (metaDescription.value) _push(`<meta head-key="description" name="description"${ssrRenderAttr("content", metaDescription.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (metaKeywords.value) _push(`<meta head-key="keywords" name="keywords"${ssrRenderAttr("content", metaKeywords.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (canonicalUrl.value) _push(`<link head-key="canonical" rel="canonical"${ssrRenderAttr("href", canonicalUrl.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (ogTitle.value) _push(`<meta head-key="og:title" property="og:title"${ssrRenderAttr("content", ogTitle.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (ogDescription.value) _push(`<meta head-key="og:description" property="og:description"${ssrRenderAttr("content", ogDescription.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (ogImage.value) _push(`<meta head-key="og:image" property="og:image"${ssrRenderAttr("content", ogImage.value)}${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="og:type" property="og:type" content="website"${_scopeId}>`);
						if (ogUrl.value) _push(`<meta head-key="og:url" property="og:url"${ssrRenderAttr("content", ogUrl.value)}${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="twitter:card" name="twitter:card"${ssrRenderAttr("content", twitterCard.value)}${_scopeId}>`);
						if (ogTitle.value) _push(`<meta head-key="twitter:title" name="twitter:title"${ssrRenderAttr("content", ogTitle.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (ogDescription.value) _push(`<meta head-key="twitter:description" name="twitter:description"${ssrRenderAttr("content", ogDescription.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (ogImage.value) _push(`<meta head-key="twitter:image" name="twitter:image"${ssrRenderAttr("content", ogImage.value)}${_scopeId}>`);
						else _push(`<!---->`);
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
						}, null, 8, ["content"])) : createCommentVNode("", true)
					];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(_sfc_main$1, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="imas-blog-v2"${_scopeId}>`);
						_push(ssrRenderComponent(_sfc_main$2, {
							"page-title": __props.page.title,
							items: headingItems.value,
							"banner-image-url": heroBannerUrl.value
						}, null, _parent, _scopeId));
						_push(`<main class="imas-cms-page-show__page container"${_scopeId}><article class="imas-blog-show imas-cms-page-show"${_scopeId}><div class="imas-blog-show__content"${_scopeId}><div class="imas-blog-show-body imas-cms-page-show__body text-base text-start"${_scopeId}>${__props.page.content ?? ""}</div></div></article></main></div>`);
					} else return [createVNode("div", { class: "imas-blog-v2" }, [createVNode(_sfc_main$2, {
						"page-title": __props.page.title,
						items: headingItems.value,
						"banner-image-url": heroBannerUrl.value
					}, null, 8, [
						"page-title",
						"items",
						"banner-image-url"
					]), createVNode("main", { class: "imas-cms-page-show__page container" }, [createVNode("article", { class: "imas-blog-show imas-cms-page-show" }, [createVNode("div", { class: "imas-blog-show__content" }, [createVNode("div", {
						class: "imas-blog-show-body imas-cms-page-show__body text-base text-start",
						innerHTML: __props.page.content
					}, null, 8, ["innerHTML"])])])])])];
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
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Cms/resources/assets/js/Pages/PageShow.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
//#endregion
export { _sfc_main as default };
