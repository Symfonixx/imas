import { f as _plugin_vue_export_helper_default, h as useOpenAuthModal } from "../ssr.js";
import { t as _sfc_main$1 } from "./App-nb92tFBB.js";
import { t as useDocumentSeo } from "./useDocumentSeo-IoWJXXs8.js";
import { Head, usePage } from "@inertiajs/vue3";
import { createBlock, createCommentVNode, createVNode, onMounted, openBlock, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderComponent } from "vue/server-renderer";
//#region Modules/User/resources/assets/js/Pages/Auth/ForgotPassword.vue
var _sfc_main = {
	__name: "ForgotPassword",
	__ssrInlineRender: true,
	props: { errors: {
		type: Object,
		default: () => ({})
	} },
	setup(__props) {
		const page = usePage();
		const { openAuthModal } = useOpenAuthModal();
		const trans = (key) => page.props.translations?.[key] || key;
		const { title: documentTitle, description: metaDescription, keywords: metaKeywords, ogTitle, ogDescription, ogImage, canonical: canonicalUrl, ogUrl, twitterCard, robots } = useDocumentSeo({
			pageTitle: () => trans("Forgot Password"),
			robots: "noindex, nofollow",
			canonical: () => {
				try {
					if (typeof route === "function" && route().has?.("password.request")) return route("password.request");
				} catch {}
				return "";
			}
		});
		function openForgotModal() {
			openAuthModal("forgot");
		}
		onMounted(() => {
			openForgotModal();
			window.setTimeout(openForgotModal, 50);
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: unref(documentTitle) }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (unref(metaDescription)) _push(`<meta head-key="description" name="description"${ssrRenderAttr("content", unref(metaDescription))} data-v-75419444${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(metaKeywords)) _push(`<meta head-key="keywords" name="keywords"${ssrRenderAttr("content", unref(metaKeywords))} data-v-75419444${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(canonicalUrl)) _push(`<link head-key="canonical" rel="canonical"${ssrRenderAttr("href", unref(canonicalUrl))} data-v-75419444${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogTitle)) _push(`<meta head-key="og:title" property="og:title"${ssrRenderAttr("content", unref(ogTitle))} data-v-75419444${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogDescription)) _push(`<meta head-key="og:description" property="og:description"${ssrRenderAttr("content", unref(ogDescription))} data-v-75419444${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogImage)) _push(`<meta head-key="og:image" property="og:image"${ssrRenderAttr("content", unref(ogImage))} data-v-75419444${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="og:type" property="og:type" content="website" data-v-75419444${_scopeId}>`);
						if (unref(ogUrl)) _push(`<meta head-key="og:url" property="og:url"${ssrRenderAttr("content", unref(ogUrl))} data-v-75419444${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(robots)) _push(`<meta head-key="robots" name="robots"${ssrRenderAttr("content", unref(robots))} data-v-75419444${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="twitter:card" name="twitter:card"${ssrRenderAttr("content", unref(twitterCard))} data-v-75419444${_scopeId}>`);
						if (unref(ogTitle)) _push(`<meta head-key="twitter:title" name="twitter:title"${ssrRenderAttr("content", unref(ogTitle))} data-v-75419444${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogDescription)) _push(`<meta head-key="twitter:description" name="twitter:description"${ssrRenderAttr("content", unref(ogDescription))} data-v-75419444${_scopeId}>`);
						else _push(`<!---->`);
						if (unref(ogImage)) _push(`<meta head-key="twitter:image" name="twitter:image"${ssrRenderAttr("content", unref(ogImage))} data-v-75419444${_scopeId}>`);
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
					if (_push) _push(`<div class="container mt-5 mb-5" data-v-75419444${_scopeId}><div class="row justify-content-center" data-v-75419444${_scopeId}><div class="col-md-6" data-v-75419444${_scopeId}><div class="card imas-auth-page-card" data-v-75419444${_scopeId}><div class="card-header text-center" data-v-75419444${_scopeId}><h3 class="text-md font-semibold mb-0" data-v-75419444${_scopeId}>${ssrInterpolate(trans("Forgot Password"))}</h3></div><div class="card-body text-center" data-v-75419444${_scopeId}><p class="text-sm text-dim mb-3" data-v-75419444${_scopeId}>${ssrInterpolate(trans("auth_modal.forgot_page_opening"))}</p><button type="button" class="btn btn-primary" data-v-75419444${_scopeId}>${ssrInterpolate(trans("Forgot Password"))}</button></div></div></div></div></div>`);
					else return [createVNode("div", { class: "container mt-5 mb-5" }, [createVNode("div", { class: "row justify-content-center" }, [createVNode("div", { class: "col-md-6" }, [createVNode("div", { class: "card imas-auth-page-card" }, [createVNode("div", { class: "card-header text-center" }, [createVNode("h3", { class: "text-md font-semibold mb-0" }, toDisplayString(trans("Forgot Password")), 1)]), createVNode("div", { class: "card-body text-center" }, [createVNode("p", { class: "text-sm text-dim mb-3" }, toDisplayString(trans("auth_modal.forgot_page_opening")), 1), createVNode("button", {
						type: "button",
						class: "btn btn-primary",
						onClick: openForgotModal
					}, toDisplayString(trans("Forgot Password")), 1)])])])])])];
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
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/User/resources/assets/js/Pages/Auth/ForgotPassword.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var ForgotPassword_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-75419444"]]);
//#endregion
export { ForgotPassword_default as default };
