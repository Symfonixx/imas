import { o as localizedRoute, t as _sfc_main$2 } from "./App-nb92tFBB.js";
import { t as useDocumentSeo } from "./useDocumentSeo-IoWJXXs8.js";
import { t as _sfc_main$3 } from "./InnerPageHeadingHero-B4myItxi.js";
import { t as _sfc_main$4 } from "./BlogV2ArticleCard-DB7mNYG4.js";
import { n as _sfc_main$5, t as blogIndexLocalizedUrl } from "./blogLocalizedRoute-Zilze5J4.js";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { Fragment, computed, createBlock, createCommentVNode, createVNode, mergeProps, onMounted, openBlock, ref, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region Modules/Cms/resources/assets/js/Components/BlogV2Pagination.vue
var _sfc_main$1 = {
	__name: "BlogV2Pagination",
	__ssrInlineRender: true,
	props: { links: {
		type: Array,
		default: () => []
	} },
	emits: ["navigate"],
	setup(__props, { emit: __emit }) {
		const emit = __emit;
		function onNavigate(event) {
			const btn = event.currentTarget;
			if (!btn || typeof btn.getBoundingClientRect !== "function") {
				emit("navigate");
				return;
			}
			const rect = btn.getBoundingClientRect();
			const size = Math.max(rect.width, rect.height);
			const ripple = document.createElement("span");
			ripple.className = "imas-blog-v2-pagination__ripple";
			ripple.style.width = `${size}px`;
			ripple.style.height = `${size}px`;
			ripple.style.left = `${event.clientX - rect.left - size / 2}px`;
			ripple.style.top = `${event.clientY - rect.top - size / 2}px`;
			btn.appendChild(ripple);
			setTimeout(() => ripple.remove(), 600);
			emit("navigate");
		}
		return (_ctx, _push, _parent, _attrs) => {
			if (__props.links.length > 0) {
				_push(`<nav${ssrRenderAttrs(mergeProps({
					class: "imas-blog-v2-pagination",
					"aria-label": "Blog pagination"
				}, _attrs))}><!--[-->`);
				ssrRenderList(__props.links, (link, idx) => {
					_push(`<!--[-->`);
					if (link.url) _push(ssrRenderComponent(unref(Link), {
						href: link.url,
						class: ["imas-blog-v2-pagination__btn", { "is-active": link.active }],
						"preserve-scroll": false,
						onClick: onNavigate
					}, {
						default: withCtx((_, _push, _parent, _scopeId) => {
							if (_push) _push(`<span${_scopeId}>${link.displayLabel ?? ""}</span>`);
							else return [createVNode("span", { innerHTML: link.displayLabel }, null, 8, ["innerHTML"])];
						}),
						_: 2
					}, _parent));
					else _push(`<span class="imas-blog-v2-pagination__btn is-disabled" aria-disabled="true"><span>${link.displayLabel ?? ""}</span></span>`);
					_push(`<!--]-->`);
				});
				_push(`<!--]--></nav>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Cms/resources/assets/js/Components/BlogV2Pagination.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
//#endregion
//#region Modules/Cms/resources/assets/js/Pages/Index.vue
var _sfc_main = {
	__name: "Index",
	__ssrInlineRender: true,
	props: {
		title: {
			type: String,
			required: true
		},
		blogs: {
			type: Object,
			required: true
		},
		recentBlogs: {
			type: Array,
			default: () => []
		},
		categories: {
			type: Array,
			default: () => []
		},
		filters: {
			type: Object,
			required: true
		}
	},
	setup(__props) {
		const props = __props;
		const page = usePage();
		const activeLocale = computed(() => page.props.locale || "en");
		const pageRef = ref(null);
		function scrollToBlogTop() {
			pageRef.value?.scrollIntoView({
				behavior: "smooth",
				block: "start"
			});
		}
		onMounted(() => {
			scrollToBlogTop();
		});
		const { media, title: documentTitle, description: metaDescription, keywords: metaKeywords, ogTitle, ogDescription, ogImage, canonical: canonicalUrl, ogUrl, twitterCard } = useDocumentSeo({
			pageTitle: () => props.title,
			canonical: () => blogIndexLocalizedUrl(activeLocale.value)
		});
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const readArticleCta = computed(() => {
			return `${trans("articles.read_more").replace(/\.\.\.$|…$/u, "").trim()} ›`;
		});
		const blogHeadingItems = computed(() => {
			const rows = [];
			try {
				if (typeof route === "function" && route().has?.("home")) rows.push({
					title: trans("navBar.Home"),
					href: localizedRoute("home", {}, activeLocale.value, "/")
				});
			} catch {}
			rows.push({
				title: trans("navBar.Blogs"),
				href: null
			});
			return rows;
		});
		const blogIndexUrl = computed(() => blogIndexLocalizedUrl(activeLocale.value));
		function categoryIndexUrl(categorySlug) {
			const params = {};
			if (props.filters.q) params.q = props.filters.q;
			if (categorySlug != null && categorySlug !== "") params.category = categorySlug;
			return blogIndexLocalizedUrl(activeLocale.value, params);
		}
		const paginationLinks = computed(() => {
			const raw = props.blogs?.links ?? [];
			const n = raw.length;
			if (n < 2) return raw.map((link) => ({
				...link,
				displayLabel: link.label
			}));
			return raw.map((link, idx) => {
				let displayLabel = link.label;
				if (idx === 0) displayLabel = trans("global.previous");
				else if (idx === n - 1) displayLabel = trans("global.next");
				return {
					...link,
					displayLabel
				};
			});
		});
		const blogShowBannerUrl = computed(() => {
			const url = media.value.blog_show_banner;
			if (typeof url !== "string" || url.trim() === "") return "";
			const trimmed = url.trim();
			if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) return "";
			return trimmed;
		});
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
						_push(`<div class="imas-blog-v2 imas-blog-section-anchor"${_scopeId}>`);
						_push(ssrRenderComponent(_sfc_main$3, {
							"page-title": trans("blogs.hub_title"),
							items: blogHeadingItems.value,
							"banner-image-url": blogShowBannerUrl.value
						}, null, _parent, _scopeId));
						_push(`<main class="imas-blog-v2__page container"${_scopeId}><section class="imas-blog-v2__main"${_scopeId}>`);
						if (__props.blogs.data.length > 0) {
							_push(`<div class="imas-blog-v2__grid"${_scopeId}><!--[-->`);
							ssrRenderList(__props.blogs.data, (post, idx) => {
								_push(ssrRenderComponent(_sfc_main$4, {
									key: post.id,
									article: post,
									"stagger-index": idx,
									"read-more-label": trans("articles.read_more"),
									"read-article-label": readArticleCta.value
								}, null, _parent, _scopeId));
							});
							_push(`<!--]--></div>`);
						} else _push(`<p class="imas-blog-v2__empty text-dim text-start"${_scopeId}>${ssrInterpolate(trans("blogs.no_posts"))}</p>`);
						_push(ssrRenderComponent(_sfc_main$1, {
							links: paginationLinks.value,
							onNavigate: scrollToBlogTop
						}, null, _parent, _scopeId));
						_push(`</section>`);
						_push(ssrRenderComponent(_sfc_main$5, {
							"search-action": blogIndexUrl.value,
							filters: __props.filters,
							categories: __props.categories,
							"recent-blogs": __props.recentBlogs,
							"category-url": categoryIndexUrl
						}, null, _parent, _scopeId));
						_push(`</main></div>`);
					} else return [createVNode("div", {
						class: "imas-blog-v2 imas-blog-section-anchor",
						ref_key: "pageRef",
						ref: pageRef
					}, [createVNode(_sfc_main$3, {
						"page-title": trans("blogs.hub_title"),
						items: blogHeadingItems.value,
						"banner-image-url": blogShowBannerUrl.value
					}, null, 8, [
						"page-title",
						"items",
						"banner-image-url"
					]), createVNode("main", { class: "imas-blog-v2__page container" }, [createVNode("section", { class: "imas-blog-v2__main" }, [__props.blogs.data.length > 0 ? (openBlock(), createBlock("div", {
						key: 0,
						class: "imas-blog-v2__grid"
					}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.blogs.data, (post, idx) => {
						return openBlock(), createBlock(_sfc_main$4, {
							key: post.id,
							article: post,
							"stagger-index": idx,
							"read-more-label": trans("articles.read_more"),
							"read-article-label": readArticleCta.value
						}, null, 8, [
							"article",
							"stagger-index",
							"read-more-label",
							"read-article-label"
						]);
					}), 128))])) : (openBlock(), createBlock("p", {
						key: 1,
						class: "imas-blog-v2__empty text-dim text-start"
					}, toDisplayString(trans("blogs.no_posts")), 1)), createVNode(_sfc_main$1, {
						links: paginationLinks.value,
						onNavigate: scrollToBlogTop
					}, null, 8, ["links"])]), createVNode(_sfc_main$5, {
						"search-action": blogIndexUrl.value,
						filters: __props.filters,
						categories: __props.categories,
						"recent-blogs": __props.recentBlogs,
						"category-url": categoryIndexUrl
					}, null, 8, [
						"search-action",
						"filters",
						"categories",
						"recent-blogs"
					])])], 512)];
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
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Cms/resources/assets/js/Pages/Index.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
//#endregion
export { _sfc_main as default };
