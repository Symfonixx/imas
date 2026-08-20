import { o as localizedRoute, t as _sfc_main$1 } from "./App-6l5p54Dj.js";
import { t as useScrollReveal } from "./useScrollReveal-B62WZo2W.js";
import { t as useDocumentSeo } from "./useDocumentSeo-DFy1QA_G.js";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { Fragment, computed, createBlock, createCommentVNode, createTextVNode, createVNode, openBlock, ref, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region resources/js/Pages/Errors/NotFound.vue
var _sfc_main = {
	__name: "NotFound",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		const pageRef = ref(null);
		useScrollReveal(pageRef, { variant: "sections" });
		const activeLocale = computed(() => page.props.locale || "en");
		function trans(key, fallback = key) {
			return page.props.translations?.[key] || fallback;
		}
		const homeUrl = computed(() => localizedRoute("home", {}, activeLocale.value, "/"));
		const propertiesUrl = computed(() => localizedRoute("property.index", {}, activeLocale.value, "/property"));
		const blogUrl = computed(() => localizedRoute("blog.index", {}, activeLocale.value, "/blog"));
		const contactUrl = computed(() => localizedRoute("support.contact-us", {}, activeLocale.value, "/contact-us"));
		const shortcuts = computed(() => [
			{
				href: homeUrl.value,
				label: trans("errors.not_found.link_home", "Home")
			},
			{
				href: propertiesUrl.value,
				label: trans("errors.not_found.link_properties", "Buy Real Estate")
			},
			{
				href: blogUrl.value,
				label: trans("errors.not_found.link_blog", "Blog")
			},
			{
				href: contactUrl.value,
				label: trans("errors.not_found.link_contact", "Contact us")
			}
		]);
		const { title: documentTitle, description: metaDescription, ogTitle, ogDescription, robots } = useDocumentSeo({
			pageTitle: () => trans("errors.not_found.title", "Page not found"),
			description: () => trans("errors.not_found.message", "The page you are looking for could not be found."),
			robots: "noindex, nofollow"
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: unref(documentTitle) }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (unref(metaDescription)) _push(`<meta head-key="description" name="description"${ssrRenderAttr("content", unref(metaDescription))}${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(robots)) _push(`<meta head-key="robots" name="robots"${ssrRenderAttr("content", unref(robots))}${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogTitle)) _push(`<meta head-key="og:title" property="og:title"${ssrRenderAttr("content", unref(ogTitle))}${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogDescription)) _push(`<meta head-key="og:description" property="og:description"${ssrRenderAttr("content", unref(ogDescription))}${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="og:type" property="og:type" content="website"${_scopeId}>`);
					} else return [
						unref(metaDescription) ? (openBlock(), createBlock("meta", {
							key: 0,
							"head-key": "description",
							name: "description",
							content: unref(metaDescription)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						unref(robots) ? (openBlock(), createBlock("meta", {
							key: 1,
							"head-key": "robots",
							name: "robots",
							content: unref(robots)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						unref(ogTitle) ? (openBlock(), createBlock("meta", {
							key: 2,
							"head-key": "og:title",
							property: "og:title",
							content: unref(ogTitle)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						unref(ogDescription) ? (openBlock(), createBlock("meta", {
							key: 3,
							"head-key": "og:description",
							property: "og:description",
							content: unref(ogDescription)
						}, null, 8, ["content"])) : createCommentVNode("", true),
						createVNode("meta", {
							"head-key": "og:type",
							property: "og:type",
							content: "website"
						})
					];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(_sfc_main$1, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<section class="inner-pages notfound imas-notfound" aria-labelledby="imas-notfound-title"${_scopeId}><div class="container"${_scopeId}><div class="imas-notfound__panel" data-imas-reveal="up"${_scopeId}><div class="imas-notfound__visual" aria-hidden="true"${_scopeId}><span class="imas-notfound__glow"${_scopeId}></span><span class="imas-notfound__code"${_scopeId}>404</span><span class="imas-notfound__mark"${_scopeId}><i class="fa fa-map-marker-alt"${_scopeId}></i></span></div><div class="top-headings text-center imas-notfound__copy"${_scopeId}><p class="imas-notfound__eyebrow text-xs font-semibold text-gold"${_scopeId}>${ssrInterpolate(trans("errors.not_found.eyebrow"))}</p><h1 id="imas-notfound-title" class="imas-notfound__title text-2xl font-bold"${_scopeId}>${ssrInterpolate(trans("errors.not_found.heading"))}</h1><p class="imas-notfound__message text-base text-dim"${_scopeId}>${ssrInterpolate(trans("errors.not_found.message"))}</p></div><div class="port-info imas-notfound__actions"${_scopeId}>`);
						_push(ssrRenderComponent(unref(Link), {
							href: homeUrl.value,
							class: "btn btn-primary btn-lg imas-notfound__btn imas-notfound__btn--primary"
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`${ssrInterpolate(trans("errors.not_found.go_home"))}`);
								else return [createTextVNode(toDisplayString(trans("errors.not_found.go_home")), 1)];
							}),
							_: 1
						}, _parent, _scopeId));
						_push(ssrRenderComponent(unref(Link), {
							href: propertiesUrl.value,
							class: "btn btn-lg imas-notfound__btn imas-notfound__btn--outline"
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`${ssrInterpolate(trans("errors.not_found.browse_properties"))}`);
								else return [createTextVNode(toDisplayString(trans("errors.not_found.browse_properties")), 1)];
							}),
							_: 1
						}, _parent, _scopeId));
						_push(`</div><nav class="imas-notfound__shortcuts"${ssrRenderAttr("aria-label", trans("errors.not_found.shortcuts_label"))}${_scopeId}><p class="imas-notfound__shortcuts-label text-sm text-muted"${_scopeId}>${ssrInterpolate(trans("errors.not_found.try_these"))}</p><ul class="imas-notfound__shortcut-list"${_scopeId}><!--[-->`);
						ssrRenderList(shortcuts.value, (item) => {
							_push(`<li${_scopeId}>`);
							_push(ssrRenderComponent(unref(Link), {
								href: item.href,
								class: "imas-notfound__shortcut text-sm"
							}, {
								default: withCtx((_, _push, _parent, _scopeId) => {
									if (_push) _push(`${ssrInterpolate(item.label)}`);
									else return [createTextVNode(toDisplayString(item.label), 1)];
								}),
								_: 2
							}, _parent, _scopeId));
							_push(`</li>`);
						});
						_push(`<!--]--></ul></nav></div></div></section>`);
					} else return [createVNode("section", {
						ref_key: "pageRef",
						ref: pageRef,
						class: "inner-pages notfound imas-notfound",
						"aria-labelledby": "imas-notfound-title"
					}, [createVNode("div", { class: "container" }, [createVNode("div", {
						class: "imas-notfound__panel",
						"data-imas-reveal": "up"
					}, [
						createVNode("div", {
							class: "imas-notfound__visual",
							"aria-hidden": "true"
						}, [
							createVNode("span", { class: "imas-notfound__glow" }),
							createVNode("span", { class: "imas-notfound__code" }, "404"),
							createVNode("span", { class: "imas-notfound__mark" }, [createVNode("i", { class: "fa fa-map-marker-alt" })])
						]),
						createVNode("div", { class: "top-headings text-center imas-notfound__copy" }, [
							createVNode("p", { class: "imas-notfound__eyebrow text-xs font-semibold text-gold" }, toDisplayString(trans("errors.not_found.eyebrow")), 1),
							createVNode("h1", {
								id: "imas-notfound-title",
								class: "imas-notfound__title text-2xl font-bold"
							}, toDisplayString(trans("errors.not_found.heading")), 1),
							createVNode("p", { class: "imas-notfound__message text-base text-dim" }, toDisplayString(trans("errors.not_found.message")), 1)
						]),
						createVNode("div", { class: "port-info imas-notfound__actions" }, [createVNode(unref(Link), {
							href: homeUrl.value,
							class: "btn btn-primary btn-lg imas-notfound__btn imas-notfound__btn--primary"
						}, {
							default: withCtx(() => [createTextVNode(toDisplayString(trans("errors.not_found.go_home")), 1)]),
							_: 1
						}, 8, ["href"]), createVNode(unref(Link), {
							href: propertiesUrl.value,
							class: "btn btn-lg imas-notfound__btn imas-notfound__btn--outline"
						}, {
							default: withCtx(() => [createTextVNode(toDisplayString(trans("errors.not_found.browse_properties")), 1)]),
							_: 1
						}, 8, ["href"])]),
						createVNode("nav", {
							class: "imas-notfound__shortcuts",
							"aria-label": trans("errors.not_found.shortcuts_label")
						}, [createVNode("p", { class: "imas-notfound__shortcuts-label text-sm text-muted" }, toDisplayString(trans("errors.not_found.try_these")), 1), createVNode("ul", { class: "imas-notfound__shortcut-list" }, [(openBlock(true), createBlock(Fragment, null, renderList(shortcuts.value, (item) => {
							return openBlock(), createBlock("li", { key: item.href }, [createVNode(unref(Link), {
								href: item.href,
								class: "imas-notfound__shortcut text-sm"
							}, {
								default: withCtx(() => [createTextVNode(toDisplayString(item.label), 1)]),
								_: 2
							}, 1032, ["href"])]);
						}), 128))])], 8, ["aria-label"])
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
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Errors/NotFound.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
//#endregion
export { _sfc_main as default };
