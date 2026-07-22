import { d as _plugin_vue_export_helper_default } from "../ssr.js";
import { o as localizedRoute, t as _sfc_main$2 } from "./App-BI701647.js";
import { t as useScrollReveal } from "./useScrollReveal-Gyyo-c-h.js";
import { t as _sfc_main$3 } from "./InnerPageHeadingHero-BLXiwMGA.js";
import { t as _sfc_main$4 } from "./FeaturedPropertiesSidebar-kVCwhJ6B.js";
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
		const globals = computed(() => page.props.globals ?? {});
		const seo = computed(() => globals.value.seo ?? {});
		const media = computed(() => globals.value.media ?? {});
		const contentHtml = computed(() => props.aboutUs.content ?? "");
		const heroYoutubeEmbed = computed(() => {
			const raw = props.aboutUs.youtube_embed ?? "";
			return typeof raw === "string" ? raw.trim() : "";
		});
		function pickSeoString(fromProps, ...globalKeys) {
			const p = fromProps;
			if (typeof p === "string" && p.trim() !== "") return p.trim();
			const s = seo.value;
			for (const key of globalKeys) {
				const v = s[key];
				if (typeof v === "string" && v.trim() !== "") return v.trim();
			}
			return "";
		}
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const sectionLabel = computed(() => trans("about_us.title"));
		const pageHeadingTitle = computed(() => {
			const t = pickSeoString(props.aboutUs.meta_title, "about_us_meta_title");
			return t !== "" ? t : sectionLabel.value;
		});
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
			const url = media.value.about_us_banner;
			if (typeof url !== "string" || url.trim() === "") return "";
			const trimmed = url.trim();
			if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) return "";
			return trimmed;
		});
		const documentTitle = computed(() => {
			const t = pickSeoString(props.aboutUs.meta_title, "about_us_meta_title");
			if (t !== "") return `${t} | ${page.props.appName}`;
			return `${sectionLabel.value} | ${page.props.appName}`;
		});
		const metaDescription = computed(() => pickSeoString(props.aboutUs.meta_description, "about_us_meta_description", "site_meta_description", "website_desc"));
		const metaKeywords = computed(() => pickSeoString(props.aboutUs.meta_keywords, "about_us_meta_keywords", "site_meta_keywords", "website_keywords"));
		const ogTitle = computed(() => {
			const t = pickSeoString(props.aboutUs.meta_title, "about_us_meta_title");
			return t !== "" ? t : sectionLabel.value;
		});
		const ogDescription = computed(() => metaDescription.value);
		const ogImage = computed(() => {
			const banner = media.value.about_us_banner;
			if (typeof banner === "string" && banner.trim() !== "") {
				const trimmed = banner.trim();
				if (!/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) return trimmed;
			}
			const fallback = media.value.meta_img;
			return typeof fallback === "string" && fallback.trim() !== "" ? fallback.trim() : "";
		});
		const canonicalUrl = computed(() => {
			if (typeof route !== "function" || !route().has?.("about-us")) return "";
			try {
				return route("about-us");
			} catch {
				return "";
			}
		});
		const ogUrl = computed(() => canonicalUrl.value);
		const twitterCard = computed(() => ogImage.value ? "summary_large_image" : "summary");
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
			_push(ssrRenderComponent(unref(Head), { title: documentTitle.value }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (metaDescription.value) _push(`<meta head-key="description" name="description"${ssrRenderAttr("content", metaDescription.value)} data-v-1fce4a6b${_scopeId}>`);
						else _push(`<!---->`);
						if (metaKeywords.value) _push(`<meta head-key="keywords" name="keywords"${ssrRenderAttr("content", metaKeywords.value)} data-v-1fce4a6b${_scopeId}>`);
						else _push(`<!---->`);
						if (canonicalUrl.value) _push(`<link head-key="canonical" rel="canonical"${ssrRenderAttr("href", canonicalUrl.value)} data-v-1fce4a6b${_scopeId}>`);
						else _push(`<!---->`);
						if (ogTitle.value) _push(`<meta head-key="og:title" property="og:title"${ssrRenderAttr("content", ogTitle.value)} data-v-1fce4a6b${_scopeId}>`);
						else _push(`<!---->`);
						if (ogDescription.value) _push(`<meta head-key="og:description" property="og:description"${ssrRenderAttr("content", ogDescription.value)} data-v-1fce4a6b${_scopeId}>`);
						else _push(`<!---->`);
						if (ogImage.value) _push(`<meta head-key="og:image" property="og:image"${ssrRenderAttr("content", ogImage.value)} data-v-1fce4a6b${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="og:type" property="og:type" content="website" data-v-1fce4a6b${_scopeId}>`);
						if (ogUrl.value) _push(`<meta head-key="og:url" property="og:url"${ssrRenderAttr("content", ogUrl.value)} data-v-1fce4a6b${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="twitter:card" name="twitter:card"${ssrRenderAttr("content", twitterCard.value)} data-v-1fce4a6b${_scopeId}>`);
						if (ogTitle.value) _push(`<meta head-key="twitter:title" name="twitter:title"${ssrRenderAttr("content", ogTitle.value)} data-v-1fce4a6b${_scopeId}>`);
						else _push(`<!---->`);
						if (ogDescription.value) _push(`<meta head-key="twitter:description" name="twitter:description"${ssrRenderAttr("content", ogDescription.value)} data-v-1fce4a6b${_scopeId}>`);
						else _push(`<!---->`);
						if (ogImage.value) _push(`<meta head-key="twitter:image" name="twitter:image"${ssrRenderAttr("content", ogImage.value)} data-v-1fce4a6b${_scopeId}>`);
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
			_push(ssrRenderComponent(_sfc_main$2, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="inner-pages imas-about-page" data-v-1fce4a6b${_scopeId}>`);
						_push(ssrRenderComponent(_sfc_main$3, {
							"page-title": pageHeadingTitle.value,
							items: headingItems.value,
							"banner-image-url": heroBannerUrl.value,
							"banner-video-embed": heroYoutubeEmbed.value
						}, null, _parent, _scopeId));
						_push(`<main class="${ssrRenderClass([{ "imas-about-page__page--with-sidebar": hasSidebar.value }, "imas-about-page__page imas-blog-v2__page container"])}" data-v-1fce4a6b${_scopeId}><section class="imas-about-page__main" data-v-1fce4a6b${_scopeId}>`);
						if (contentHtml.value) _push(`<article class="imas-blog-show imas-cms-page-show" data-v-1fce4a6b${_scopeId}><div class="imas-blog-show__content" data-v-1fce4a6b${_scopeId}><div class="imas-blog-show-body imas-cms-page-show__body text-base text-start" data-v-1fce4a6b${_scopeId}>${contentHtml.value ?? ""}</div></div></article>`);
						else _push(`<!---->`);
						if (!contentHtml.value) _push(`<p class="imas-about-page__empty text-muted text-base" data-v-1fce4a6b${_scopeId}>${ssrInterpolate(trans("about_us.no_content"))}</p>`);
						else _push(`<!---->`);
						_push(`</section>`);
						if (hasSidebar.value) _push(ssrRenderComponent(_sfc_main$1, { "featured-properties": __props.featuredProperties }, null, _parent, _scopeId));
						else _push(`<!---->`);
						_push(`</main></div>`);
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
					]), createVNode("main", { class: ["imas-about-page__page imas-blog-v2__page container", { "imas-about-page__page--with-sidebar": hasSidebar.value }] }, [createVNode("section", { class: "imas-about-page__main" }, [contentHtml.value ? (openBlock(), createBlock("article", {
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
var AboutUs_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-1fce4a6b"]]);
//#endregion
export { AboutUs_default as default };
