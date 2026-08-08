import { f as _plugin_vue_export_helper_default } from "../ssr.js";
import { o as localizedRoute, t as _sfc_main$2 } from "./App-DkOZMeWI.js";
import { t as useScrollReveal } from "./useScrollReveal-BBzB6gt6.js";
import { t as useDocumentSeo } from "./useDocumentSeo-IoWJXXs8.js";
import { t as _sfc_main$3 } from "./InnerPageHeadingHero-Cb2JTq3_.js";
import { t as _sfc_main$4 } from "./FeaturedPropertiesSidebar-4D26uQ27.js";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed, createBlock, createCommentVNode, createVNode, mergeProps, openBlock, ref, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region Modules/Base/resources/assets/js/components/AboutUsQuickLinksSidebar.vue
var _sfc_main$1 = {
	__name: "AboutUsQuickLinksSidebar",
	__ssrInlineRender: true,
	props: { featuredProperties: {
		type: Array,
		default: () => []
	} },
	setup(__props) {
		const props = __props;
		const page = usePage();
		const globals = computed(() => page.props.globals ?? {});
		const media = computed(() => globals.value.media ?? {});
		const activeLocale = computed(() => page.props.locale || "en");
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const featuredPropertiesHeading = computed(() => trans("aboutUs.featured_properties"));
		const showSidebar = computed(() => quickLinks.value.length > 0 || props.featuredProperties.length > 0);
		function resolveMediaBanner(url) {
			if (typeof url !== "string" || url.trim() === "") return "";
			const trimmed = url.trim();
			if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) return "";
			return trimmed;
		}
		function mediaFallback() {
			const meta = media.value.meta_img;
			return typeof meta === "string" && meta.trim() !== "" ? meta.trim() : "";
		}
		function resolveRouteUrl(name, fallbackPath) {
			try {
				if (typeof route === "function" && route().has?.(name)) return route(name);
			} catch {}
			return localizedRoute(name, {}, activeLocale.value, fallbackPath);
		}
		const quickLinks = computed(() => {
			const fallback = mediaFallback();
			return [{
				id: "turkish-citizenship",
				title: trans("navBar.Turkish Citizenship"),
				url: resolveRouteUrl("turkish-citizenship", "/turkish-citizenship"),
				image: resolveMediaBanner(media.value.turkish_citizenship_banner) || fallback
			}, {
				id: "blog",
				title: trans("navBar.Blogs"),
				url: resolveRouteUrl("blog.index", "/blog"),
				image: resolveMediaBanner(media.value.blog_show_banner) || fallback
			}].filter((row) => row.url);
		});
		return (_ctx, _push, _parent, _attrs) => {
			if (showSidebar.value) {
				_push(`<aside${ssrRenderAttrs(mergeProps({ class: "imas-blog-v2-sidebar imas-about-page__quick-links" }, _attrs))}>`);
				if (quickLinks.value.length) {
					_push(`<div class="imas-blog-v2-sidebar__box"><h4 class="imas-blog-v2-sidebar__heading">${ssrInterpolate(trans("aboutUs.explore_more"))}</h4><div class="imas-blog-v2-sidebar__recent"><!--[-->`);
					ssrRenderList(quickLinks.value, (link) => {
						_push(ssrRenderComponent(unref(Link), {
							key: link.id,
							href: link.url,
							class: "imas-blog-v2-sidebar__recent-item"
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) {
									if (link.image) _push(`<img${ssrRenderAttr("src", link.image)}${ssrRenderAttr("alt", link.title)} loading="lazy"${_scopeId}>`);
									else _push(`<!---->`);
									_push(`<div${_scopeId}><div class="imas-blog-v2-sidebar__recent-title"${_scopeId}>${ssrInterpolate(link.title)}</div></div>`);
								} else return [link.image ? (openBlock(), createBlock("img", {
									key: 0,
									src: link.image,
									alt: link.title,
									loading: "lazy"
								}, null, 8, ["src", "alt"])) : createCommentVNode("", true), createVNode("div", null, [createVNode("div", { class: "imas-blog-v2-sidebar__recent-title" }, toDisplayString(link.title), 1)])];
							}),
							_: 2
						}, _parent));
					});
					_push(`<!--]--></div></div>`);
				} else _push(`<!---->`);
				if (__props.featuredProperties.length > 0) _push(ssrRenderComponent(_sfc_main$4, {
					"featured-properties": __props.featuredProperties,
					heading: featuredPropertiesHeading.value
				}, null, _parent));
				else _push(`<!---->`);
				_push(`</aside>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/components/AboutUsQuickLinksSidebar.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
//#endregion
//#region Modules/Base/resources/assets/js/Pages/AboutUs.vue
var _sfc_main = {
	__name: "AboutUs",
	__ssrInlineRender: true,
	props: {
		aboutUs: {
			type: Object,
			required: true
		},
		featuredProperties: {
			type: Array,
			default: () => []
		}
	},
	setup(__props) {
		const props = __props;
		const page = usePage();
		const pageRef = ref(null);
		useScrollReveal(pageRef, { variant: "propertyListings" });
		const contentHtml = computed(() => props.aboutUs.content ?? "");
		const heroYoutubeEmbed = computed(() => {
			const raw = props.aboutUs.youtube_embed ?? "";
			return typeof raw === "string" ? raw.trim() : "";
		});
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const sectionLabel = computed(() => trans("about_us.title"));
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
		const { media, pickSeoString, title: documentTitle, description: metaDescription, keywords: metaKeywords, ogTitle, ogDescription, ogImage, canonical: canonicalUrl, ogUrl, twitterCard } = useDocumentSeo({
			pageTitle: () => {
				const t = props.aboutUs.meta_title;
				if (typeof t === "string" && t.trim() !== "") return t.trim();
				const fromGlobal = pickSeoString("about_us_meta_title");
				return fromGlobal !== "" ? fromGlobal : sectionLabel.value;
			},
			description: () => {
				const d = props.aboutUs.meta_description;
				if (typeof d === "string" && d.trim() !== "") return d.trim();
				return pickSeoString("about_us_meta_description", "site_meta_description", "website_desc");
			},
			keywords: () => {
				const k = props.aboutUs.meta_keywords;
				if (typeof k === "string" && k.trim() !== "") return k.trim();
				return pickSeoString("about_us_meta_keywords", "site_meta_keywords", "website_keywords");
			},
			ogImage: () => {
				const url = page.props.globals?.media?.about_us_banner;
				if (typeof url === "string" && url.trim() !== "") {
					const trimmed = url.trim();
					if (!/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) return trimmed;
				}
				return "";
			},
			canonical: () => {
				if (typeof route !== "function" || !route().has?.("about-us")) return "";
				try {
					return route("about-us");
				} catch {
					return "";
				}
			}
		});
		const pageHeadingTitle = computed(() => {
			const t = props.aboutUs.meta_title;
			if (typeof t === "string" && t.trim() !== "") return t.trim();
			const fromGlobal = pickSeoString("about_us_meta_title");
			return fromGlobal !== "" ? fromGlobal : sectionLabel.value;
		});
		const heroBannerUrl = computed(() => {
			const url = media.value.about_us_banner;
			if (typeof url !== "string" || url.trim() === "") return "";
			const trimmed = url.trim();
			if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) return "";
			return trimmed;
		});
		const hasQuickLinks = computed(() => {
			try {
				return typeof route === "function" && route().has?.("turkish-citizenship") || typeof route === "function" && route().has?.("blog.index");
			} catch {
				return true;
			}
		});
		const hasSidebar = computed(() => hasQuickLinks.value || props.featuredProperties.length > 0);
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: unref(documentTitle) }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (unref(metaDescription)) _push(`<meta head-key="description" name="description"${ssrRenderAttr("content", unref(metaDescription))} data-v-5bbe573f${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(metaKeywords)) _push(`<meta head-key="keywords" name="keywords"${ssrRenderAttr("content", unref(metaKeywords))} data-v-5bbe573f${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(canonicalUrl)) _push(`<link head-key="canonical" rel="canonical"${ssrRenderAttr("href", unref(canonicalUrl))} data-v-5bbe573f${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogTitle)) _push(`<meta head-key="og:title" property="og:title"${ssrRenderAttr("content", unref(ogTitle))} data-v-5bbe573f${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogDescription)) _push(`<meta head-key="og:description" property="og:description"${ssrRenderAttr("content", unref(ogDescription))} data-v-5bbe573f${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogImage)) _push(`<meta head-key="og:image" property="og:image"${ssrRenderAttr("content", unref(ogImage))} data-v-5bbe573f${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="og:type" property="og:type" content="website" data-v-5bbe573f${_scopeId}>`);
						if (unref(ogUrl)) _push(`<meta head-key="og:url" property="og:url"${ssrRenderAttr("content", unref(ogUrl))} data-v-5bbe573f${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="twitter:card" name="twitter:card"${ssrRenderAttr("content", unref(twitterCard))} data-v-5bbe573f${_scopeId}>`);
						if (unref(ogTitle)) _push(`<meta head-key="twitter:title" name="twitter:title"${ssrRenderAttr("content", unref(ogTitle))} data-v-5bbe573f${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogDescription)) _push(`<meta head-key="twitter:description" name="twitter:description"${ssrRenderAttr("content", unref(ogDescription))} data-v-5bbe573f${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogImage)) _push(`<meta head-key="twitter:image" name="twitter:image"${ssrRenderAttr("content", unref(ogImage))} data-v-5bbe573f${_scopeId}>`);
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
			_push(ssrRenderComponent(_sfc_main$2, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="inner-pages imas-about-page" data-v-5bbe573f${_scopeId}>`);
						_push(ssrRenderComponent(_sfc_main$3, {
							"page-title": pageHeadingTitle.value,
							items: headingItems.value,
							"banner-image-url": heroBannerUrl.value,
							"banner-video-embed": heroYoutubeEmbed.value
						}, null, _parent, _scopeId));
						_push(`<div class="${ssrRenderClass([{ "imas-about-page__page--with-sidebar": hasSidebar.value }, "imas-about-page__page imas-blog-v2__page container"])}" data-v-5bbe573f${_scopeId}><section class="imas-about-page__main" data-v-5bbe573f${_scopeId}>`);
						if (contentHtml.value) _push(`<article class="imas-blog-show imas-cms-page-show" data-v-5bbe573f${_scopeId}><div class="imas-blog-show__content" data-v-5bbe573f${_scopeId}><div class="imas-blog-show-body imas-cms-page-show__body text-base text-start" data-v-5bbe573f${_scopeId}>${contentHtml.value ?? ""}</div></div></article>`);
						else _push(`<!---->`);
						if (!contentHtml.value) _push(`<p class="imas-about-page__empty text-muted text-base" data-v-5bbe573f${_scopeId}>${ssrInterpolate(trans("about_us.no_content"))}</p>`);
						else _push(`<!---->`);
						_push(`</section>`);
						if (hasSidebar.value) _push(ssrRenderComponent(_sfc_main$1, { "featured-properties": __props.featuredProperties }, null, _parent, _scopeId));
						else _push(`<!---->`);
						_push(`</div></div>`);
					} else return [createVNode("div", {
						ref_key: "pageRef",
						ref: pageRef,
						class: "inner-pages imas-about-page"
					}, [createVNode(_sfc_main$3, {
						"page-title": pageHeadingTitle.value,
						items: headingItems.value,
						"banner-image-url": heroBannerUrl.value,
						"banner-video-embed": heroYoutubeEmbed.value
					}, null, 8, [
						"page-title",
						"items",
						"banner-image-url",
						"banner-video-embed"
					]), createVNode("div", { class: ["imas-about-page__page imas-blog-v2__page container", { "imas-about-page__page--with-sidebar": hasSidebar.value }] }, [createVNode("section", { class: "imas-about-page__main" }, [contentHtml.value ? (openBlock(), createBlock("article", {
						key: 0,
						class: "imas-blog-show imas-cms-page-show"
					}, [createVNode("div", { class: "imas-blog-show__content" }, [createVNode("div", {
						class: "imas-blog-show-body imas-cms-page-show__body text-base text-start",
						innerHTML: contentHtml.value
					}, null, 8, ["innerHTML"])])])) : createCommentVNode("", true), !contentHtml.value ? (openBlock(), createBlock("p", {
						key: 1,
						class: "imas-about-page__empty text-muted text-base"
					}, toDisplayString(trans("about_us.no_content")), 1)) : createCommentVNode("", true)]), hasSidebar.value ? (openBlock(), createBlock(_sfc_main$1, {
						key: 0,
						"featured-properties": __props.featuredProperties
					}, null, 8, ["featured-properties"])) : createCommentVNode("", true)], 2)], 512)];
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
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/Pages/AboutUs.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var AboutUs_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-5bbe573f"]]);
//#endregion
export { AboutUs_default as default };
